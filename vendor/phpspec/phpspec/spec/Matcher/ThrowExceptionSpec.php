<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\ThrowException;

class DescribeThrowException extends \PHPSpec\Context {
    private $matcher;
	
    function before()
    {
		$this->matcher = $this->spec(new ThrowException('InvalidArgumentException'));
    }
    
	function itShouldReturnADescriptionWithExpectedValue()
    {
		$this->matcher->getDescription()->should->be('throw exception InvalidArgumentException');
	}
	
	function itShouldReturnAMeaningfulFailureMessageIfRequested()
    {
	    $this->matcher->matches('BadMethodCallException');
	    $this->matcher->getFailureMessage()->should->be(
	        'expected to throw exception \'InvalidArgumentException\', got \'BadMethodCallException\' (using throwException())'
	    );
	}
	
	function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
    {
	    $this->matcher->matches('BadMethodCallException');
		$this->matcher->getNegativeFailureMessage()->should->be(
		    'expected \'BadMethodCallException\' not to be thrown but got \'InvalidArgumentException\' (using throwException())'
		);
	}
}