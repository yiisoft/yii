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

namespace PhpSpec\Locator;

interface Resource
{
    
    public function getName(): string;

    
    public function getSpecName(): string;

    
    public function getSrcFilename(): string;

    
    public function getSrcNamespace(): string;

    
    public function getSrcClassname(): string;

    
    public function getSpecFilename(): string;

    
    public function getSpecNamespace(): string;

    
    public function getSpecClassname(): string;
}
