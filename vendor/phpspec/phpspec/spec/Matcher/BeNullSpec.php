<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeNull;

class DescribeBeNull extends \PHPSpec\Context
{
	private $matcher;
	
	function before()
	{
		$this->matcher = $this->spec(new BeNull(THIS_REQUIRED_ATTRIBUTE_IS_IGNORED_BY_CONSTRUCTOR));
		$this->matcher->matches(1);
	}

	function itShouldReturnADescriptionWithExpectedValue()
	{
	    $this->matcher->getDescription()->should->be('be NULL');
	}

	function itShouldReturnAMeanifulFailureMessageIfRequested()
	{
	    $this->matcher->getFailureMessage()->should->be('expected to be NULL, got 1 (using beNull())');
	}

	function itShouldReturnAMeanifulNegativeFailureMessageIfRequested()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected not to be NULL (using beNull())');
	}

	function itShouldReturnFalseOnMismatch()
	{
	    $this->matcher->matches(1)->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
	    $this->matcher->matches(NULL)->should->beTrue();
	}
}