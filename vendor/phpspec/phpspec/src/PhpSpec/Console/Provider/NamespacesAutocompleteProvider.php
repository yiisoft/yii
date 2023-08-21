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

namespace PhpSpec\Console\Provider;

use PhpSpec\Locator\ResourceLocator;
use PhpSpec\Locator\SrcPathLocator;
use Symfony\Component\Finder\Finder;

final class NamespacesAutocompleteProvider
{
    /**
     * @var Finder
     */
    private $finder;

    private $paths = [];

    public function __construct(Finder $finder, array $locators)
    {
        foreach ($locators as $locator) {
            if ($locator instanceof SrcPathLocator) {
                $this->paths[] = $locator->getFullSrcPath();
            }
        }

        $this->finder = $finder;
    }

    /**
     * Get namespaces from paths.
     *
     * @return array of namespases
     */
    public function getNamespaces()
    {
        $namespaces = [];
        foreach ($this->finder->files()->name('*.php')->in($this->paths) as $phpFile) {
            $tokens = token_get_all($phpFile->getContents());
            foreach ($tokens as $index => $token) {
                if (!is_array($token) || T_NAMESPACE !== $token[0]) {
                    continue;
                }

                $shift = 2;
                $namespace = '';

                while ($tokens[$index + $shift] !== ';') {
                    $namespace .= $tokens[$index + $shift][1];
                    $shift++;
                }

                $namespaceParts = explode('\\', $namespace);
                $namespace = '';

                foreach ($namespaceParts as $part) {
                    $namespace .= $part;
                    $namespace .= '\\';
                    $namespaces[] = $namespace;
                }

                break;
            }
        }

        return array_unique($namespaces);
    }
}
