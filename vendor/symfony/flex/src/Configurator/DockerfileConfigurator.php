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
 * Adds commands to a Dockerfile.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class DockerfileConfigurator extends AbstractConfigurator
{
    public function configure(Recipe $recipe, $config, Lock $lock, array $options = [])
    {
        if (!DockerComposeConfigurator::shouldConfigureDockerRecipe($this->composer, $this->io, $recipe)) {
            return;
        }

        $this->configureDockerfile($recipe, $config, $options['force'] ?? false);
    }

    public function unconfigure(Recipe $recipe, $config, Lock $lock)
    {
        if (!file_exists($dockerfile = $this->options->get('root-dir').'/Dockerfile')) {
            return;
        }

        $name = $recipe->getName();
        $contents = preg_replace(sprintf('{%s+###> %s ###.*?###< %s ###%s+}s', "\n", $name, $name, "\n"), "\n", file_get_contents($dockerfile), -1, $count);
        if (!$count) {
            return;
        }

        $this->write('Removing Dockerfile entries');
        file_put_contents($dockerfile, ltrim($contents, "\n"));
    }

    public function update(RecipeUpdate $recipeUpdate, array $originalConfig, array $newConfig): void
    {
        if (!DockerComposeConfigurator::shouldConfigureDockerRecipe($this->composer, $this->io, $recipeUpdate->getNewRecipe())) {
            return;
        }

        $recipeUpdate->setOriginalFile(
            'Dockerfile',
            $this->getContentsAfterApplyingRecipe($recipeUpdate->getOriginalRecipe(), $originalConfig)
        );

        $recipeUpdate->setNewFile(
            'Dockerfile',
            $this->getContentsAfterApplyingRecipe($recipeUpdate->getNewRecipe(), $newConfig)
        );
    }

    private function configureDockerfile(Recipe $recipe, array $config, bool $update, bool $writeOutput = true): void
    {
        $dockerfile = $this->options->get('root-dir').'/Dockerfile';
        if (!file_exists($dockerfile) || (!$update && $this->isFileMarked($recipe, $dockerfile))) {
            return;
        }

        if ($writeOutput) {
            $this->write('Adding Dockerfile entries');
        }

        $data = ltrim($this->markData($recipe, implode("\n", $config)), "\n");
        if ($this->updateData($dockerfile, $data)) {
            // done! Existing spot updated
            return;
        }

        $lines = [];
        foreach (file($dockerfile) as $line) {
            $lines[] = $line;
            if (!preg_match('/^###> recipes ###$/', $line)) {
                continue;
            }

            $lines[] = $data;
        }

        file_put_contents($dockerfile, implode('', $lines));
    }

    private function getContentsAfterApplyingRecipe(Recipe $recipe, array $config): ?string
    {
        if (0 === \count($config)) {
            return null;
        }

        $dockerfile = $this->options->get('root-dir').'/Dockerfile';
        $originalContents = file_exists($dockerfile) ? file_get_contents($dockerfile) : null;

        $this->configureDockerfile(
            $recipe,
            $config,
            true,
            false
        );

        $updatedContents = file_exists($dockerfile) ? file_get_contents($dockerfile) : null;

        if (null === $originalContents) {
            if (file_exists($dockerfile)) {
                unlink($dockerfile);
            }
        } else {
            file_put_contents($dockerfile, $originalContents);
        }

        return $updatedContents;
    }
}
