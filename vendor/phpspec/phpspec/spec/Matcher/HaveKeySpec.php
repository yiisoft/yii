<?php

namespace Spec\PHPspec\Matcher;

use \PHPSpec\Matcher\HaveKey;

class DescribeHaveKey extends \PHPSpec\Context {
    private $matcher;
    
    function before()
    {
        $this->matcher = $this->spec(new HaveKey('foo'));
        $this->matcher->matches(array('foo' => 42));
    }
    
    function itShouldReturnADescriptionWithExpectedValue()
    {
        $this->matcher->getDescription()->should->be('have key \'foo\'');
    }
    
    function itShouldReturnAMeaningfulFailureMessageIfRequested()
    {
        $this->matcher->matches(array('bar' => 42));
        $this->matcher->getFailureMessage()->should->be(
            'expected to have key \'foo\', got key does not exist (using haveKey())'
        );
    }
    
    function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
    {
        $this->matcher->matches(array('bar' => 42));
        $this->matcher->getNegativeFailureMessage()->should->be(
            'expected key \'foo\' not to exist (using haveKey())'
        );
    }
    
    function itReturnsTrueIfKeyExists()
    {
        $this->matcher->matches(array('foo' => 42))->should->beTrue();
    }
    
    function itReturnsFalseIfKeyDoesNotExist()
    {
        $this->matcher->matches(array('zoo' => 42))->should->beFalse();
    }
}