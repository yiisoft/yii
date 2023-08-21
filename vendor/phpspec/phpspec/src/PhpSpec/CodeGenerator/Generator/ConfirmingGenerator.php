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

use PhpSpec\Console\ConsoleIO;
use PhpSpec\Locator\Resource;

final class ConfirmingGenerator implements Generator
{
    /**
     * @var ConsoleIO
     */
    private $io;

    /**
     * @var string
     */
    private $message;

    /**
     * @var Generator
     */
    private $generator;

    public function __construct(ConsoleIO $io, string $message, Generator $generator)
    {
        $this->io = $io;
        $this->message = $message;
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
        if ($this->io->askConfirmation($this->composeMessage($resource))) {
            $this->generator->generate($resource, $data);
        }
    }

    private function composeMessage(Resource $resource): string
    {
        return str_replace('{CLASSNAME}', $resource->getSrcClassname(), $this->message);
    }

    public function getPriority(): int
    {
        return $this->generator->getPriority();
    }
}
