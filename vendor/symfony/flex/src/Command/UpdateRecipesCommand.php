<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Flex\Command;

use Composer\Command\BaseCommand;
use Composer\IO\IOInterface;
use Composer\Package\Package;
use Composer\Package\PackageInterface;
use Composer\Util\ProcessExecutor;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Flex\Configurator;
use Symfony\Flex\Downloader;
use Symfony\Flex\Flex;
use Symfony\Flex\GithubApi;
use Symfony\Flex\InformationOperation;
use Symfony\Flex\Lock;
use Symfony\Flex\Recipe;
use Symfony\Flex\Update\RecipePatcher;
use Symfony\Flex\Update\RecipeUpdate;

class UpdateRecipesCommand extends BaseCommand
{
    /** @var Flex */
    private $flex;
    private $downloader;
    private $configurator;
    private $rootDir;
    private $githubApi;
    private $processExecutor;

    public function __construct(/* cannot be type-hinted */ $flex, Downloader $downloader, $httpDownloader, Configurator $configurator, string $rootDir)
    {
        $this->flex = $flex;
        $this->downloader = $downloader;
        $this->configurator = $configurator;
        $this->rootDir = $rootDir;
        $this->githubApi = new GithubApi($httpDownloader);

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('symfony:recipes:update')
            ->setAliases(['recipes:update'])
            ->setDescription('Updates an already-installed recipe to the latest version.')
            ->addArgument('package', InputArgument::OPTIONAL, 'Recipe that should be updated.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $win = '\\' === \DIRECTORY_SEPARATOR;
        $runtimeExceptionClass = class_exists(RuntimeException::class) ? RuntimeException::class : \RuntimeException::class;
        if (!@is_executable(strtok(exec($win ? 'where git' : 'command -v git'), \PHP_EOL))) {
            throw new $runtimeExceptionClass('Cannot run "recipes:update": git not found.');
        }

        $io = $this->getIO();
        if (!$this->isIndexClean($io)) {
            $io->write([
                '  Cannot run <comment>recipes:update</comment>: Your git index contains uncommitted changes.',
                '  Please commit or stash them and try again!',
            ]);

            return 1;
        }

        $packageName = $input->getArgument('package');
        $symfonyLock = $this->flex->getLock();
        if (!$packageName) {
            $packageName = $this->askForPackage($io, $symfonyLock);

            if (null === $packageName) {
                $io->writeError('All packages appear to be up-to-date!');

                return 0;
            }
        }

        if (!$symfonyLock->has($packageName)) {
            $io->writeError([
                'Package not found inside symfony.lock. It looks like it\'s not installed?',
                sprintf('Try running <info>composer recipes:install %s --force -v</info> to re-install the recipe.', $packageName),
            ]);

            return 1;
        }

        $packageLockData = $symfonyLock->get($packageName);
        if (!isset($packageLockData['recipe'])) {
            $io->writeError([
                'It doesn\'t look like this package had a recipe when it was originally installed.',
                'To install the latest version of the recipe, if there is one, run:',
                sprintf('  <info>composer recipes:install %s --force -v</info>', $packageName),
            ]);

            return 1;
        }

        $recipeRef = $packageLockData['recipe']['ref'] ?? null;
        $recipeVersion = $packageLockData['recipe']['version'] ?? null;
        if (!$recipeRef || !$recipeVersion) {
            $io->writeError([
                'The version of the installed recipe was not saved into symfony.lock.',
                'This is possible if it was installed by an old version of Symfony Flex.',
                'Update the recipe by re-installing the latest version with:',
                sprintf('  <info>composer recipes:install %s --force -v</info>', $packageName),
            ]);

            return 1;
        }

        $installedRepo = $this->getComposer()->getRepositoryManager()->getLocalRepository();
        $package = $installedRepo->findPackage($packageName, '*') ?? new Package($packageName, $packageLockData['version'], $packageLockData['version']);
        $originalRecipe = $this->getRecipe($package, $recipeRef, $recipeVersion);

        if (null === $originalRecipe) {
            $io->writeError([
                'The original recipe version you have installed could not be found, it may be too old.',
                'Update the recipe by re-installing the latest version with:',
                sprintf('  <info>composer recipes:install %s --force -v</info>', $packageName),
            ]);

            return 1;
        }

        $newRecipe = $this->getRecipe($package);

        if ($newRecipe->getRef() === $originalRecipe->getRef()) {
            $io->write(sprintf('This recipe for <info>%s</info> is already at the latest version.', $packageName));

            return 0;
        }

        $io->write([
            sprintf('  Updating recipe for <info>%s</info>...', $packageName),
            '',
        ]);

        $recipeUpdate = new RecipeUpdate($originalRecipe, $newRecipe, $symfonyLock, $this->rootDir);
        $this->configurator->populateUpdate($recipeUpdate);
        $originalComposerJsonHash = $this->flex->getComposerJsonHash();
        $patcher = new RecipePatcher($this->rootDir, $io);

        try {
            $patch = $patcher->generatePatch($recipeUpdate->getOriginalFiles(), $recipeUpdate->getNewFiles());
            $hasConflicts = !$patcher->applyPatch($patch);
        } catch (\Throwable $throwable) {
            $io->writeError([
                '<bg=red;fg=white>There was an error applying the recipe update patch</>',
                $throwable->getMessage(),
                '',
                'Update the recipe by re-installing the latest version with:',
                sprintf('  <info>composer recipes:install %s --force -v</info>', $packageName),
            ]);

            return 1;
        }

        $symfonyLock->add($packageName, $newRecipe->getLock());
        $this->flex->finish($this->rootDir, $originalComposerJsonHash);

        // stage symfony.lock, as all patched files with already be staged
        $cmdOutput = '';
        $this->getProcessExecutor()->execute('git add symfony.lock', $cmdOutput, $this->rootDir);

        $io->write([
            '  <bg=blue;fg=white>                      </>',
            '  <bg=blue;fg=white> Yes! Recipe updated! </>',
            '  <bg=blue;fg=white>                      </>',
            '',
        ]);

        if ($hasConflicts) {
            $io->write([
                '  The recipe was updated but with <bg=red;fg=white>one or more conflicts</>.',
                '  Run <comment>git status</comment> to see them.',
                '  After resolving, commit your changes like normal.',
            ]);
        } else {
            if (!$patch->getPatch()) {
                // no changes were required
                $io->write([
                    '  No files were changed as a result of the update.',
                ]);
            } else {
                $io->write([
                    '  Run <comment>git status</comment> or <comment>git diff --cached</comment> to see the changes.',
                    '  When you\'re ready, commit these changes like normal.',
                ]);
            }
        }

        if (0 !== \count($recipeUpdate->getCopyFromPackagePaths())) {
            $io->write([
                '',
                '  <bg=red;fg=white>NOTE:</>',
                '  This recipe copies the following paths from the bundle into your app:',
            ]);
            foreach ($recipeUpdate->getCopyFromPackagePaths() as $source => $target) {
                $io->write(sprintf('  * %s => %s', $source, $target));
            }
            $io->write([
                '',
                '  The recipe updater has no way of knowing if these files have changed since you originally installed the recipe.',
                '  And so, no updates were made to these paths.',
            ]);
        }

        if (0 !== \count($patch->getRemovedPatches())) {
            if (1 === \count($patch->getRemovedPatches())) {
                $notes = [
                    sprintf('  The file <comment>%s</comment> was not updated because it doesn\'t exist in your app.', array_keys($patch->getRemovedPatches())[0]),
                ];
            } else {
                $notes = ['  The following files were not updated because they don\'t exist in your app:'];
                foreach ($patch->getRemovedPatches() as $filename => $contents) {
                    $notes[] = sprintf('    * <comment>%s</comment>', $filename);
                }
            }
            $io->write([
                '',
                '  <bg=red;fg=white>NOTE:</>',
            ]);
            $io->write($notes);
            $io->write('');
            if ($io->askConfirmation('  Would you like to save the "diff" to a file so you can review it? (Y/n) ')) {
                $patchFilename = str_replace('/', '.', $packageName).'.updates-for-deleted-files.patch';
                file_put_contents($this->rootDir.'/'.$patchFilename, implode("\n", $patch->getRemovedPatches()));
                $io->write([
                    '',
                    sprintf('  Saved diff to <info>%s</info>', $patchFilename),
                ]);
            }
        }

        if ($patch->getPatch()) {
            $io->write('');
            $io->write('  Calculating CHANGELOG...', false);
            $changelog = $this->generateChangelog($originalRecipe);
            $io->write("\r", false); // clear current line
            if ($changelog) {
                $io->write($changelog);
            } else {
                $io->write('No CHANGELOG could be calculated.');
            }
        }

        return 0;
    }

    private function getRecipe(PackageInterface $package, string $recipeRef = null, string $recipeVersion = null): ?Recipe
    {
        $operation = new InformationOperation($package);
        if (null !== $recipeRef) {
            $operation->setSpecificRecipeVersion($recipeRef, $recipeVersion);
        }
        $recipes = $this->downloader->getRecipes([$operation]);

        if (0 === \count($recipes['manifests'] ?? [])) {
            return null;
        }

        return new Recipe(
            $package,
            $package->getName(),
            $operation->getOperationType(),
            $recipes['manifests'][$package->getName()],
            $recipes['locks'][$package->getName()] ?? []
        );
    }

    private function generateChangelog(Recipe $originalRecipe): ?array
    {
        $recipeData = $originalRecipe->getLock()['recipe'] ?? null;
        if (null === $recipeData) {
            return null;
        }

        if (!isset($recipeData['ref']) || !isset($recipeData['repo']) || !isset($recipeData['branch']) || !isset($recipeData['version'])) {
            return null;
        }

        $currentRecipeVersionData = $this->githubApi->findRecipeCommitDataFromTreeRef(
            $originalRecipe->getName(),
            $recipeData['repo'],
            $recipeData['branch'],
            $recipeData['version'],
            $recipeData['ref']
        );

        if (!$currentRecipeVersionData) {
            return null;
        }

        $recipeVersions = $this->githubApi->getVersionsOfRecipe(
            $recipeData['repo'],
            $recipeData['branch'],
            $originalRecipe->getName()
        );
        if (!$recipeVersions) {
            return null;
        }

        $newerRecipeVersions = array_filter($recipeVersions, function ($version) use ($recipeData) {
            return version_compare($version, $recipeData['version'], '>');
        });

        $newCommits = $currentRecipeVersionData['new_commits'];
        foreach ($newerRecipeVersions as $newerRecipeVersion) {
            $newCommits = array_merge(
                $newCommits,
                $this->githubApi->getCommitDataForPath($recipeData['repo'], $originalRecipe->getName().'/'.$newerRecipeVersion, $recipeData['branch'])
            );
        }

        $newCommits = array_unique($newCommits);
        asort($newCommits);

        $pullRequests = [];
        foreach ($newCommits as $commit => $date) {
            $pr = $this->githubApi->getPullRequestForCommit($commit, $recipeData['repo']);
            if ($pr) {
                $pullRequests[$pr['number']] = $pr;
            }
        }

        $lines = [];
        // borrowed from symfony/console's OutputFormatterStyle
        $handlesHrefGracefully = 'JetBrains-JediTerm' !== getenv('TERMINAL_EMULATOR')
                        && (!getenv('KONSOLE_VERSION') || (int) getenv('KONSOLE_VERSION') > 201100);
        foreach ($pullRequests as $number => $data) {
            $url = $data['url'];
            if ($handlesHrefGracefully) {
                $url = "\033]8;;$url\033\\$number\033]8;;\033\\";
            }
            $lines[] = sprintf('  * %s (PR %s)', $data['title'], $url);
        }

        return $lines;
    }

    private function askForPackage(IOInterface $io, Lock $symfonyLock): ?string
    {
        $installedRepo = $this->getComposer()->getRepositoryManager()->getLocalRepository();

        $operations = [];
        foreach ($symfonyLock->all() as $name => $lock) {
            if (isset($lock['recipe']['ref'])) {
                $package = $installedRepo->findPackage($name, '*') ?? new Package($name, $lock['version'], $lock['version']);
                $operations[] = new InformationOperation($package);
            }
        }

        $recipes = $this->flex->fetchRecipes($operations, false);
        ksort($recipes);

        $outdatedRecipes = [];
        foreach ($recipes as $name => $recipe) {
            $lockRef = $symfonyLock->get($name)['recipe']['ref'] ?? null;

            if (null !== $lockRef && $recipe->getRef() !== $lockRef && !$recipe->isAuto()) {
                $outdatedRecipes[] = $name;
            }
        }

        if (0 === \count($outdatedRecipes)) {
            return null;
        }

        $question = 'Which outdated recipe would you like to update? (default: <info>0</info>)';

        $choice = $io->select(
            $question,
            $outdatedRecipes,
            0
        );

        return $outdatedRecipes[$choice];
    }

    private function isIndexClean(IOInterface $io): bool
    {
        $output = '';

        $this->getProcessExecutor()->execute('git status --porcelain --untracked-files=no', $output, $this->rootDir);
        if ('' !== trim($output)) {
            return false;
        }

        return true;
    }

    private function getProcessExecutor(): ProcessExecutor
    {
        if (null === $this->processExecutor) {
            $this->processExecutor = new ProcessExecutor($this->getIO());
        }

        return $this->processExecutor;
    }
}
