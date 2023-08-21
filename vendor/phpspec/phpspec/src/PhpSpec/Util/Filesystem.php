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

namespace PhpSpec\Util;

use Symfony\Component\Finder\Finder;

class Filesystem
{
    
    public function pathExists(string $path): bool
    {
        return file_exists($path);
    }

    
    public function getFileContents(string $path): string
    {
        return file_get_contents($path);
    }

    
    public function putFileContents(string $path, string $content)
    {
        file_put_contents($path, $content);
    }

    
    public function isDirectory(string $path): bool
    {
        return is_dir($path);
    }

    
    public function makeDirectory(string $path): void
    {
        mkdir($path, 0777, true);
    }

    /**
     * @return \SplFileInfo[]
     */
    public function findSpecFilesIn(string $path): array
    {
        $finder = Finder::create()
            ->files()
            ->name('*Spec.php')
            ->followLinks()
            ->sortByName()
            ->in($path)
        ;

        return iterator_to_array($finder);
    }
}
