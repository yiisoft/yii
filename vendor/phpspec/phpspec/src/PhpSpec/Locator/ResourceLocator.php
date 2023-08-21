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

interface ResourceLocator
{
    /**
     * @return Resource[]
     */
    public function getAllResources(): array;

    
    public function supportsQuery(string $query): bool;

    /**
     * @return Resource[]
     */
    public function findResources(string $query);

    
    public function supportsClass(string $classname): bool;

    /**
     * @return null|Resource
     */
    public function createResource(string $classname);

    
    public function getPriority(): int;
}
