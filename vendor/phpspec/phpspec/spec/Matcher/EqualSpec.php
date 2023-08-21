<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\Equal;

class DescribeEqual extends \PHPSpec\Context {
	private $matcher;

    function before()
    {
        $this->matcher = $this->spec(new Equal(1));
        $this->matcher->matches(0);
    }

	function itShouldReturnADescriptionWithExpectedValue()
	{
	    $this->matcher->getDescription()->should->be('equal 1');
	}

	function itShouldReturnAMeanifulFailureMessageIfRequested()
	{
	    $this->matcher->getFailureMessage()->should->be('expected 1, got 0 (using equal())');
	}

	function itShouldReturnAMeanifulNegativeFailureMessageIfRequested()
	{
	    $this->matcher->getNegativeFailureMessage()->should->be('expected 0 not to equal 1 (using equal())');
	}

	function itShouldReturnFalseOnMismatch()
	{
	    $this->matcher->matches(0)->should->beFalse();
	}

	function itShouldReturnTrueOnMatch()
	{
		$this->matcher->matches(1)->should->beTrue();
	}

	function itShouldReturnFalseOnMismatchedArrayType()
	{
	    $this->matcher->matches(array())->should->beFalse();
	    $this->matcher->matches(array(1))->should->beFalse();
	}

	function itShouldReturnFalseOnMismatchedClassType()
	{
		include_once __DIR__ . '/_files/Foo.php';
		$this->matcher = $this->spec(new Equal(new \stdClass));
	    $this->matcher->matches(new \Foo)->should->beFalse();
	}

	function itShouldReturnFalseOnMismatchedObjectType()
	{
		$this->matcher = $this->spec(new Equal(new \stdClass));
	    $this->matcher->matches(array())->should->beFalse();
	    $this->matcher->matches(1)->should->beFalse();
	}

	function itShouldReturnFalseOnNonMatchingObjects()
	{
		$obj1 = new \stdClass;
		$obj2 = new \stdClass;
		$obj1->hasFoo = true;
		$obj2->hasFoo = false;
	    $this->matcher = $this->spec(new Equal($obj1));
	    $this->matcher->matches($obj2)->should->beFalse();
	}

	function itShouldReturnFalseOnNonMatchingArrays()
	{
	    $this->matcher = $this->spec(new Equal(array(1,2,3)));
	    $this->matcher->matches(array(1,2,4))->should->beFalse();
	}

	function itShouldReturnFalseOnNonMatchingStrings()
	{
	    $this->matcher = $this->spec(new Equal("a string"));
	    $this->matcher->matches("another")->should->beFalse();
	}

	function itShouldReturnFalseOnNonMatchingFloats()
	{
		$this->matcher = $this->spec(new Equal(0.123));
	    $this->matcher->matches(0.125, 0.0001)->should->beFalse();
	}

	function itShouldReturnTrueOnMatchingArrays()
	{
	    $this->matcher = $this->spec(new Equal(array(1,2,3)));
	    $this->matcher->matches(array(1,2,3))->should->beTrue();
	}

	function itShouldReturnTrueOnMatchingObjects()
	{
		$obj = new \stdClass;
	    $this->matcher = $this->spec(new Equal($obj));
	    $this->matcher->matches($obj)->should->beTrue();
	}

	function itShouldReturnTrueOnMatchingFloats()
	{
		$this->matcher = $this->spec(new Equal(0.123));
	    $this->matcher->matches(0.123, 0.0001)->should->beTrue();
	}

	function itShouldReturnTrueOnMatchingString()
	{
		$this->matcher = $this->spec(new Equal('a string'));
	    $this->matcher->matches('a string')->should->beTrue();
	}

	function itShouldReturnTrueOnMatchingResource()
	{
		$fh = fopen('php://input', 'r');
		$this->matcher = $this->spec(new Equal($fh));
	    $this->matcher->matches($fh)->should->beTrue();
	    fclose($fh);
	}
}