<?php

namespace Spec\PHPSpec\Matcher;

use \PHPSpec\Matcher\Predicate;

require_once __DIR__ . '/_files/Foo.php';

class DescribePredicate extends \PHPSpec\Context
{
    private $predicate;
    
    function before()
	{
		$this->predicate = $this->spec(new Predicate(true));
		$this->predicate->setMethodName('hasArg1');
		$this->predicate->setObject(new \Foo);
		$this->predicate->setPredicateCall('haveArg1');
	}

	function itShouldReturnADescriptionOfTheExpectation()
	{
	    $this->predicate->getDescription()->should->be('have arg1');
	}

	function itShouldReturnAMeaningfulFailureMessageIfRequested()
	{
	    $this->predicate->getFailureMessage()->should->be('expected TRUE, got FALSE or non-boolean (using haveArg1())');
	}

	function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
	{
	    $this->predicate->getNegativeFailureMessage()->should->be('expected FALSE or non-boolean not TRUE (using haveArg1())');
	}

	function itShouldReturnFalseOnMismatch()
	{
		$this->predicate->setObject(new \Foo);
		$this->predicate->matches('unused_param')->should->beFalse();
	}

	function itShouldReturnFalseIfPredicateDoesntReturnBoolean()
	{
		$this->predicate = $this->spec(new Predicate(true));
		$this->predicate->setMethodName('getArg1');
		$this->predicate->setObject(new \Foo('not boolean'));
		$this->predicate->setPredicateCall('canGetArg1');
	    $this->predicate->matches('unused_param')->should->beFalse();
	}

	function itShouldReturnTrueOnMatch() {
		$foo = new \Foo('something');
		$this->predicate->setObject($foo);
		$this->predicate->matches('unused_param')->should->beTrue();
	}

	function itShouldThrowAnExceptionWhenTryingToSetObjectWithSomethingElse()
	{
	    $predicate = $this->predicate;
        $this->spec(function() use ($predicate) {
		    $predicate->setObject('I am not an object');
        })->should->throwException("\\PHPSpec\\Exception");
	}
    
    function itReadsAMethodStartingWithIsUsingShouldAndBe()
    {
        $dummy = $this->spec(new Dummy);
        $dummy->should->beValid();
    }
    
    function itReadsAMethodStartingWithIsUsingShouldNotAndBe()
    {
        $dummy = $this->spec(new Dummy);
        $dummy->shouldNot->beInvalid();
    }
}

class Dummy
{
    function isValid()
    {
        return true;
    }
    
    function isInvalid()
    {
        return false;
    }
}