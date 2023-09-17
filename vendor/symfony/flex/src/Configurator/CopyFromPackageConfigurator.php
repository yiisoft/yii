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
class CopyFromPackageConfigurator extends AbstractConfigurator
{
    public function configure(Recipe $recipe, $config, Lock $lock, array $options = [])
    {
        $this->write('Copying files from package');
        $packageDir = $this->composer->getInstallationManager()->getInstallPath($recipe->getPackage());
        $options = array_merge($this->options->toArray(), $options);

        $files = $this->getFilesToCopy($config, $packageDir);
        foreach ($files as $source => $target) {
            $this->copyFile($source, $target, $options);
        }
    }

    public function unconfigure(Recipe $recipe, $config, Lock $lock)
    {
        $this->write('Removing files from package');
        $packageDir = $this->composer->getInstallationManager()->getInstallPath($recipe->getPackage());
        $this->removeFiles($config, $packageDir, $this->options->get('root-dir'));
    }

    public function update(RecipeUpdate $recipeUpdate, array $originalConfig, array $newConfig): void
    {
        $packageDir = $this->composer->getInstallationManager()->getInstallPath($recipeUpdate->getNewRecipe()->getPackage());
        foreach ($originalConfig as $source => $target) {
            if (isset($newConfig[$source])) {
                // path is in both, we cannot update
                $recipeUpdate->addCopyFromPackagePath(
                    $packageDir.'/'.$source,
                    $this->options->expandTargetDir($target)
                );

                unset($newConfig[$source]);
            }

            // if any paths were removed from the recipe, we'll keep them
        }

        // any remaining files are new, and we can copy them
        foreach ($this->getFilesToCopy($newConfig, $packageDir) as $source => $target) {
            if (!file_exists($source)) {
                throw new \LogicException(sprintf('File "%s" does not exist!', $source));
            }

            $recipeUpdate->setNewFile($target, file_get_contents($source));
        }
    }

    private function getFilesToCopy(array $manifest, string $from): array
    {
        $files = [];
        foreach ($manifest as $source => $target) {
            $target = $this->options->expandTargetDir($target);
            if ('/' === substr($source, -1)) {
                $files = array_merge($files, $this->getFilesForDir($this->path->concatenate([$from, $source]), $this->path->concatenate([$target])));

                continue;
            }

            $files[$this->path->concatenate([$from, $source])] = $target;
        }

        return $files;
    }

    private function removeFiles(array $manifest, string $from, string $to)
    {
        foreach ($manifest as $source => $target) {
            $target = $this->options->expandTargetDir($target);
            if ('/' === substr($source, -1)) {
                $this->removeFilesFromDir($this->path->concatenate([$from, $source]), $this->path->concatenate([$to, $target]));
            } else {
                $targetPath = $this->path->concatenate([$to, $target]);
                if (file_exists($targetPath)) {
                    @unlink($targetPath);
                    $this->write(sprintf('  Removed <fg=green>"%s"</>', $this->path->relativize($targetPath)));
                }
            }
        }
    }

    private function getFilesForDir(string $source, string $target): array
    {
        $iterator = $this->createSourceIterator($source, \RecursiveIteratorIterator::SELF_FIRST);
        $files = [];
        foreach ($iterator as $item) {
            $targetPath = $this->path->concatenate([$target, $iterator->getSubPathName()]);

            $files[(string) $item] = $targetPath;
        }

        return $files;
    }

    /**
     * @param string $source The absolute path to the source file
     * @param string $target The relative (to root dir) path to the target
     */
    public function copyFile(string $source, string $target, array $options)
    {
        $target = $this->options->get('root-dir').'/'.$target;
        if (is_dir($source)) {
            // directory will be created when a file is copied to it
            return;
        }

        $overwrite = $options['force'] ?? false;
        if (!$this->options->shouldWriteFile($target, $overwrite)) {
            return;
        }

        if (!file_exists($source)) {
            throw new \LogicException(sprintf('File "%s" does not exist!', $source));
        }

        if (!file_exists(\dirname($target))) {
            mkdir(\dirname($target), 0777, true);
            $this->write(sprintf('  Created <fg=green>"%s"</>', $this->path->relativize(\dirname($target))));
        }

        file_put_contents($target, $this->options->expandTargetDir(file_get_contents($source)));
        @chmod($target, fileperms($target) | (fileperms($source) & 0111));
        $this->write(sprintf('  Created <fg=green>"%s"</>', $this->path->relativize($target)));
    }

    private function removeFilesFromDir(string $source, string $target)
    {
        if (!is_dir($source)) {
            return;
        }
        $iterator = $this->createSourceIterator($source, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $item) {
            $targetPath = $this->path->concatenate([$target, $iterator->getSubPathName()]);
            if ($item->isDir()) {
                // that removes the dir only if it is empty
                @rmdir($targetPath);
                $this->write(sprintf('  Removed directory <fg=green>"%s"</>', $this->path->relativize($targetPath)));
            } else {
                @unlink($targetPath);
                $this->write(sprintf('  Removed <fg=green>"%s"</>', $this->path->relativize($targetPath)));
            }
        }
    }

    private function createSourceIterator(string $source, int $mode): \RecursiveIteratorIterator
    {
        return new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), $mode);
    }
}
