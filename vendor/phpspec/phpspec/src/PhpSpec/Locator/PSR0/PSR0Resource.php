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

namespace PhpSpec\Locator\PSR0;

use PhpSpec\Locator\Resource;

final class PSR0Resource implements Resource
{
    /**
     * @var array
     */
    private $parts;
    /**
     * @var PSR0Locator
     */
    private $locator;

    
    public function __construct(array $parts, PSR0Locator $locator)
    {
        $this->parts   = $parts;
        $this->locator = $locator;
    }

    
    public function getName(): string
    {
        return end($this->parts);
    }

    
    public function getSpecName(): string
    {
        return $this->getName().'Spec';
    }

    
    public function getSrcFilename(): string
    {
        if ($this->locator->isPSR4()) {
            return $this->locator->getFullSrcPath().implode(DIRECTORY_SEPARATOR, $this->parts).'.php';
        }

        $nsParts   = $this->parts;
        $classname = array_pop($nsParts);
        $parts     = array_merge($nsParts, explode('_', $classname));

        return $this->locator->getFullSrcPath().implode(DIRECTORY_SEPARATOR, $parts).'.php';
    }

    
    public function getSrcNamespace(): string
    {
        $nsParts = $this->parts;
        array_pop($nsParts);

        return rtrim($this->locator->getSrcNamespace().implode('\\', $nsParts), '\\');
    }

    
    public function getSrcClassname(): string
    {
        return $this->locator->getSrcNamespace().implode('\\', $this->parts);
    }

    
    public function getSpecFilename(): string
    {
        if ($this->locator->isPSR4()) {
            return $this->locator->getFullSpecPath().
                implode(DIRECTORY_SEPARATOR, $this->parts).'Spec.php';
        }

        $nsParts   = $this->parts;
        $classname = array_pop($nsParts);
        $parts     = array_merge($nsParts, explode('_', $classname));

        return $this->locator->getFullSpecPath().
            implode(DIRECTORY_SEPARATOR, $parts).'Spec.php';
    }

    
    public function getSpecNamespace(): string
    {
        $nsParts = $this->parts;
        array_pop($nsParts);

        return rtrim($this->locator->getSpecNamespace().implode('\\', $nsParts), '\\');
    }

    
    public function getSpecClassname(): string
    {
        return $this->locator->getSpecNamespace().implode('\\', $this->parts).'Spec';
    }
}
