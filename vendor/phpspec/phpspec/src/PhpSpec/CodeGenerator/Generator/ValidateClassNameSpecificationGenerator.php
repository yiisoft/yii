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
use PhpSpec\Util\NameChecker;
use PhpSpec\Locator\Resource;

final class ValidateClassNameSpecificationGenerator implements Generator
{

    private $classNameChecker;
    private $io;
    private $originalGenerator;

    public function __construct(NameChecker $classNameChecker, ConsoleIO $io, Generator $originalGenerator)
    {
        $this->classNameChecker = $classNameChecker;
        $this->io = $io;
        $this->originalGenerator = $originalGenerator;
    }

    public function supports(Resource $resource, string $generation, array $data): bool
    {
        return $this->originalGenerator->supports($resource, $generation, $data);
    }

    public function generate(Resource $resource, array $data): void
    {
        $className = $resource->getSrcClassname();

        if (!$this->classNameChecker->isNameValid($className)) {
            $this->writeInvalidClassNameError($className);
            return;
        }

        $this->originalGenerator->generate($resource, $data);
    }

    private function writeInvalidClassNameError(string $className): void
    {
        $error = "I cannot generate spec for '$className' because class name contains reserved keyword";
        $this->io->writeBrokenCodeBlock($error, 2);
    }

    public function getPriority(): int
    {
        return $this->originalGenerator->getPriority();
    }

}
