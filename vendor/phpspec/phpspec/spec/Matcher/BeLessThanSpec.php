<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeLessThan;

class DescribeBeLessThan extends \PHPSpec\Context
{
	private $matcher;
	
	function before()
	{
		$this->matcher = $this->spec(new BeLessThan(1));
		$this->matcher->matches(0);
	}

	function itShouldReturnADescriptionWithExpectedValue()
	{
	    $this->matcher->getDescription()->should->be('be less than 1');
	}

	function itShouldReturnAMeaningfulFailureMessageIfRequested()
	{
	    $this->matcher->getFailureMessage()->should->be('expected less than 1, got 0 (using beLessThan())');
	}

	function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected 0 not to be less than 1 (using beLessThan())');
	}

	function itShouldReturnFalseOnMismatch()
	{
		$this->matcher->matches(2)->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
		$this->matcher->matches(0)->should->beTrue();
	}
}