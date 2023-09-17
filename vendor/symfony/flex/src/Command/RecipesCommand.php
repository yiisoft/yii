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
use Composer\Downloader\TransportException;
use Composer\Package\Package;
use Composer\Util\HttpDownloader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Flex\GithubApi;
use Symfony\Flex\InformationOperation;
use Symfony\Flex\Lock;
use Symfony\Flex\Recipe;

/**
 * @author Maxime Hélias <maximehelias16@gmail.com>
 */
class RecipesCommand extends BaseCommand
{
    /** @var \Symfony\Flex\Flex */
    private $flex;

    private Lock $symfonyLock;
    private GithubApi $githubApi;

    public function __construct(/* cannot be type-hinted */ $flex, Lock $symfonyLock, HttpDownloader $downloader)
    {
        $this->flex = $flex;
        $this->symfonyLock = $symfonyLock;
        $this->githubApi = new GithubApi($downloader);

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('symfony:recipes')
            ->setAliases(['recipes'])
            ->setDescription('Shows information about all available recipes.')
            ->setDefinition([
                new InputArgument('package', InputArgument::OPTIONAL, 'Package to inspect, if not provided all packages are.'),
            ])
            ->addOption('outdated', 'o', InputOption::VALUE_NONE, 'Show only recipes that are outdated')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $installedRepo = $this->getComposer()->getRepositoryManager()->getLocalRepository();

        // Inspect one or all packages
        $package = $input->getArgument('package');
        if (null !== $package) {
            $packages = [strtolower($package)];
        } else {
            $locker = $this->getComposer()->getLocker();
            $lockData = $locker->getLockData();

            // Merge all packages installed
            $packages = array_column(array_merge($lockData['packages'], $lockData['packages-dev']), 'name');
            $packages = array_unique(array_merge($packages, array_keys($this->symfonyLock->all())));
        }

        $operations = [];
        foreach ($packages as $name) {
            $pkg = $installedRepo->findPackage($name, '*');

            if (!$pkg && $this->symfonyLock->has($name)) {
                $pkgVersion = $this->symfonyLock->get($name)['version'];
                $pkg = new Package($name, $pkgVersion, $pkgVersion);
            } elseif (!$pkg) {
                $this->getIO()->writeError(sprintf('<error>Package %s is not installed</error>', $name));

                continue;
            }

            $operations[] = new InformationOperation($pkg);
        }

        $recipes = $this->flex->fetchRecipes($operations, false);
        ksort($recipes);

        $nbRecipe = \count($recipes);
        if ($nbRecipe <= 0) {
            $this->getIO()->writeError('<error>No recipe found</error>');

            return 1;
        }

        // Display the information about a specific recipe
        if (1 === $nbRecipe) {
            $this->displayPackageInformation(current($recipes));

            return 0;
        }

        $outdated = $input->getOption('outdated');

        $write = [];
        $hasOutdatedRecipes = false;
        foreach ($recipes as $name => $recipe) {
            $lockRef = $this->symfonyLock->get($name)['recipe']['ref'] ?? null;

            $additional = null;
            if (null === $lockRef && null !== $recipe->getRef()) {
                $additional = '<comment>(recipe not installed)</comment>';
            } elseif ($recipe->getRef() !== $lockRef && !$recipe->isAuto()) {
                $additional = '<comment>(update available)</comment>';
            }

            if ($outdated && null === $additional) {
                continue;
            }

            $hasOutdatedRecipes = true;
            $write[] = sprintf(' * %s %s', $name, $additional);
        }

        // Nothing to display
        if (!$hasOutdatedRecipes) {
            return 0;
        }

        $this->getIO()->write(array_merge([
            '',
            '<bg=blue;fg=white>                      </>',
            sprintf('<bg=blue;fg=white> %s recipes.   </>', $outdated ? ' Outdated' : 'Available'),
            '<bg=blue;fg=white>                      </>',
            '',
        ], $write, [
            '',
            'Run:',
            ' * <info>composer recipes vendor/package</info> to see details about a recipe.',
            ' * <info>composer recipes:update vendor/package</info> to update that recipe.',
            '',
        ]));

        if ($outdated) {
            return 1;
        }

        return 0;
    }

