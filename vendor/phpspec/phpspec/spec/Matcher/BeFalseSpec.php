<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeFalse;

class DescribeBeFalse extends \PHPSpec\Context {
	private $matcher;
	
	function before()
	{
		$this->matcher = $this->spec(new BeFalse(THIS_REQUIRED_ATTRIBUTE_IS_IGNORED_BY_CONSTRUCTOR));
		$this->matcher->matches(TRUE);
	}

	function itShouldReturnADescriptionWithExpectedValue()
	{
		$this->matcher->getDescription()->should->be('be false');
	}

	function itShouldReturnAMeaningfulFailureMessageIfRequested()
	{
	    $this->matcher->getFailureMessage()->should->be('expected false, got true or non-boolean (using beFalse())');
	}

	function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected true or non-boolean not false (using beFalse())');
	}

	function itShouldReturnFalseOnMismatch()
	{
		$this->matcher->matches(TRUE)->should->beFalse();
		$this->matcher->matches('')->should->beFalse();
		$this->matcher->matches(0)->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
	    $this->matcher->matches(FALSE)->should->beTrue();
	}
}