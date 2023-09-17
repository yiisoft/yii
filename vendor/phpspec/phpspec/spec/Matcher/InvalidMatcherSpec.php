<?php

namespace Spec\PHPSpec\Matcher;

class DescribeInvalidMatcher extends \PHPSpec\Context
{
    function itShouldComplainWhenUsingAnInvalidMatcher()
    {
        $context = $this;
        $this->spec(function() use ($context) {
            $context->spec(42)->should->beChucksFavorite();    
        })->should->throwException(
            'PHPSpec\Matcher\InvalidMatcher',
            'Call to undefined method beChucksFavorite'
        );
    }
    
    function itShouldComplainWhenUsingAnInvalidMatcherWithObject()
    {
        $context = $this;
        $this->spec(function() use ($context) {
             $context->spec(new \stdClass)->should->beAFunkyObject();   
        })->should->throwException(
            'PHPSpec\Matcher\InvalidMatcher',
            'Call to undefined method stdClass::beAFunkyObject'
        );
    }
    
    function itShouldComplainWhenUsingAnInvalidMatcherWithClosure()
    {
        $context = $this;
        $this->spec(function() use ($context) {
             $context->spec(function(){})->should->beAFunkyClosure();   
        })->should->throwException(
            'PHPSpec\Matcher\InvalidMatcher',
            'Call to undefined method beAFunkyClosure'
        );
    }
    
    function itShouldComplainWhenUsingAnInvalidMatcherWithArray()
    {
        $context = $this;
        $this->spec(function() use ($context) {
             $context->spec(array())->should->beAFunkyArray();   
        })->should->throwException(
            'PHPSpec\Matcher\InvalidMatcher',
            'Call to undefined method beAFunkyArray'
        );
    }
}