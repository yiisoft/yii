<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Flex\Configurator;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Json\JsonManipulator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Flex\Lock;
use Symfony\Flex\Options;
use Symfony\Flex\Recipe;
use Symfony\Flex\Update\RecipeUpdate;

/**
 * Adds services and volumes to docker-compose.yml file.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class DockerComposeConfigurator extends AbstractConfigurator
{
    private $filesystem;

    public static $configureDockerRecipes = null;

    public function __construct(Composer $composer, IOInterface $io, Options $options)
    {
        parent::__construct($composer, $io, $options);

        $this->filesystem = new Filesystem();
    }

    public function configure(Recipe $recipe, $config, Lock $lock, array $options = [])
    {
        if (!self::shouldConfigureDockerRecipe($this->composer, $this->io, $recipe)) {
            return;
        }

        $this->configureDockerCompose($recipe, $config, $options['force'] ?? false);

        $this->write('Docker Compose definitions have been modified. Please run "docker compose up --build" again to apply the changes.');
    }

    public function unconfigure(Recipe $recipe, $config, Lock $lock)
    {
        $rootDir = $this->options->get('root-dir');
        foreach ($this->normalizeConfig($config) as $file => $extra) {
            if (null === $dockerComposeFile = $this->findDockerComposeFile($rootDir, $file)) {
                continue;
            }

            $name = $recipe->getName();
            // Remove recipe and add break line
            $contents = preg_replace(sprintf('{%s+###> %s ###.*?###< %s ###%s+}s', "\n", $name, $name, "\n"), \PHP_EOL.\PHP_EOL, file_get_contents($dockerComposeFile), -1, $count);
            if (!$count) {
                return;
            }

            foreach ($extra as $key => $value) {
                if (0 === preg_match(sprintf('{^%s:[ \t\r\n]*([ \t]+\w|#)}m', $key), $contents, $matches)) {
                    $contents = preg_replace(sprintf('{\n?^%s:[ \t\r\n]*}sm', $key), '', $contents, -1, $count);
                }
            }

            $this->write(sprintf('Removing Docker Compose entries from "%s"', $dockerComposeFile));
            file_put_contents($dockerComposeFile, ltrim($contents, "\n"));
        }

        $this->write('Docker Compose definitions have been modified. Please run "docker compose up" again to apply the changes.');
    }

    public function update(RecipeUpdate $recipeUpdate, array $originalConfig, array $newConfig): void
    {
        if (!self::shouldConfigureDockerRecipe($this->composer, $this->io, $recipeUpdate->getNewRecipe())) {
            return;
        }

        $recipeUpdate->addOriginalFiles(
            $this->getContentsAfterApplyingRecipe($recipeUpdate->getRootDir(), $recipeUpdate->getOriginalRecipe(), $originalConfig)
        );

        $recipeUpdate->addNewFiles(
            $this->getContentsAfterApplyingRecipe($recipeUpdate->getRootDir(), $recipeUpdate->getNewRecipe(), $newConfig)
        );
    }

    public static function shouldConfigureDockerRecipe(Composer $composer, IOInterface $io, Recipe $recipe): bool
    {
        if (null !== self::$configureDockerRecipes) {
            return self::$configureDockerRecipes;
        }

        if (null !== $dockerPreference = $composer->getPackage()->getExtra()['symfony']['docker'] ?? null) {
            self::$configureDockerRecipes = $dockerPreference;

            return self::$configureDockerRecipes;
        }

        if ('install' !== $recipe->getJob()) {
            // default to not configuring
            return false;
        }

        if (!isset($_SERVER['SYMFONY_DOCKER'])) {
            $answer = self::askDockerSupport($io, $recipe);
        } elseif (filter_var($_SERVER['SYMFONY_DOCKER'], \FILTER_VALIDATE_BOOLEAN)) {
            $answer = 'p';
        } else {
            $answer = 'x';
        }

        if ('n' === $answer) {
            self::$configureDockerRecipes = false;

            return self::$configureDockerRecipes;
        }
        if ('y' === $answer) {
            self::$configureDockerRecipes = true;

            return self::$configureDockerRecipes;
        }

        // yes or no permanently
        self::$configureDockerRecipes = 'p' === $answer;
        $json = new JsonFile(Factory::getComposerFile());
        $manipulator = new JsonManipulator(file_get_contents($json->getPath()));
        $manipulator->addSubNode('extra', 'symfony.docker', self::$configureDockerRecipes);
        file_put_contents($json->getPath(), $manipulator->getContents());

        return self::$configureDockerRecipes;
    }

    /**
     * Normalizes the config and return the name of the main Docker Compose file if applicable.
     */
    private function normalizeConfig(array $config): array
    {
        foreach ($config as $val) {
            // Support for the short syntax recipe syntax that modifies docker-compose.yml only
            return isset($val[0]) ? ['docker-compose.yml' => $config] : $config;
        }

        return $config;
    }

    /**
     * Finds the Docker Compose file according to these rules: https://docs.docker.com/compose/reference/envvars/#compose_file.
     */
    private function findDockerComposeFile(string $rootDir, string $file): ?string
    {
        if (isset($_SERVER['COMPOSE_FILE'])) {
            $separator = $_SERVER['COMPOSE_PATH_SEPARATOR'] ?? ('\\' === \DIRECTORY_SEPARATOR ? ';' : ':');

            $files = explode($separator, $_SERVER['COMPOSE_FILE']);
            foreach ($files as $f) {
                if ($file !== basename($f)) {
                    continue;
                }

                if (!$this->filesystem->isAbsolutePath($f)) {
                    $f = realpath(sprintf('%s/%s', $rootDir, $f));
                }

                if ($this->filesystem->exists($f)) {
                    return $f;
                }
            }
        }

        // COMPOSE_FILE not set, or doesn't contain the file we're looking for
        $dir = $rootDir;
        do {
            // Test with the ".yaml" extension if the file doesn't end up with ".yml".
            if (
                $this->filesystem->exists($dockerComposeFile = sprintf('%s/%s', $dir, $file)) ||
                $this->filesystem->exists($dockerComposeFile = substr($dockerComposeFile, 0, -2).'aml')
            ) {
                return $dockerComposeFile;
            }

            $previousDir = $dir;
            $dir = \dirname($dir);
        } while ($dir !== $previousDir);

        return null;
    }

    private function parse($level, $indent, $services): string
    {
        $line = '';
        foreach ($services as $key => $value) {
            $line .= str_repeat(' ', $indent * $level);
            if (!\is_array($value)) {
                if (\is_string($key)) {
                    $line .= sprintf('%s:', $key);
                }
                $line .= sprintf("%s\n", $value);
                continue;
            }
            $line .= sprintf("%s:\n", $key).$this->parse($level + 1, $indent, $value);
        }

        return $line;
    }

    private function configureDockerCompose(Recipe $recipe, array $config, bool $update): void
    {
        $rootDir = $this->options->get('root-dir');
        foreach ($this->normalizeConfig($config) as $file => $extra) {
            $dockerComposeFile = $this->findDockerComposeFile($rootDir, $file);
            if (null === $dockerComposeFile) {
                $dockerComposeFile = $rootDir.'/'.$file;
                file_put_contents($dockerComposeFile, "version: '3'\n");
                $this->write(sprintf('  Created <fg=green>"%s"</>', $file));
            }

            if (!$update && $this->isFileMarked($recipe, $dockerComposeFile)) {
                continue;
            }

            $this->write(sprintf('Adding Docker Compose definitions to "%s"', $dockerComposeFile));

            $offset = 2;
            $node = null;
            $endAt = [];
            $startAt = [];
            $lines = [];
            $nodesLines = [];
            foreach (file($dockerComposeFile) as $i => $line) {
                $lines[] = $line;
                $ltrimedLine = ltrim($line, ' ');
                if (null !== $node) {
                    $nodesLines[$node][$i] = $line;
                }

                // Skip blank lines and comments
                if (('' !== $ltrimedLine && 0 === strpos($ltrimedLine, '#')) || '' === trim($line)) {
                    continue;
                }

                // Extract Docker Compose keys (usually "services" and "volumes")
                if (!preg_match('/^[\'"]?([a-zA-Z0-9]+)[\'"]?:\s*$/', $line, $matches)) {
                    // Detect indentation to use
                    $offestLine = \strlen($line) - \strlen($ltrimedLine);
                    if ($offset > $offestLine && 0 !== $offestLine) {
                        $offset = $offestLine;
                    }
                    continue;
                }

                // Keep end in memory (check break line on previous line)
                $endAt[$node] = !$i || '' !== trim($lines[$i - 1]) ? $i : $i - 1;
                $node = $matches[1];
                if (!isset($nodesLines[$node])) {
                    $nodesLines[$node] = [];
                }
                if (!isset($startAt[$node])) {
                    // the section contents starts at the next line
                    $startAt[$node] = $i + 1;
                }
            }
            $endAt[$node] = \count($lines) + 1;

            foreach ($extra as $key => $value) {
                if (isset($endAt[$key])) {
                    $data = $this->markData($recipe, $this->parse(1, $offset, $value));
                    $updatedContents = $this->updateDataString(implode('', $nodesLines[$key]), $data);
                    if (null === $updatedContents) {
                        // not an update: just add to section
                        array_splice($lines, $endAt[$key], 0, $data);

                        continue;
                    }

                    $originalEndAt = $endAt[$key];
                    $length = $endAt[$key] - $startAt[$key];
                    array_splice($lines, $startAt[$key], $length, ltrim($updatedContents, "\n"));

                    // reset any start/end positions after this to the new positions
                    foreach ($startAt as $sectionKey => $at) {
                        if ($at > $originalEndAt) {
                            $startAt[$sectionKey] = $at - $length - 1;
                        }
                    }
                    foreach ($endAt as $sectionKey => $at) {
                        if ($at > $originalEndAt) {
                            $endAt[$sectionKey] = $at - $length;
                        }
                    }

                    continue;
                }

                $lines[] = sprintf("\n%s:", $key);
                $lines[] = $this->markData($recipe, $this->parse(1, $offset, $value));
            }

            file_put_contents($dockerComposeFile, implode('', $lines));
        }
    }

    private function getContentsAfterApplyingRecipe(string $rootDir, Recipe $recipe, array $config): array
    {
        if (0 === \count($config)) {
            return [];
        }

        $files = array_filter(array_map(function ($file) use ($rootDir) {
            return $this->findDockerComposeFile($rootDir, $file);
        }, array_keys($config)));

        $originalContents = [];
        foreach ($files as $file) {
            $originalContents[$file] = file_exists($file) ? file_get_contents($file) : null;
        }

        $this->configureDockerCompose(
            $recipe,
            $config,
            true
        );

        $updatedContents = [];
        foreach ($files as $file) {
            $localPath = $file;
            if (0 === strpos($file, $rootDir)) {
                $localPath = substr($file, \strlen($rootDir) + 1);
            }
            $localPath = ltrim($localPath, '/\\');
            $updatedContents[$localPath] = file_exists($file) ? file_get_contents($file) : null;
        }

        foreach ($originalContents as $file => $contents) {
            if (null === $contents) {
                if (file_exists($file)) {
                    unlink($file);
                }
            } else {
                file_put_contents($file, $contents);
            }
        }

        return $updatedContents;
    }

    private static function askDockerSupport(IOInterface $io, Recipe $recipe): string
    {
        $warning = $io->isInteractive() ? 'WARNING' : 'IGNORING';
        $io->writeError(sprintf('  - <warning> %s </> %s', $warning, $recipe->getFormattedOrigin()));
        $question = '    The recipe for this package contains some Docker configuration.

    This may create/update <comment>docker-compose.yml</comment> or update <comment>Dockerfile</comment> (if it exists).

    Do you want to include Docker configuration from recipes?
    [<comment>y</>] Yes
    [<comment>n</>] No
    [<comment>p</>] Yes permanently, never ask again for this project
    [<comment>x</>] No permanently, never ask again for this project
    (defaults to <comment>y</>): ';

        return $io->askAndValidate(
            $question,
            function ($value) {
                if (null === $value) {
                    return 'y';
                }
                $value = strtolower($value[0]);
                if (!\in_array($value, ['y', 'n', 'p', 'x'], true)) {
                    throw new \InvalidArgumentException('Invalid choice.');
                }

                return $value;
            },
            null,
            'y'
        );
    }
}
