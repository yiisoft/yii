<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeTrue;

class DescribeBeTrue extends \PHPSpec\Context{
	private $matcher;
	
	function before()
	{
		$this->matcher = $this->spec(new BeTrue(
		    THIS_REQUIRED_ATTRIBUTE_IS_IGNORED_BY_CONSTRUCTOR));
		$this->matcher->matches(FALSE);
	}

	function itShouldReturnADescriptionWithExpectedValue()
	{
	    $this->matcher->getDescription()->should->be('be true');
	}

	function itShouldReturnAMeaningfulFailureMessageIfRequested()
	{
	    $this->matcher->getFailureMessage()->should->be('expected true, got false or non-boolean (using beTrue())');
	}

	function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected false or non-boolean not true (using beTrue())');
	}

	function itShouldReturnFalseOnMismatch()
	{
		$this->matcher->matches(FALSE)->should->beFalse();
		$this->matcher->matches('1')->should->beFalse();
		$this->matcher->matches(1)->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
		$this->matcher->matches(TRUE)->should->beTrue();
	}
}