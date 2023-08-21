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

namespace PhpSpec\CodeGenerator;

use PhpSpec\Util\Filesystem;

/**
 * Template renderer class can render templates in registered locations. It comes
 * with a simple placeholder string replacement for specified fields
 */
class TemplateRenderer
{
    /**
     * @var array
     */
    private $locations = array();

    /**
     * @var Filesystem
     */
    private $filesystem;

    
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    
    public function setLocations(array $locations): void
    {
        $this->locations = array_map(array($this, 'normalizeLocation'), $locations);
    }

    public function prependLocation(string $location): void
    {
        array_unshift($this->locations, $this->normalizeLocation($location));
    }

    public function appendLocation(string $location): void
    {
        array_push($this->locations, $this->normalizeLocation($location));
    }

    public function getLocations(): array
    {
        return $this->locations;
    }

    public function render(string $name, array $values = array()): string
    {
        foreach ($this->locations as $location) {
            $path = $location.DIRECTORY_SEPARATOR.$this->normalizeLocation($name, true).'.tpl';

            if ($this->filesystem->pathExists($path)) {
                return $this->renderString($this->filesystem->getFileContents($path), $values);
            }
        }
        return '';
    }

    public function renderString(string $template, array $values = array()): string
    {
        return strtr($template, $values);
    }

    private function normalizeLocation(string $location, bool $trimLeft = false): string
    {
        $location = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $location);
        $location = rtrim($location, DIRECTORY_SEPARATOR);

        if ($trimLeft) {
            $location = ltrim($location, DIRECTORY_SEPARATOR);
        }

        return $location;
    }
}
