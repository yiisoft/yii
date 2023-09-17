<?php

namespace Spec\PHPSpec\Specification\Interceptor;

require_once __DIR__ . DIRECTORY_SEPARATOR . '_files/Calculator.php';

use PHPSpec\Specification\Interceptor\Object as ObjectInterceptor,
    PHPSpec\Matcher\MatcherFactory,
    spec\PHPSpec\Specification\Interceptor\Calculator;

class DescribeObject extends \PHPSpec\Context
{
    private $nonPublic = 42;
    
    public function itCanAccessNonPublicProperties()
    {
        $object = new self;
        $interceptor = new ObjectInterceptor($object);
        $interceptor->setMatcherFactory(new MatcherFactory);
        $interceptor->property('nonPublic')
                    ->should->be(42);
    }
    
    public function itWIllInterceptTheResultOfInterceptedMagicCall()
    {
        $calculator = $this->spec(new Calculator);
        $calculator->add(40, 2)->should->equal(42);
    }
}