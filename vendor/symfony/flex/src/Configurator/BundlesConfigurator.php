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
class BundlesConfigurator extends AbstractConfigurator
{
    public function configure(Recipe $recipe, $bundles, Lock $lock, array $options = [])
    {
        $this->write('Enabling the package as a Symfony bundle');
        $registered = $this->configureBundles($bundles);
        $this->dump($this->getConfFile(), $registered);
    }

    public function unconfigure(Recipe $recipe, $bundles, Lock $lock)
    {
        $this->write('Disabling the Symfony bundle');
        $file = $this->getConfFile();
        if (!file_exists($file)) {
            return;
        }

        $registered = $this->load($file);
        foreach (array_keys($this->prepareBundles($bundles)) as $class) {
            unset($registered[$class]);
        }
        $this->dump($file, $registered);
    }

    public function update(RecipeUpdate $recipeUpdate, array $originalConfig, array $newConfig): void
    {
        $originalBundles = $this->configureBundles($originalConfig, true);
        $recipeUpdate->setOriginalFile(
            $this->getLocalConfFile(),
            $this->buildContents($originalBundles)
        );

        $newBundles = $this->configureBundles($newConfig, true);
        $recipeUpdate->setNewFile(
            $this->getLocalConfFile(),
            $this->buildContents($newBundles)
        );
    }

    private function configureBundles(array $bundles, bool $resetEnvironments = false): array
    {
        $file = $this->getConfFile();
        $registered = $this->load($file);
        $classes = $this->prepareBundles($bundles);
        if (isset($classes[$fwb = 'Symfony\Bundle\FrameworkBundle\FrameworkBundle'])) {
            foreach ($classes[$fwb] as $env) {
                $registered[$fwb][$env] = true;
            }
            unset($classes[$fwb]);
        }
        foreach ($classes as $class => $envs) {
            // do not override existing configured envs for a bundle
            if (!isset($registered[$class]) || $resetEnvironments) {
                if ($resetEnvironments) {
                    // used during calculating an "upgrade"
                    // here, we want to "undo" the bundle's configuration entirely
                    // then re-add it fresh, in case some environments have been
                    // removed in an updated version of the recipe
                    $registered[$class] = [];
                }

                foreach ($envs as $env) {
                    $registered[$class][$env] = true;
                }
            }
        }

        return $registered;
    }

    private function prepareBundles(array $bundles): array
    {
        foreach ($bundles as $class => $envs) {
            $bundles[ltrim($class, '\\')] = $envs;
        }

        return $bundles;
    }

    private function load(string $file): array
    {
        $bundles = file_exists($file) ? (require $file) : [];
        if (!\is_array($bundles)) {
            $bundles = [];
        }

        return $bundles;
    }

    private function dump(string $file, array $bundles)
    {
        $contents = $this->buildContents($bundles);

        if (!is_dir(\dirname($file))) {
            mkdir(\dirname($file), 0777, true);
        }

        file_put_contents($file, $contents);

        if (\function_exists('opcache_invalidate')) {
            opcache_invalidate($file);
        }
    }

    private function buildContents(array $bundles): string
    {
        $contents = "<?php\n\nreturn [\n";
        foreach ($bundles as $class => $envs) {
            $contents .= "    $class::class => [";
            foreach ($envs as $env => $value) {
                $booleanValue = var_export($value, true);
                $contents .= "'$env' => $booleanValue, ";
            }
            $contents = substr($contents, 0, -2)."],\n";
        }
        $contents .= "];\n";

        return $contents;
    }

    private function getConfFile(): string
    {
        return $this->options->get('root-dir').'/'.$this->getLocalConfFile();
    }

    private function getLocalConfFile(): string
    {
        return $this->options->expandTargetDir('%CONFIG_DIR%/bundles.php');
    }
}
