<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeString;

class DescribeBeString extends \PHPSpec\Context {
	private $matcher;
	
	function before()
	{
		$this->matcher = $this->spec(new BeString(THIS_REQUIRED_ATTRIBUTE_IS_IGNORED_BY_CONSTRUCTOR));
		$this->matcher->matches(1);
	}

	function itShouldReturnADescriptionWithExpectedValue()
	{
	    $this->matcher->getDescription()->should->be('be string');
	}

	function itShouldReturnAMeanifulFailureMessageIfRequested()
	{
	    $this->matcher->getFailureMessage()->should->be('expected to be string, got 1 type of integer (using beString())');
	}

	function itShouldReturnAMeanifulNegativeFailureMessageIfRequested()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected 1 not to be string (using beString())');
	}

	function itShouldReturnFalseOnMismatch()
	{
	    $this->matcher->matches(1)->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
	    $this->matcher->matches('string')->should->beTrue();
	}
}