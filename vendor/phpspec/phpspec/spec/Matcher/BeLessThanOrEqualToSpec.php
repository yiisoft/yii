<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeLessThanOrEqualTo;

class DescribeBeLessThanOrEqualTo extends \PHPSpec\Context {
    private $matcher;
	
	function before()
	{
		$this->matcher = $this->spec(new BeLessThanOrEqualTo(1));
		$this->matcher->matches(2);
	}

	function itShouldReturnADescriptionWithExpectedValue()
	{
		$this->matcher->getDescription()->should->be('be less than or equal to 1');
	}

	function itShouldReturnAMeaningfulFailureMessageIfRequested()
	{
		$this->matcher->getFailureMessage()->should->be('expected less than or equal to 1, got 2 (using beLessThanOrEqualTo())');
	}

	function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected 2 not to be less than or equal to 1 (using beLessThanOrEqualTo())');
	}

	function itShouldReturnFalseOnMismatch()
	{
		$this->matcher->matches(2)->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
		$this->matcher->matches(0)->should->beTrue();
		$this->matcher->matches(1)->should->beTrue();
	}
}