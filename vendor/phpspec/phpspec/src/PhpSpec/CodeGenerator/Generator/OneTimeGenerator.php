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

final class OneTimeGenerator implements Generator
{
    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var array
     */
    private $alreadyGenerated = array();

    
    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function supports(Resource $resource, string $generation, array $data): bool
    {
        return $this->generator->supports($resource, $generation, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Resource $resource, array $data): void
    {
        $classname = $resource->getSrcClassname();
        if (\in_array($classname, $this->alreadyGenerated)) {
            return;
        }

        $this->generator->generate($resource, $data);
        $this->alreadyGenerated[] = $classname;
    }

    public function getPriority(): int
    {
        return $this->generator->getPriority();
    }
}
