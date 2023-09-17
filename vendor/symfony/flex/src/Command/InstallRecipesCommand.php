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
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Util\ProcessExecutor;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Flex\Event\UpdateEvent;
use Symfony\Flex\Flex;

class InstallRecipesCommand extends BaseCommand
{
    /** @var Flex */
    private $flex;
    private $rootDir;
    private $dotenvPath;

    public function __construct(/* cannot be type-hinted */ $flex, string $rootDir, string $dotenvPath = '.env')
    {
        $this->flex = $flex;
        $this->rootDir = $rootDir;
        $this->dotenvPath = $dotenvPath;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('symfony:recipes:install')
            ->setAliases(['recipes:install', 'symfony:sync-recipes', 'sync-recipes', 'fix-recipes'])
            ->setDescription('Installs or reinstalls recipes for already installed packages.')
            ->addArgument('packages', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Recipes that should be installed.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite existing files when a new version of a recipe is available')
            ->addOption('reset', null, InputOption::VALUE_NONE, 'Reset all recipes back to their initial state (should be combined with --force)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $win = '\\' === \DIRECTORY_SEPARATOR;
        $force = (bool) $input->getOption('force');

        if ($force && !@is_executable(strtok(exec($win ? 'where git' : 'command -v git'), \PHP_EOL))) {
            throw new RuntimeException('Cannot run "sync-recipes --force": git not found.');
        }

        $symfonyLock = $this->flex->getLock();
        $composer = $this->getComposer();
        $locker = $composer->getLocker();
        $lockData = $locker->getLockData();

        $packages = [];
        $totalPackages = [];
        foreach ($lockData['packages'] as $pkg) {
            $totalPackages[] = $pkg['name'];
            if ($force || !$symfonyLock->has($pkg['name'])) {
                $packages[] = $pkg['name'];
            }
        }
        foreach ($lockData['packages-dev'] as $pkg) {
            $totalPackages[] = $pkg['name'];
            if ($force || !$symfonyLock->has($pkg['name'])) {
                $packages[] = $pkg['name'];
            }
        }

        $io = $this->getIO();

        if (!$io->isVerbose()) {
            $io->writeError([
                'Run command with <info>-v</info> to see more details',
                '',
            ]);
        }

        if ($targetPackages = $input->getArgument('packages')) {
            if ($invalidPackages = array_diff($targetPackages, $totalPackages)) {
                $io->writeError(sprintf('<warning>Cannot update: some packages are not installed:</warning> %s', implode(', ', $invalidPackages)));

                return 1;
            }

            if ($packagesRequiringForce = array_diff($targetPackages, $packages)) {
                $io->writeError(sprintf('Recipe(s) already installed for: <info>%s</info>', implode(', ', $packagesRequiringForce)));
                $io->writeError('Re-run the command with <info>--force</info> to re-install the recipes.');
                $io->writeError('');
            }

            $packages = array_diff($targetPackages, $packagesRequiringForce);
        }

        if (!$packages) {
            $io->writeError('No recipes to install.');

            return 0;
        }

        $composer = $this->getComposer();
        $installedRepo = $composer->getRepositoryManager()->getLocalRepository();

        $operations = [];
        foreach ($packages as $package) {
            if (null === $pkg = $installedRepo->findPackage($package, '*')) {
                $io->writeError(sprintf('<error>Package %s is not installed</>', $package));

                return 1;
            }

            $operations[] = new InstallOperation($pkg);
        }

        $dotenvFile = $this->dotenvPath;
        $dotenvPath = $this->rootDir.'/'.$dotenvFile;

        if ($createEnvLocal = $force && file_exists($dotenvPath) && file_exists($dotenvPath.'.dist') && !file_exists($dotenvPath.'.local')) {
            rename($dotenvPath, $dotenvPath.'.local');
            $pipes = [];
            proc_close(proc_open(sprintf('git mv %s %s > %s 2>&1 || %s %1$s %2$s', ProcessExecutor::escape($dotenvFile.'.dist'), ProcessExecutor::escape($dotenvFile), $win ? 'NUL' : '/dev/null', $win ? 'rename' : 'mv'), $pipes, $pipes, $this->rootDir));
            if (file_exists($this->rootDir.'/phpunit.xml.dist')) {
                touch($dotenvPath.'.test');
            }
        }

        $this->flex->update(new UpdateEvent($force, (bool) $input->getOption('reset')), $operations);

        if ($force) {
            $output = [
                '',
                '<bg=blue;fg=white>                                                            </>',
                '<bg=blue;fg=white> Files have been reset to the latest version of the recipe. </>',
                '<bg=blue;fg=white>                                                            </>',
                '',
                '  * Use <comment>git diff</> to inspect the changes.',
                '',
                '    Not all of the changes will be relevant to your app: you now',
                '    need to selectively add or revert them using e.g. a combination',
                '    of <comment>git add -p</> and <comment>git checkout -p</>',
                '',
            ];

            if ($createEnvLocal) {
                $output[] = '    Dotenv files have been renamed: .env -> .env.local and .env.dist -> .env';
                $output[] = '    See https://symfony.com/doc/current/configuration/dot-env-changes.html';
                $output[] = '';
            }

            $output[] = '  * Use <comment>git checkout .</> to revert the changes.';
            $output[] = '';

            if ($createEnvLocal) {
                $root = '.' !== $this->rootDir ? $this->rootDir.'/' : '';
                $output[] = '    To revert the changes made to .env files, run';
                $output[] = sprintf('    <comment>git mv %s %s</> && <comment>%s %s %1$s</>', ProcessExecutor::escape($root.$dotenvFile), ProcessExecutor::escape($root.$dotenvFile.'.dist'), $win ? 'rename' : 'mv', ProcessExecutor::escape($root.$dotenvFile.'.local'));
                $output[] = '';
            }

            $output[] = '    New (untracked) files can be inspected using <comment>git clean --dry-run</>';
            $output[] = '    Add the new files you want to keep using <comment>git add</>';
            $output[] = '    then delete the rest using <comment>git clean --force</>';
            $output[] = '';

            $io->write($output);
        }

        return 0;
    }
}
