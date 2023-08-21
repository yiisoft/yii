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

use PhpSpec\Locator\Resource;
use InvalidArgumentException;
use PhpSpec\CodeGenerator\Generator\Generator;

/**
 * Uses registered generators to generate code honoring priority order
 */
class GeneratorManager
{
    /**
     * @var Generator[]
     */
    private $generators = array();

    
    public function registerGenerator(Generator $generator): void
    {
        $this->generators[] = $generator;
        @usort($this->generators, function (Generator $generator1, Generator $generator2) {
            return $generator2->getPriority() - $generator1->getPriority();
        });
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function generate(Resource $resource, string $name, array $data = array()): void
    {
        foreach ($this->generators as $generator) {
            if ($generator->supports($resource, $name, $data)) {
                $generator->generate($resource, $data);

                return;
            }
        }

        throw new InvalidArgumentException(sprintf(
            '"%s" code generator is not registered.',
            $name
        ));
    }
}
