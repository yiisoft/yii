<?php

namespace Spec\PHPSpec\Matcher;

use \PHPSpec\Matcher\MatcherFactory;

class DescribeMatcherFactory extends \PHPSpec\Context
{
    
    private $_localMatcherFactory;
    private $_originalIncludePath;
    
    public function before()
    {
        /*
         * WARNING - The setup for these tests causes the builtin Be matcher
         * to be replaced by a fake one for the purpose of the
         * itAllowsACustomMatcherToOverrideABuiltinMatcher test.
        */
        $this->_originalIncludePath = get_include_path();
        $extraIncludePath = __DIR__ . DIRECTORY_SEPARATOR . "_files";
        set_include_path($this->_originalIncludePath . PATH_SEPARATOR . $extraIncludePath);
        $this->_localMatcherFactory = $this->spec(new MatcherFactory(
             array('CustomMatchers')));
    }
    
    
    public function itCreatesBuiltinMatchers()
    {
        $matcherFactory = $this->spec(new MatcherFactory);
        foreach ($this->builtInMatchers() as $matcher) {
            $matcherFactory->create($matcher, true)
                 ->should
                 ->beAnInstanceOf('PHPSpec\Matcher\\' . strtoupper($matcher[0]) . substr($matcher, 1));
        }
    }
    
    public function itThrowsAnExceptionWhenMatcherDoesntExist()
    {
        $factory = new MatcherFactory;
        $this->spec(function() use ($factory){
             $factory->create('BeChuckNorris', 'Chuck Norris');
        })->should->throwException('PHPSpec\Matcher\InvalidMatcher', 'Call to undefined method BeChuckNorris');
       
    }
    
    public function itForwardsArrayOfValuesAsListOfArgumentsForMatcher()
    {
        $matcherFactory = $this->spec(new MatcherFactory);
        $matcher = $matcherFactory->create('throwException', array('\Exception', 'Does not work'));
        $matcher->property('_expectedException')->should->be('\Exception');
        $matcher->property('_expectedMessage')->should->be('Does not work');
    }
    
    
    public function itCreatesATreeOfMatcherFilesConsistentWithTheFilesOnTheIncludeMatcherPath()
    {
        
        $this->_localMatcherFactory->create('beAnInstanceOf', true);

        $matchersArray = $this->_localMatcherFactory->property('_matchers');


        $matchersArray['dummyMatcher']['namespace']->should->be('CustomMatchers\\');

    }

    public function itLoadsMatchersFromSubfolders()
    {
        $this->_localMatcherFactory->create('beAnInstanceOf', true);

        $matchersArray = $this->_localMatcherFactory->property('_matchers');

        $matchersArray['subDummyMatcher']['namespace']->should->be('CustomMatchers\SubMatchers\\');
    }

    public function itIsAbleToUseCustomMatchers()
    {
         $matcher = $this->_localMatcherFactory->create('dummyMatcher', true);
         $matcher->should->beAnInstanceOf('CustomMatchers\DummyMatcher');
    }

    public function itAllowsACustomMatcherToOverrideABuiltinMatcher()
    {
        $matcher = $this->_localMatcherFactory->create('be', true);
        $matcher->should->beAnInstanceOf('CustomMatchers\Be');
    }

    private function builtInMatchers()
    {
        return array(
            'be', 'beAnInstanceOf', 'beEmpty', 'beEqualTo', 'beFalse',
            'beGreaterThan', 'beGreaterThanOrEqualTo', 'beInteger',
            'beLessThan', 'beLessThanOrEqualTo', 'beNull', 'beString', 'beTrue',
            'equal', 'match', 'throwException'
        );
    }

    public function after()
    {
        set_include_path($this->_originalIncludePath);
    }
}