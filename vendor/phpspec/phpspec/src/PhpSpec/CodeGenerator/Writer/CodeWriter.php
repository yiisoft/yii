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

namespace PhpSpec\CodeGenerator\Writer;

interface CodeWriter
{
    public function insertMethodFirstInClass(string $class, string $method): string;

    public function insertMethodLastInClass(string $class, string $method): string;

    public function insertAfterMethod(string $class, string $methodName, string $method): string;
}
