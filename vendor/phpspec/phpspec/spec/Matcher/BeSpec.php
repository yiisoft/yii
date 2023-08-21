<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\Be;

class DescribeBe extends \PHPSpec\Context
{
	private $matcher;
	
	function before()
	{
		$this->matcher = $this->spec(new Be(1));
		$this->matcher->matches(0);
	}

	function itShouldReturnADescriptionWithExpectedValue()
	{
	    $this->matcher->getDescription()->should->be('be 1');
	}

	function itShouldReturnAMeanifulFailureMessageIfRequested()
	{
	    $this->matcher->getFailureMessage()->should->be('expected 1, got 0 (using be())');
	}

	function itShouldReturnAMeanifulNegativeFailureMessageIfRequested()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected 0 not to be 1 (using be())');
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