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

use ReflectionMethod;
use ReflectionProperty;

final class VisibilityAccessInspector implements AccessInspector
{
    /**
     * @param object $object
     */
    public function isPropertyReadable($object, string $property): bool
    {
        return $this->isExistingPublicProperty($object, $property);
    }

    /**
     * @param object $object
     */
    public function isPropertyWritable($object, string $property): bool
    {
        return $this->isExistingPublicProperty($object, $property);
    }

    /**
     * @param object $object
     */
    private function isExistingPublicProperty($object, string $property): bool
    {
        if (!property_exists($object, $property)) {
            return false;
        }

        $propertyReflection = new ReflectionProperty($object, $property);

        return $propertyReflection->isPublic();
    }

    /**
     * @param object $object
     */
    public function isMethodCallable($object, string $method): bool
    {
        return $this->isExistingPublicMethod($object, $method);
    }

    /**
     * @param object $object
     */
    private function isExistingPublicMethod($object, string $method): bool
    {
        if (!method_exists($object, $method)) {
            return false;
        }

        $methodReflection = new ReflectionMethod($object, $method);

        return $methodReflection->isPublic();
    }
}
