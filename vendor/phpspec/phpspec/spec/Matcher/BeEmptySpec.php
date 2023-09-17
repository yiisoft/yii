<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeEmpty;

class DescribeBeEmpty extends \PHPSpec\Context
{
	private $matcher;
	const SOMETHING = 'something';
	
	function before()
	{
		$this->matcher = $this->spec(new BeEmpty(
		    THIS_REQUIRED_ATTRIBUTE_IS_IGNORED_BY_CONSTRUCTOR
		));
		$this->matcher->matches(self::SOMETHING);
	}
	
	function itShouldReturnADescriptionWithExpectedValue()
	{
	    $this->matcher->getDescription()->should->be('be empty');
	}
	
	function itShouldReturnAMeaningfulFailureMessageIfRequested()
	{
	    $this->matcher->getFailureMessage()->should->be('expected to be empty, got not empty (using beEmpty())');
	}
	
	function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected not to be empty (using beEmpty())');
	}
	
	function itShouldReturnFalseOnMismatch()
	{
	    $this->matcher->matches(self::SOMETHING)->should->beFalse();
	}
	
	function itShouldReturnTrueOnMatch()
	{
	    $this->matcher->matches(null)->should->beTrue();
	}
}