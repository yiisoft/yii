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

namespace PhpSpec\CodeGenerator\Generator;

use PhpSpec\Locator\Resource;

/**
 * Interface that all Generators need to implement in PhpSpec
 */
interface Generator
{
    public function supports(Resource $resource, string $generation, array $data): bool;

    public function generate(Resource $resource, array $data): void;

    public function getPriority(): int;
}
