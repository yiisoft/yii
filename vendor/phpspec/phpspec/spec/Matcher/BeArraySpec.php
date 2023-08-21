<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeArray;

class DescribeBeArray extends \PHPSpec\Context {
	private $matcher;
	
	function before()
	{
		$this->matcher = $this->spec(new BeArray(THIS_REQUIRED_ATTRIBUTE_IS_IGNORED_BY_CONSTRUCTOR));
	}

	function itShouldReturnADescriptionWithExpectedValue()
	{
		$this->matcher->getDescription()->should->be('be array');
	}

	function itShouldReturnAMeaningfulFailureMessageIfRequested()
	{
	    $this->matcher->getFailureMessage()->should->be('expected to be array, got a non array (using beArray())');
	}

	function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected not to be an array got array(using beArray())');
	}

	function itShouldReturnFalseOnMismatch()
	{
		$this->matcher->matches(TRUE)->should->beFalse();
		$this->matcher->matches('')->should->beFalse();
		$this->matcher->matches(0)->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
	    $this->matcher->matches(array())->should->beTrue();
	}
}