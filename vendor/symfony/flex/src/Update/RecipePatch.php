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

class RecipePatch
{
    private $patch;
    private $blobs;
    private $deletedFiles;
    private $removedPatches;

    public function __construct(string $patch, array $blobs, array $deletedFiles, array $removedPatches = [])
    {
        $this->patch = $patch;
        $this->blobs = $blobs;
        $this->deletedFiles = $deletedFiles;
        $this->removedPatches = $removedPatches;
    }

    public function getPatch(): string
    {
        return $this->patch;
    }

    public function getBlobs(): array
    {
        return $this->blobs;
    }

    public function getDeletedFiles(): array
    {
        return $this->deletedFiles;
    }

    /**
     * Patches for modified files that were removed because the file
     * has been deleted in the user's project.
     */
    public function getRemovedPatches(): array
    {
        return $this->removedPatches;
    }
}
