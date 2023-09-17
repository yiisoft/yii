<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeEqualTo;

class DescribeBeEqualTo extends \PHPSpec\Context
{
	private $matcher;
	
	function before()
	{
		$this->matcher = $this->spec(new BeEqualTo(1));
		$this->matcher->matches(0);
	}
	
	function itShouldReturnADescriptionWithExpectedValue()
	{
	    $this->matcher->getDescription()->should->be('be equal to 1');
	}

	function itShouldReturnAMeanifulFailureMessageIfRequested()
	{
	    $this->matcher->getFailureMessage()->should->be('expected 1, got 0 (using beEqualTo())');
	}

	function itShouldReturnAMeanifulNegativeFailureMessageIfRequested()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected 0 not to equal 1 (using beEqualTo())');
	}

	function itShouldReturnFalseOnMismatch()
	{
	    $this->matcher->matches(0)->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
	    $this->matcher->matches(1)->should->beTrue();
	}
}