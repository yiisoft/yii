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

namespace PhpSpec\Console;

use PhpSpec\Exception\Configuration\InvalidConfigurationException;
use PhpSpec\Loader\StreamWrapper;
use PhpSpec\Matcher\Matcher;
use PhpSpec\Process\Context\JsonExecutionContext;
use PhpSpec\ServiceContainer;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Terminal;
use Symfony\Component\Yaml\Yaml;
use PhpSpec\ServiceContainer\IndexedServiceContainer;
use PhpSpec\Extension;
use RuntimeException;

/**
 * The command line application entry point
 *
 * @internal
 */
final class Application extends BaseApplication
{
    /**
     * @var IndexedServiceContainer
     */
    private $container;

    public function __construct(string $version)
    {
        $this->container = new IndexedServiceContainer();
        parent::__construct('phpspec', $version);
    }

    public function getContainer(): IndexedServiceContainer
    {
        return $this->container;
    }


    public function doRun(InputInterface $input, OutputInterface $output): int
    {
        $helperSet = $this->getHelperSet();
        $this->container->set('console.input', $input);
        $this->container->set('console.output', $output);
        $this->container->set('console.helper_set', $helperSet);

        $this->container->define('process.executioncontext', function () {
            return JsonExecutionContext::fromEnv($_SERVER);
        });

        $assembler = new ContainerAssembler();
        $assembler->build($this->container);

        $this->loadConfigurationFile($input, $this->container);

        foreach ($this->container->getByTag('console.commands') as $command) {
            $this->add($command);
        }

        $dispatcher = $this->container->get('console_event_dispatcher');

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $this->setDispatcher($dispatcher);

        $consoleWidth = (new Terminal)->getWidth();

        $this->container->get('console.io')->setConsoleWidth($consoleWidth);

        StreamWrapper::reset();
        foreach ($this->container->getByTag('loader.resource_loader.spec_transformer') as $transformer) {
            StreamWrapper::addTransformer($transformer);
        }
        StreamWrapper::register();

        return parent::doRun($input, $output);
    }

    /**
     * Fixes an issue with definitions of the no-interaction option not being
     * completely shown in some cases
     */
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $description = 'Do not ask any interactive question (disables code generation).';

        $definition = parent::getDefaultInputDefinition();
        $options = $definition->getOptions();

        if (array_key_exists('no-interaction', $options)) {
            $option = $options['no-interaction'];
            $options['no-interaction'] = new InputOption(
                $option->getName(),
                $option->getShortcut(),
                InputOption::VALUE_NONE,
                $description
            );
        }

        $options['config'] = new InputOption(
            'config',
            'c',
            InputOption::VALUE_REQUIRED,
            'Specify a custom location for the configuration file'
        );

        $definition->setOptions($options);

        return $definition;
    }

    /**
     * @throws \RuntimeException
     * @return void
     */
    protected function loadConfigurationFile(InputInterface $input, IndexedServiceContainer $container)
    {
        $config = $this->parseConfigurationFile($input);

        $this->populateContainerParameters($container, $config);

        foreach ($config as $key => $val) {
            if ('extensions' === $key && \is_array($val)) {
                foreach ($val as $class => $extensionConfig) {
                    $this->loadExtension($container, $class, $extensionConfig ?: []);
                }
            }
            elseif ('matchers' === $key && \is_array($val)) {
                $this->registerCustomMatchers($container, $val);
            }
        }
    }

    /**
     * @return void
     */
    private function populateContainerParameters(IndexedServiceContainer $container, array $config)
    {
        foreach ($config as $key => $val) {
            if ('extensions' !== $key && 'matchers' !== $key) {
                $container->setParam($key, $val);
            }
        }
    }

    /**
     * @return void
     */
    private function registerCustomMatchers(IndexedServiceContainer $container, array $matchersClassnames)
    {
        foreach ($matchersClassnames as $class) {
            $this->ensureIsValidMatcherClass($class);

            $container->define(sprintf('matchers.%s', $class), function () use ($class) {
                /** @psalm-suppress InvalidStringClass */
                return new $class();
            }, ['matchers']);
        }
    }

    /**
     * @return void
     */
    private function ensureIsValidMatcherClass(string $class)
    {
        if (!class_exists($class)) {
            throw new InvalidConfigurationException(sprintf('Custom matcher %s does not exist.', $class));
        }

        if (!is_subclass_of($class, Matcher::class)) {
            throw new InvalidConfigurationException(sprintf(
                'Custom matcher %s must implement %s interface, but it does not.',
                $class,
                Matcher::class
            ));
        }
    }

    /**
     * @param mixed $config
     * @return void
     */
    private function loadExtension(ServiceContainer $container, string $extensionClass, $config)
    {
        if (!class_exists($extensionClass)) {
            throw new InvalidConfigurationException(sprintf('Extension class `%s` does not exist.', $extensionClass));
        }

        if (!\is_array($config)) {
            throw new InvalidConfigurationException('Extension configuration must be an array or null.');
        }

        if (!is_a($extensionClass, Extension::class, true)) {
            throw new InvalidConfigurationException(sprintf('Extension class `%s` must implement Extension interface', $extensionClass));
        }

        (new $extensionClass)->load($container, $config);
    }

    /**
     * @throws \RuntimeException
     */
    protected function parseConfigurationFile(InputInterface $input): array
    {
        $paths = array('phpspec.yml', '.phpspec.yml', 'phpspec.yml.dist', 'phpspec.yaml', '.phpspec.yaml', 'phpspec.yaml.dist');

        if ($customPath = $input->getParameterOption(array('-c','--config'))) {
            if (!file_exists($customPath)) {
                throw new RuntimeException('Custom configuration file not found at '.$customPath);
            }
            $paths = array($customPath);
        }

        $config = $this->extractConfigFromFirstParsablePath($paths);

        if ($homeFolder = getenv('HOME')) {
            $config = array_replace_recursive($this->parseConfigFromExistingPath($homeFolder.'/.phpspec.yml'), $config);
        }

        return $config;
    }

    private function extractConfigFromFirstParsablePath(array $paths): array
    {
        foreach ($paths as $path) {
            if (!file_exists($path)) {
                continue;
            }

            $config = $this->parseConfigFromExistingPath($path);

            return $this->addPathsToEachSuiteConfig(dirname($path), $config);
        }

        return array();
    }


    private function parseConfigFromExistingPath(string $path): array
    {
        if (!file_exists($path)) {
            return array();
        }

        /** @psalm-suppress ReservedWord, RedundantCondition */
        return Yaml::parse(file_get_contents($path)) ?: [];
    }

    private function addPathsToEachSuiteConfig(string $configDir, array $config): array
    {
        if (isset($config['suites']) && \is_array($config['suites'])) {
            foreach ($config['suites'] as $suiteKey => $suiteConfig) {
                $config['suites'][$suiteKey] = str_replace('%paths.config%', $configDir, $suiteConfig);
            }
        }

        return $config;
    }
}
