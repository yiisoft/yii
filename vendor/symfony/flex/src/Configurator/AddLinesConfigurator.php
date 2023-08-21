<?php

namespace Symfony\Flex\Configurator;

use Composer\IO\IOInterface;
use Symfony\Flex\Lock;
use Symfony\Flex\Recipe;
use Symfony\Flex\Update\RecipeUpdate;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 * @author Ryan Weaver <ryan@symfonycasts.com>
 */
class AddLinesConfigurator extends AbstractConfigurator
{
    private const POSITION_TOP = 'top';
    private const POSITION_BOTTOM = 'bottom';
    private const POSITION_AFTER_TARGET = 'after_target';

    private const VALID_POSITIONS = [
        self::POSITION_TOP,
        self::POSITION_BOTTOM,
        self::POSITION_AFTER_TARGET,
    ];

    /**
     * Holds file contents for files that have been loaded.
     * This allows us to "change" the contents of a file multiple
     * times before we actually write it out.
     *
     * @var string[]
     */
    private $fileContents = [];

    public function configure(Recipe $recipe, $config, Lock $lock, array $options = []): void
    {
        $this->fileContents = [];
        $this->executeConfigure($recipe, $config);

        foreach ($this->fileContents as $file => $contents) {
            $this->write(sprintf('[add-lines] Patching file "%s"', $this->relativize($file)));
            file_put_contents($file, $contents);
        }
    }

    public function unconfigure(Recipe $recipe, $config, Lock $lock): void
    {
        $this->fileContents = [];
        $this->executeUnconfigure($recipe, $config);

        foreach ($this->fileContents as $file => $change) {
            $this->write(sprintf('[add-lines] Reverting file "%s"', $this->relativize($file)));
            file_put_contents($file, $change);
        }
    }

    public function update(RecipeUpdate $recipeUpdate, array $originalConfig, array $newConfig): void
    {
        // manually check for "requires", as unconfigure ignores it
        $originalConfig = array_filter($originalConfig, function ($item) {
            return !isset($item['requires']) || $this->isPackageInstalled($item['requires']);
        });

        // reset the file content cache
        $this->fileContents = [];
        $this->executeUnconfigure($recipeUpdate->getOriginalRecipe(), $originalConfig);
        $this->executeConfigure($recipeUpdate->getNewRecipe(), $newConfig);
        $newFiles = [];
        $originalFiles = [];
        foreach ($this->fileContents as $file => $contents) {
            // set the original file to the current contents
            $originalFiles[$this->relativize($file)] = file_get_contents($file);
            // and the new file where the old recipe was unconfigured, and the new configured
            $newFiles[$this->relativize($file)] = $contents;
        }
        $recipeUpdate->addOriginalFiles($originalFiles);
        $recipeUpdate->addNewFiles($newFiles);
    }

    public function executeConfigure(Recipe $recipe, $config): void
    {
        foreach ($config as $patch) {
            if (!isset($patch['file'])) {
                $this->write(sprintf('The "file" key is required for the "add-lines" configurator for recipe "%s". Skipping', $recipe->getName()));

                continue;
            }

            if (isset($patch['requires']) && !$this->isPackageInstalled($patch['requires'])) {
                continue;
            }

            if (!isset($patch['content'])) {
                $this->write(sprintf('The "content" key is required for the "add-lines" configurator for recipe "%s". Skipping', $recipe->getName()));

                continue;
            }
            $content = $patch['content'];

            $file = $this->path->concatenate([$this->options->get('root-dir'), $patch['file']]);
            $warnIfMissing = isset($patch['warn_if_missing']) && $patch['warn_if_missing'];
            if (!is_file($file)) {
                $this->write([
                    sprintf('Could not add lines to file <info>%s</info> as it does not exist. Missing lines:', $patch['file']),
                    '<comment>"""</comment>',
                    $content,
                    '<comment>"""</comment>',
                    '',
                ], $warnIfMissing ? IOInterface::NORMAL : IOInterface::VERBOSE);

                continue;
            }

            if (!isset($patch['position'])) {
                $this->write(sprintf('The "position" key is required for the "add-lines" configurator for recipe "%s". Skipping', $recipe->getName()));

                continue;
            }
            $position = $patch['position'];
            if (!\in_array($position, self::VALID_POSITIONS, true)) {
                $this->write(sprintf('The "position" key must be one of "%s" for the "add-lines" configurator for recipe "%s". Skipping', implode('", "', self::VALID_POSITIONS), $recipe->getName()));

                continue;
            }

            if (self::POSITION_AFTER_TARGET === $position && !isset($patch['target'])) {
                $this->write(sprintf('The "target" key is required when "position" is "%s" for the "add-lines" configurator for recipe "%s". Skipping', self::POSITION_AFTER_TARGET, $recipe->getName()));

                continue;
            }
            $target = isset($patch['target']) ? $patch['target'] : null;

            $newContents = $this->getPatchedContents($file, $content, $position, $target, $warnIfMissing);
            $this->fileContents[$file] = $newContents;
        }
    }

