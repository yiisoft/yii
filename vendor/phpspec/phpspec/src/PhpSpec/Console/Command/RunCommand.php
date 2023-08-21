<?php

/*
 * This file is part of PhpSpec, A php toolset to drive emergent
 * design by specification.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpSpec\Console\Command;

use PhpSpec\Console\Application;
use PhpSpec\Formatter\FatalPresenter;
use PhpSpec\Process\Shutdown\UpdateConsoleAction;
use PhpSpec\ServiceContainer\IndexedServiceContainer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Main command, responsible for running the specs
 *
 * @internal
 */
final class RunCommand extends Command
{
    /**
     * @psalm-suppress MethodSignatureMismatch
     */
    public function getApplication(): Application
    {
        $application = parent::getApplication();

        if (!$application instanceof Application) {
            throw new \RuntimeException('PhpSpec commands require PhpSpec application');
        }

        return $application;
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('run')
            ->setDefinition(array(
                    new InputArgument(
                        'spec',
                        InputArgument::OPTIONAL,
                        'Specs to run'
                    ),
                    new InputOption(
                        'format',
                        'f',
                        InputOption::VALUE_REQUIRED,
                        'Formatter'
                    ),
                    new InputOption(
                        'stop-on-failure',
                        null,
                        InputOption::VALUE_NONE,
                        'Stop on failure'
                    ),
                    new InputOption(
                        'no-code-generation',
                        null,
                        InputOption::VALUE_NONE,
                        'Do not prompt for missing method/class generation'
                    ),
                    new InputOption(
                        'no-rerun',
                        null,
                        InputOption::VALUE_NONE,
                        'Do not rerun the suite after code generation'
                    ),
                    new InputOption(
                        'fake',
                        null,
                        InputOption::VALUE_NONE,
                        'Automatically fake return values when possible'
                    ),
                    new InputOption(
                        'bootstrap',
                        'b',
                        InputOption::VALUE_REQUIRED,
                        'Bootstrap php file that is run before the specs'
                    )
                ))
            ->setDescription('Runs specifications')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command runs specifications:

  <info>php %command.full_name%</info>

Will run all the specifications in the spec directory.

  <info>php %command.full_name% spec/ClassNameSpec.php</info>

Will run only the ClassNameSpec.

  <info>php %command.full_name% spec/ClassNameSpec.php:56</info>

Will run only specification defined in the ClassNameSpec on line 56.

You can choose the bootstrap file with the bootstrap option e.g.:

  <info>php %command.full_name% --bootstrap=bootstrap.php</info>

By default, you will be asked whether missing methods and classes should
be generated. You can suppress these prompts and automatically choose not
to generate code with:

  <info>php %command.full_name% --no-code-generation</info>

You can choose to stop on failure and not attempt to run the remaining
specs with:

  <info>php %command.full_name% --stop-on-failure</info>

You can opt to automatically fake return values with:

  <info>php %command.full_name% --fake</info>

You can choose the output format with the format option e.g.:

  <info>php %command.full_name% --format=dot</info>

The available formatters are:

   progress (default)
   html
   pretty
   junit
   dot
   tap

EOF
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $container = $this->getApplication()->getContainer();

        $container->setParam(
            'formatter.name',
            $input->getOption('format') ?: $container->getParam('formatter.name')
        );

        $formatterName = $container->getParam('formatter.name', 'progress');
        $currentFormatter = $container->get('formatter.formatters.'.$formatterName);

        if ($currentFormatter instanceof FatalPresenter) {

            $container->define('process.shutdown.update_console_action', function (IndexedServiceContainer $c) use ($currentFormatter) {
                $currentExample = $c->get('current_example');
                /** @var \PhpSpec\Message\CurrentExampleTracker $currentExample */
                return new UpdateConsoleAction(
                    $currentExample,
                    $currentFormatter
                );
            });

            $container->get('process.shutdown')->registerAction(
                $container->get('process.shutdown.update_console_action')
            );
            $container->get('process.shutdown')->registerShutdown();
        }

        $container->configure();

        $locator = $input->getArgument('spec') ?? '';
        $linenum = null;
        if (preg_match('/^(.*)\:(\d+)$/', $locator, $matches)) {
            list($_, $locator, $linenum) = $matches;
        }

        $suite       = $container->get('loader.resource_loader')->load((string)$locator, $linenum);
        $suiteRunner = $container->get('runner.suite');

        return $container->get('console.result_converter')->convert(
            $suiteRunner->run($suite)
        );
    }
}
