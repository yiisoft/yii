<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\Match;

class DescribeMatch extends \PHPSpec\Context
{
	private $matcher;
	
	function before()
	{
		$this->matcher = $this->spec(new Match("/bar/"));
		$this->matcher->matches('bar');
	}

	function itShouldReturnADescriptionWithExpectedValue()
	{
	    $this->matcher->getDescription()->should->be('match \'/bar/\' PCRE regular expression');
	}

	function itShouldReturnAMeaningfulFailureMessageIfRequested()
	{
		$this->matcher->matches('foo');
		$this->matcher->getFailureMessage()->should->be('expected match for \'/bar/\' PCRE regular expression, got \'foo\' (using match())');
	}

    function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected no match for \'/bar/\' PCRE regular expression, got \'bar\' (using match())');
    }
	
	function itShouldReturnFalseOnMismatch()
	{
	    $this->matcher->matches('foo')->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
	    $this->matcher->matches('bar')->should->beTrue();
	}
}