    public function executeUnconfigure(Recipe $recipe, $config): void
    {
        foreach ($config as $patch) {
            if (!isset($patch['file'])) {
                $this->write(sprintf('The "file" key is required for the "add-lines" configurator for recipe "%s". Skipping', $recipe->getName()));

                continue;
            }

            // Ignore "requires": the target packages may have just become uninstalled.
            // Checking for a "content" match is enough.

            $file = $this->path->concatenate([$this->options->get('root-dir'), $patch['file']]);
            if (!is_file($file)) {
                continue;
            }

            if (!isset($patch['content'])) {
                $this->write(sprintf('The "content" key is required for the "add-lines" configurator for recipe "%s". Skipping', $recipe->getName()));

                continue;
            }
            $value = $patch['content'];

            $newContents = $this->getUnPatchedContents($file, $value);
            $this->fileContents[$file] = $newContents;
        }
    }

    private function getPatchedContents(string $file, string $value, string $position, ?string $target, bool $warnIfMissing): string
    {
        $fileContents = $this->readFile($file);

        if (false !== strpos($fileContents, $value)) {
            return $fileContents; // already includes value, skip
        }

        switch ($position) {
            case self::POSITION_BOTTOM:
                $fileContents .= "\n".$value;

                break;
            case self::POSITION_TOP:
                $fileContents = $value."\n".$fileContents;

                break;
            case self::POSITION_AFTER_TARGET:
                $lines = explode("\n", $fileContents);
                $targetFound = false;
                foreach ($lines as $key => $line) {
                    if (false !== strpos($line, $target)) {
                        array_splice($lines, $key + 1, 0, $value);
                        $targetFound = true;

                        break;
                    }
                }
                $fileContents = implode("\n", $lines);

                if (!$targetFound) {
                    $this->write([
                        sprintf('Could not add lines after "%s" as no such string was found in "%s". Missing lines:', $target, $file),
                        '<comment>"""</comment>',
                        $value,
                        '<comment>"""</comment>',
                        '',
                    ], $warnIfMissing ? IOInterface::NORMAL : IOInterface::VERBOSE);
                }

                break;
        }

        return $fileContents;
    }

    private function getUnPatchedContents(string $file, $value): string
    {
        $fileContents = $this->readFile($file);

        if (false === strpos($fileContents, $value)) {
            return $fileContents; // value already gone!
        }

        if (false !== strpos($fileContents, "\n".$value)) {
            $value = "\n".$value;
        } elseif (false !== strpos($fileContents, $value."\n")) {
            $value = $value."\n";
        }

        $position = strpos($fileContents, $value);

        return substr_replace($fileContents, '', $position, \strlen($value));
    }

    private function isPackageInstalled($packages): bool
    {
        if (\is_string($packages)) {
            $packages = [$packages];
        }

        $installedRepo = $this->composer->getRepositoryManager()->getLocalRepository();

        foreach ($packages as $packageName) {
            if (null === $installedRepo->findPackage($packageName, '*')) {
                return false;
            }
        }

        return true;
    }

    private function relativize(string $path): string
    {
        $rootDir = $this->options->get('root-dir');
        if (0 === strpos($path, $rootDir)) {
            $path = substr($path, \strlen($rootDir) + 1);
        }

        return ltrim($path, '/\\');
    }

    private function readFile(string $file): string
    {
        if (isset($this->fileContents[$file])) {
            return $this->fileContents[$file];
        }

        $fileContents = file_get_contents($file);
        $this->fileContents[$file] = $fileContents;

        return $fileContents;
    }
}
