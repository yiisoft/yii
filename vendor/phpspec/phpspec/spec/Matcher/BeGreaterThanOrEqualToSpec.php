<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeGreaterThanOrEqualTo;

class DescribeBeGreaterThanOrEqualTo extends \PHPSpec\Context
{
	private $matcher;
	
	function before()
	{
		$this->matcher = $this->spec(new BeGreaterThanOrEqualTo(1));
		$this->matcher->matches(0);
	}

	function itShouldReturnADescriptionWithExpectedValue()
	{
	    $this->matcher->getDescription()->should->be('be greater than or equal to 1');
	}

	function itShouldReturnAMeaningfulFailureMessageIfRequested()
	{
	    $this->matcher->getFailureMessage()->should->be('expected greater than or equal to 1, got 0 (using beGreaterThanOrEqualTo())');
	}

	function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected 0 not to be greater than or equal to 1 (using beGreaterThanOrEqualTo())');
	}

	function itShouldReturnFalseOnMismatch()
	{
	    $this->matcher->matches(0)->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
	    $this->matcher->matches(1)->should->beTrue();
	    $this->matcher->matches(2)->should->beTrue();
	}
}