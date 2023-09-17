<?php

namespace Spec\PHPSpec\Matcher;

class ExampleCustomMatcherMatchSuccessful extends \Exception {}
class ExampleCustomMatcherMatchFailed extends \Exception {}

\PHPSpec\Matcher\define("customEqual", function($match) {
    return array(
        "match" => function($actual) use ($match) {
            if($actual === $match) {
                throw new ExampleCustomMatcherMatchSuccessful;
            } else {
                throw new ExampleCustomMatcherMatchFailed;
            }
         },
         "failure_message_for_should" => function($actual) use ($match) {
             return "expected {$match}, got {$actual}";
         },
         "failure_message_for_should_not" => function($actual) use ($match) {
             return "expected {$match} not to equal {$match}";
         },
    );
});

class DescribeCustomMatcher extends \PHPSpec\Context
{
    function itCallsTheCustomMatcherForSuccessfulShould() {
        $context = $this;

        $this->spec(function() use ($context) {
            $context->spec(10)->should->customEqual(10);
        })->should->throwException("Spec\PHPSpec\Matcher\ExampleCustomMatcherMatchSuccessful");
    }

    function itCallsTheCustomMatcherForUnsuccessfulShould() {
        $context = $this;

        $this->spec(function() use ($context) {
            $context->spec(10)->should->customEqual(9);
        })->should->throwException("Spec\PHPSpec\Matcher\ExampleCustomMatcherMatchFailed");
    }
}

