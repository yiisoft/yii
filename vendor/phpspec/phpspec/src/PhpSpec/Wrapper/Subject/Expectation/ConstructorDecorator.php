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

use PhpSpec\Util\Instantiator;
use PhpSpec\Wrapper\Subject\WrappedObject;

final class ConstructorDecorator extends Decorator implements Expectation
{
    
    public function __construct(Expectation $expectation)
    {
        $this->setExpectation($expectation);
    }

    /**
     * @throws \PhpSpec\Exception\ErrorException
     * @throws \PhpSpec\Exception\Example\ErrorException
     * @throws \PhpSpec\Exception\Fracture\FractureException
     */
    public function match(string $alias, $subject, array $arguments = [], WrappedObject $wrappedObject = null)
    {
        try {
            $wrapped = $subject->getWrappedObject();
        } catch (\PhpSpec\Exception\Example\ErrorException $e) {
            throw $e;
        } catch (\PhpSpec\Exception\Fracture\FractureException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($wrappedObject === null || $wrappedObject->getClassName() === null) {
                throw $e;
            }

            $instantiator = new Instantiator();
            $wrapped = $instantiator->instantiate(
                $wrappedObject->getClassName()
            );
        }

        return $this->getExpectation()->match($alias, $wrapped, $arguments);
    }
}
