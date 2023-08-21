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

namespace PhpSpec\Runner\Maintainer;

use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Specification;
use PhpSpec\Runner\MatcherManager;
use PhpSpec\Runner\CollaboratorManager;

class LetAndLetgoMaintainer implements Maintainer
{
    
    public function supports(ExampleNode $example): bool
    {
        return $example->getSpecification()->getClassReflection()->hasMethod('let')
            || $example->getSpecification()->getClassReflection()->hasMethod('letgo')
        ;
    }

    
    public function prepare(
        ExampleNode $example,
        Specification $context,
        MatcherManager $matchers,
        CollaboratorManager $collaborators
    ): void {
        if (!$example->getSpecification()->getClassReflection()->hasMethod('let')) {
            return;
        }

        $reflection = $example->getSpecification()->getClassReflection()->getMethod('let');
        $reflection->invokeArgs($context, $collaborators->getArgumentsFor($reflection));
    }

    
    public function teardown(
        ExampleNode $example,
        Specification $context,
        MatcherManager $matchers,
        CollaboratorManager $collaborators
    ): void {
        if (!$example->getSpecification()->getClassReflection()->hasMethod('letgo')) {
            return;
        }

        $reflection = $example->getSpecification()->getClassReflection()->getMethod('letgo');
        $reflection->invokeArgs($context, $collaborators->getArgumentsFor($reflection));
    }

    
    public function getPriority(): int
    {
        return 10;
    }
}
