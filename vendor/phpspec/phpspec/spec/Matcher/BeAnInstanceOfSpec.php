<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\BeAnInstanceOf;

class DescribeBeAnInstanceOf extends \PHPSpec\Context
{
	private $matcher;
	
	function before()
	{
		include_once __DIR__ . '/_files/Foo.php';
		include_once __DIR__ . '/_files/Bar.php';
		$this->matcher = $this->spec(new BeAnInstanceOf('Foo'));
		$this->matcher->matches(new \Bar);
	}
	
	function itShouldReturnADescriptionWithExpectedValue()
	{
		$this->matcher->getDescription()->should->be("be an instance of 'Foo'");
	}
	
	function itShouldReturnAMeanifulFailureMessageIfRequested()
	{
		$this->matcher->getFailureMessage()->should
		     ->be('expected \'Foo\', got \'Bar\' (using beAnInstanceOf())');
	}

	function itShouldReturnAMeanifulNegativeFailureMessageIfRequested()
	{
	    $this->matcher->getNegativeFailureMessage()->should
		     ->be('expected \'Bar\' not to be \'Foo\' (using beAnInstanceOf())');
	}

	function itShouldReturnFalseOnMismatch()
	{
	    $this->matcher->matches(new \Bar)->should->beFalse();
		$this->matcher->matches(NULL)->should->beFalse();
		$this->matcher->matches('a string')->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
		$this->matcher->matches(new \Foo)->should->beTrue();
	}	
}