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

namespace PhpSpec\Wrapper\Subject\Expectation;

abstract class Decorator implements Expectation
{
    /**
     * @var Expectation
     */
    private $expectation;

    
    public function __construct(Expectation $expectation)
    {
        $this->expectation = $expectation;
    }

    
    public function getExpectation(): Expectation
    {
        return $this->expectation;
    }

    
    protected function setExpectation(Expectation $expectation): void
    {
        $this->expectation = $expectation;
    }

    
    public function getNestedExpectation(): Expectation
    {
        $expectation = $this->getExpectation();
        while ($expectation instanceof Decorator) {
            $expectation = $expectation->getExpectation();
        }

        return $expectation;
    }
}
