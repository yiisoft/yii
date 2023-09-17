<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Flex\Update;

class DiffHelper
{
    public static function removeFilesFromPatch(string $patch, array $files, array &$removedPatches): string
    {
        foreach ($files as $filename) {
            $start = strpos($patch, sprintf('diff --git a/%s b/%s', $filename, $filename));
            if (false === $start) {
                throw new \LogicException(sprintf('Could not find file "%s" in the patch.', $filename));
            }

            $end = strpos($patch, 'diff --git a/', $start + 1);
            $contentBefore = substr($patch, 0, $start);
            if (false === $end) {
                // last patch in the file
                $removedPatches[$filename] = rtrim(substr($patch, $start), "\n");
                $patch = rtrim($contentBefore, "\n");

                continue;
            }

            $removedPatches[$filename] = rtrim(substr($patch, $start, $end - $start), "\n");
            $patch = $contentBefore.substr($patch, $end);
        }

        // valid patches end with a blank line
        if ($patch && "\n" !== substr($patch, \strlen($patch) - 1, 1)) {
            $patch = $patch."\n";
        }

        return $patch;
    }
}
