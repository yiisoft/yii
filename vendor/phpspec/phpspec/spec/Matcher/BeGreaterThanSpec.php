<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeGreaterThan;

class DescribeBeGreaterThan extends \PHPSpec\Context {
	private $matcher;
	
	function before()
	{
		$this->matcher = $this->spec(new BeGreaterThan(1));
		$this->matcher->matches(0);
	}

	function itShouldReturnADescriptionWithExpectedValue()
	{
	    $this->matcher->getDescription()->should->be('be greater than 1');
	}

	function itShouldReturnAMeaningfulFailureMessageIfRequested()
	{
		$this->matcher->getFailureMessage()->should->be('expected greater than 1, got 0 (using beGreaterThan())');
	}

	function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
	{
		$this->matcher->getNegativeFailureMessage()->should->be('expected 0 not to be greater than 1 (using beGreaterThan())');
	}

	function itShouldReturnFalseOnMismatch()
	{
		$this->matcher->matches(0)->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
		$this->matcher->matches(2)->should->beTrue();
	}
}