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

namespace PhpSpec\CodeAnalysis;

final class MagicAwareAccessInspector implements AccessInspector
{
    /**
     * @var AccessInspector
     */
    private $accessInspector;

    
    public function __construct(AccessInspector $accessInspector)
    {
        $this->accessInspector = $accessInspector;
    }

    /**
     * @param object $object
     */
    public function isPropertyReadable($object, string $property): bool
    {
        return method_exists($object, '__get') || $this->accessInspector->isPropertyReadable($object, $property);
    }

    /**
     * @param object $object
     */
    public function isPropertyWritable($object, string $property): bool
    {
        return method_exists($object, '__set') || $this->accessInspector->isPropertyWritable($object, $property);
    }

    /**
     * @param object $object
     */
    public function isMethodCallable($object, string $method): bool
    {
        return method_exists($object, '__call') || $this->accessInspector->isMethodCallable($object, $method);
    }
}
