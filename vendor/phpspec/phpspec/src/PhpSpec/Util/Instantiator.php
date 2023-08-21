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

use PhpSpec\Exception\Fracture\ClassNotFoundException;

class Instantiator
{
    /**
     * @return object
     */
    public function instantiate(string $className)
    {
        if (!class_exists($className)) {
            throw new ClassNotFoundException("Class $className does not exist.", $className);
        }

        $instantiator = new \Doctrine\Instantiator\Instantiator();

        return $instantiator->instantiate($className);
    }
}