    private function displayPackageInformation(Recipe $recipe)
    {
        $io = $this->getIO();
        $recipeLock = $this->symfonyLock->get($recipe->getName());

        $lockRef = $recipeLock['recipe']['ref'] ?? null;
        $lockRepo = $recipeLock['recipe']['repo'] ?? null;
        $lockFiles = $recipeLock['files'] ?? null;
        $lockBranch = $recipeLock['recipe']['branch'] ?? null;
        $lockVersion = $recipeLock['recipe']['version'] ?? $recipeLock['version'] ?? null;

        if ('master' === $lockBranch && \in_array($lockRepo, ['github.com/symfony/recipes', 'github.com/symfony/recipes-contrib'])) {
            $lockBranch = 'main';
        }

        $status = '<comment>up to date</comment>';
        if ($recipe->isAuto()) {
            $status = '<comment>auto-generated recipe</comment>';
        } elseif (null === $lockRef && null !== $recipe->getRef()) {
            $status = '<comment>recipe not installed</comment>';
        } elseif ($recipe->getRef() !== $lockRef) {
            $status = '<comment>update available</comment>';
        }

        $gitSha = null;
        $commitDate = null;
        if (null !== $lockRef && null !== $lockRepo) {
            try {
                $recipeCommitData = $this->githubApi->findRecipeCommitDataFromTreeRef(
                    $recipe->getName(),
                    $lockRepo,
                    $lockBranch ?? '',
                    $lockVersion,
                    $lockRef
                );
                $gitSha = $recipeCommitData ? $recipeCommitData['commit'] : null;
                $commitDate = $recipeCommitData ? $recipeCommitData['date'] : null;
            } catch (TransportException $exception) {
                $io->writeError('Error downloading exact git sha for installed recipe.');
            }
        }

        $io->write('<info>name</info>             : '.$recipe->getName());
        $io->write('<info>version</info>          : '.($lockVersion ?? 'n/a'));
        $io->write('<info>status</info>           : '.$status);
        if (!$recipe->isAuto() && null !== $lockVersion) {
            $recipeUrl = sprintf(
                'https://%s/tree/%s/%s/%s',
                $lockRepo,
                // if something fails, default to the branch as the closest "sha"
                $gitSha ?? $lockBranch,
                $recipe->getName(),
                $lockVersion
            );

            $io->write('<info>installed recipe</info> : '.$recipeUrl);
        }

        if ($lockRef !== $recipe->getRef()) {
            $io->write('<info>latest recipe</info>    : '.$recipe->getURL());
        }

        if ($lockRef !== $recipe->getRef() && null !== $lockVersion) {
            $historyUrl = sprintf(
                'https://%s/commits/%s/%s',
                $lockRepo,
                $lockBranch,
                $recipe->getName()
            );

            // show commits since one second after the currently-installed recipe
            if (null !== $commitDate) {
                $historyUrl .= '?since='.(new \DateTime($commitDate))->modify('+1 seconds')->format('c\Z');
            }

            $io->write('<info>recipe history</info>   : '.$historyUrl);
        }

        if (null !== $lockFiles) {
            $io->write('<info>files</info>            : ');
            $io->write('');

            $tree = $this->generateFilesTree($lockFiles);

            $this->displayFilesTree($tree);
        }

        if ($lockRef !== $recipe->getRef()) {
            $io->write([
                '',
                'Update this recipe by running:',
                sprintf('<info>composer recipes:update %s</info>', $recipe->getName()),
            ]);
        }
    }

    private function generateFilesTree(array $files): array
    {
        $tree = [];
        foreach ($files as $file) {
            $path = explode('/', $file);

            $tree = array_merge_recursive($tree, $this->addNode($path));
        }

        return $tree;
    }

    private function addNode(array $node): array
    {
        $current = array_shift($node);

        $subTree = [];
        if (null !== $current) {
            $subTree[$current] = $this->addNode($node);
        }

        return $subTree;
    }

    /**
     * Note : We do not display file modification information with Configurator like ComposerScripts, Container, DockerComposer, Dockerfile, Env, Gitignore and Makefile.
     */
    private function displayFilesTree(array $tree)
    {
        end($tree);
        $endKey = key($tree);
        foreach ($tree as $dir => $files) {
            $treeBar = '├';
            $total = \count($files);
            if (0 === $total || $endKey === $dir) {
                $treeBar = '└';
            }

            $info = sprintf(
                '%s──%s',
                $treeBar,
                $dir
            );
            $this->writeTreeLine($info);

            $treeBar = str_replace('└', ' ', $treeBar);

            $this->displayTree($files, $treeBar);
        }
    }

    private function displayTree(array $tree, $previousTreeBar = '├', $level = 1)
    {
        $previousTreeBar = str_replace('├', '│', $previousTreeBar);
        $treeBar = $previousTreeBar.'  ├';

        $i = 0;
        $total = \count($tree);

        foreach ($tree as $dir => $files) {
            ++$i;
            if ($i === $total) {
                $treeBar = $previousTreeBar.'  └';
            }

            $info = sprintf(
                '%s──%s',
                $treeBar,
                $dir
            );
            $this->writeTreeLine($info);

            $treeBar = str_replace('└', ' ', $treeBar);

            $this->displayTree($files, $treeBar, $level + 1);
        }
    }

    private function writeTreeLine($line)
    {
        $io = $this->getIO();
        if (!$io->isDecorated()) {
            $line = str_replace(['└', '├', '──', '│'], ['`-', '|-', '-', '|'], $line);
        }

        $io->write($line);
    }
}
