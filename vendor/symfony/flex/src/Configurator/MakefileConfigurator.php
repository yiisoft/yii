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
class MakefileConfigurator extends AbstractConfigurator
{
    public function configure(Recipe $recipe, $definitions, Lock $lock, array $options = [])
    {
        $this->write('Adding Makefile entries');

        $this->configureMakefile($recipe, $definitions, $options['force'] ?? false);
    }

    public function unconfigure(Recipe $recipe, $vars, Lock $lock)
    {
        if (!file_exists($makefile = $this->options->get('root-dir').'/Makefile')) {
            return;
        }

        $contents = preg_replace(sprintf('{%s*###> %s ###.*###< %s ###%s+}s', "\n", $recipe->getName(), $recipe->getName(), "\n"), "\n", file_get_contents($makefile), -1, $count);
        if (!$count) {
            return;
        }

        $this->write(sprintf('Removing Makefile entries from %s', $makefile));
        if (!trim($contents)) {
            @unlink($makefile);
        } else {
            file_put_contents($makefile, ltrim($contents, "\r\n"));
        }
    }

    public function update(RecipeUpdate $recipeUpdate, array $originalConfig, array $newConfig): void
    {
        $recipeUpdate->setOriginalFile(
            'Makefile',
            $this->getContentsAfterApplyingRecipe($recipeUpdate->getRootDir(), $recipeUpdate->getOriginalRecipe(), $originalConfig)
        );

        $recipeUpdate->setNewFile(
            'Makefile',
            $this->getContentsAfterApplyingRecipe($recipeUpdate->getRootDir(), $recipeUpdate->getNewRecipe(), $newConfig)
        );
    }

    private function configureMakefile(Recipe $recipe, array $definitions, bool $update)
    {
        $makefile = $this->options->get('root-dir').'/Makefile';
        if (!$update && $this->isFileMarked($recipe, $makefile)) {
            return;
        }

        $data = $this->options->expandTargetDir(implode("\n", $definitions));
        $data = $this->markData($recipe, $data);
        $data = "\n".ltrim($data, "\r\n");

        if (!file_exists($makefile)) {
            $envKey = $this->options->get('runtime')['env_var_name'] ?? 'APP_ENV';
            $dotenvPath = $this->options->get('runtime')['dotenv_path'] ?? '.env';
            file_put_contents(
                $this->options->get('root-dir').'/Makefile',
                <<<EOF
ifndef {$envKey}
	include {$dotenvPath}
endif

.DEFAULT_GOAL := help
.PHONY: help
help:
	@awk 'BEGIN {FS = ":.*?## "}; /^[a-zA-Z-]+:.*?## .*$$/ {printf "\033[32m%-15s\033[0m %s\\n", $$1, $$2}' Makefile | sort

EOF
            );
        }

        if (!$this->updateData($makefile, $data)) {
            file_put_contents($makefile, $data, \FILE_APPEND);
        }
    }

    private function getContentsAfterApplyingRecipe(string $rootDir, Recipe $recipe, array $definitions): ?string
    {
        if (0 === \count($definitions)) {
            return null;
        }

        $file = $rootDir.'/Makefile';
        $originalContents = file_exists($file) ? file_get_contents($file) : null;

        $this->configureMakefile(
            $recipe,
            $definitions,
            true
        );

        $updatedContents = file_exists($file) ? file_get_contents($file) : null;

        if (null === $originalContents) {
            if (file_exists($file)) {
                unlink($file);
            }
        } else {
            file_put_contents($file, $originalContents);
        }

        return $updatedContents;
    }
}
