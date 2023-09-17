<?php

namespace tests\PHPSpec\Specification\Interceptor;

class DescribeInterceptor extends \PHPSpec\Context {
    
    private $_interceptor;
    
    public function before() {
        $this->_interceptor = \PHPSpec\Specification\Interceptor
                              \InterceptorFactory::create(
                                  new InterceptorLoop()
                              );
    }
    
    public function itKeepsTrackOfAssertions() {
        $this->_interceptor->should->beAnInstanceOf(
            'tests\PHPSpec\Specification\Interceptor\InterceptorLoop'
        );
        
        $assertions = $this->_interceptor->getNumberOfAssertions();
        
        $this->spec($assertions)->should->be(1);
    }
    
    public function itKeepsTrackOfAssertionsRecursively() {
        $this->_interceptor->should->beAnInstanceOf(
            'tests\PHPSpec\Specification\Interceptor\InterceptorLoop'
        );
        
        $this->_interceptor->doSomething()->should->beAnInstanceOf(
            'tests\PHPSpec\Specification\Interceptor\InterceptorLoop'
        );
        
        $assertions = $this->_interceptor->getNumberOfAssertions();
        
        $this->spec($assertions)->should->be(2);
    }
    
}

class InterceptorLoop {
    
    public function doSomething() {
        return new InterceptorLoop();
    }
    
}