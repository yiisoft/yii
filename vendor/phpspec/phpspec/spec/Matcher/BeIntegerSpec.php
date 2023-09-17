<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeInteger;

class DescribeBeInteger extends \PHPSpec\Context {
	private $matcher;
	
	public function before()
	{
		$this->matcher = $this->spec(new BeInteger(
		    THIS_REQUIRED_ATTRIBUTE_IS_IGNORED_BY_CONSTRUCTOR));
		$this->matcher->matches("hello world");
	}

	public function itShouldReturnADescriptionWithExpectedValue()
	{
	    $this->matcher->getDescription()->should->be('be integer');
	}

	public function itShouldReturnAMeaningfulFailureMessageIfRequested()
	{
	    $this->matcher->getFailureMessage()->should->be('expected to be integer, got \'hello world\' type of string (using beInteger())');
	}

	public function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected \'hello world\' not to be integer (using beInteger())');
	}

	public function itShouldReturnFalseOnMismatch()
	{
	    $this->matcher->matches("hello world")->should->beFalse();
	}

	public function itShouldReturnTrueOnMatch()
	{
	    $this->matcher->matches(123)->should->beTrue();
	}
}