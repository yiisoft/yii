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

use Symfony\Flex\Lock;
use Symfony\Flex\Recipe;
use Symfony\Flex\Update\RecipeUpdate;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class EnvConfigurator extends AbstractConfigurator
{
    public function configure(Recipe $recipe, $vars, Lock $lock, array $options = [])
    {
        $this->write('Adding environment variable defaults');

        $this->configureEnvDist($recipe, $vars, $options['force'] ?? false);
        if (!file_exists($this->options->get('root-dir').'/'.($this->options->get('runtime')['dotenv_path'] ?? '.env').'.test')) {
            $this->configurePhpUnit($recipe, $vars, $options['force'] ?? false);
        }
    }

    public function unconfigure(Recipe $recipe, $vars, Lock $lock)
    {
        $this->unconfigureEnvFiles($recipe, $vars);
        $this->unconfigurePhpUnit($recipe, $vars);
    }

    public function update(RecipeUpdate $recipeUpdate, array $originalConfig, array $newConfig): void
    {
        $recipeUpdate->addOriginalFiles(
            $this->getContentsAfterApplyingRecipe($recipeUpdate->getRootDir(), $recipeUpdate->getOriginalRecipe(), $originalConfig)
        );

        $recipeUpdate->addNewFiles(
            $this->getContentsAfterApplyingRecipe($recipeUpdate->getRootDir(), $recipeUpdate->getNewRecipe(), $newConfig)
        );
    }

    private function configureEnvDist(Recipe $recipe, $vars, bool $update)
    {
        $dotenvPath = $this->options->get('runtime')['dotenv_path'] ?? '.env';

        foreach ([$dotenvPath.'.dist', $dotenvPath] as $file) {
            $env = $this->options->get('root-dir').'/'.$file;
            if (!is_file($env)) {
                continue;
            }

            if (!$update && $this->isFileMarked($recipe, $env)) {
                continue;
            }

            $data = '';
            foreach ($vars as $key => $value) {
                $existingValue = $update ? $this->findExistingValue($key, $env, $recipe) : null;
                $value = $this->evaluateValue($value, $existingValue);
                if ('#' === $key[0] && is_numeric(substr($key, 1))) {
                    if ('' === $value) {
                        $data .= "#\n";
                    } else {
                        $data .= '# '.$value."\n";
                    }

                    continue;
                }

                $value = $this->options->expandTargetDir($value);
                if (false !== strpbrk($value, " \t\n&!\"")) {
                    $value = '"'.str_replace(['\\', '"', "\t", "\n"], ['\\\\', '\\"', '\t', '\n'], $value).'"';
                }
                $data .= "$key=$value\n";
            }
            $data = $this->markData($recipe, $data);

            if (!$this->updateData($env, $data)) {
                file_put_contents($env, $data, \FILE_APPEND);
            }
        }
    }

    private function configurePhpUnit(Recipe $recipe, $vars, bool $update)
    {
        foreach (['phpunit.xml.dist', 'phpunit.xml'] as $file) {
            $phpunit = $this->options->get('root-dir').'/'.$file;
            if (!is_file($phpunit)) {
                continue;
            }

            if (!$update && $this->isFileXmlMarked($recipe, $phpunit)) {
                continue;
            }

            $data = '';
            foreach ($vars as $key => $value) {
                $value = $this->evaluateValue($value);
                if ('#' === $key[0]) {
                    if (is_numeric(substr($key, 1))) {
                        $doc = new \DOMDocument();
                        $data .= '        '.$doc->saveXML($doc->createComment(' '.$value.' '))."\n";
                    } else {
                        $value = $this->options->expandTargetDir($value);
                        $doc = new \DOMDocument();
                        $fragment = $doc->createElement('env');
                        $fragment->setAttribute('name', substr($key, 1));
                        $fragment->setAttribute('value', $value);
                        $data .= '        '.str_replace(['<', '/>'], ['<!-- ', ' -->'], $doc->saveXML($fragment))."\n";
                    }
                } else {
                    $value = $this->options->expandTargetDir($value);
                    $doc = new \DOMDocument();
                    $fragment = $doc->createElement('env');
                    $fragment->setAttribute('name', $key);
                    $fragment->setAttribute('value', $value);
                    $data .= '        '.$doc->saveXML($fragment)."\n";
                }
            }
            $data = $this->markXmlData($recipe, $data);

            if (!$this->updateData($phpunit, $data)) {
                file_put_contents($phpunit, preg_replace('{^(\s+</php>)}m', $data.'$1', file_get_contents($phpunit)));
            }
        }
    }

    private function unconfigureEnvFiles(Recipe $recipe, $vars)
    {
        $dotenvPath = $this->options->get('runtime')['dotenv_path'] ?? '.env';

        foreach ([$dotenvPath, $dotenvPath.'.dist'] as $file) {
            $env = $this->options->get('root-dir').'/'.$file;
            if (!file_exists($env)) {
                continue;
            }

            $contents = preg_replace(sprintf('{%s*###> %s ###.*###< %s ###%s+}s', "\n", $recipe->getName(), $recipe->getName(), "\n"), "\n", file_get_contents($env), -1, $count);
            if (!$count) {
                continue;
            }

            $this->write(sprintf('Removing environment variables from %s', $file));
            file_put_contents($env, $contents);
        }
    }

    private function unconfigurePhpUnit(Recipe $recipe, $vars)
    {
        foreach (['phpunit.xml.dist', 'phpunit.xml'] as $file) {
            $phpunit = $this->options->get('root-dir').'/'.$file;
            if (!is_file($phpunit)) {
                continue;
            }

            $contents = preg_replace(sprintf('{%s*\s+<!-- ###\+ %s ### -->.*<!-- ###- %s ### -->%s+}s', "\n", $recipe->getName(), $recipe->getName(), "\n"), "\n", file_get_contents($phpunit), -1, $count);
            if (!$count) {
                continue;
            }

            $this->write(sprintf('Removing environment variables from %s', $file));
            file_put_contents($phpunit, $contents);
        }
    }

    /**
     * Evaluates expressions like %generate(secret)%.
     *
     * If $originalValue is passed, and the value contains an expression.
     * the $originalValue is used.
     */
    private function evaluateValue($value, string $originalValue = null)
    {
        if ('%generate(secret)%' === $value) {
            if (null !== $originalValue) {
                return $originalValue;
            }

            return $this->generateRandomBytes();
        }
        if (preg_match('~^%generate\(secret,\s*([0-9]+)\)%$~', $value, $matches)) {
            if (null !== $originalValue) {
                return $originalValue;
            }

            return $this->generateRandomBytes($matches[1]);
        }

        return $value;
    }

    private function generateRandomBytes($length = 16)
    {
        return bin2hex(random_bytes($length));
    }

    private function getContentsAfterApplyingRecipe(string $rootDir, Recipe $recipe, array $vars): array
    {
        $dotenvPath = $this->options->get('runtime')['dotenv_path'] ?? '.env';
        $files = [$dotenvPath, $dotenvPath.'.dist', 'phpunit.xml.dist', 'phpunit.xml'];

        if (0 === \count($vars)) {
            return array_fill_keys($files, null);
        }

        $originalContents = [];
        foreach ($files as $file) {
            $originalContents[$file] = file_exists($rootDir.'/'.$file) ? file_get_contents($rootDir.'/'.$file) : null;
        }

        $this->configureEnvDist(
            $recipe,
            $vars,
            true
        );

        if (!file_exists($rootDir.'/'.$dotenvPath.'.test')) {
            $this->configurePhpUnit(
                $recipe,
                $vars,
                true
            );
        }

        $updatedContents = [];
        foreach ($files as $file) {
            $updatedContents[$file] = file_exists($rootDir.'/'.$file) ? file_get_contents($rootDir.'/'.$file) : null;
        }

        foreach ($originalContents as $file => $contents) {
            if (null === $contents) {
                if (file_exists($rootDir.'/'.$file)) {
                    unlink($rootDir.'/'.$file);
                }
            } else {
                file_put_contents($rootDir.'/'.$file, $contents);
            }
        }

        return $updatedContents;
    }

    /**
     * Attempts to find the existing value of an environment variable.
     */
    private function findExistingValue(string $var, string $filename, Recipe $recipe): ?string
    {
        if (!file_exists($filename)) {
            return null;
        }

        $contents = file_get_contents($filename);
        $section = $this->extractSection($recipe, $contents);
        if (!$section) {
            return null;
        }

        $lines = explode("\n", $section);
        foreach ($lines as $line) {
            if (0 !== strpos($line, sprintf('%s=', $var))) {
                continue;
            }

            return trim(substr($line, \strlen($var) + 1));
        }

        return null;
    }
}
