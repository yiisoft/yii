<?php

namespace Spec\PHPSpec\Specification;

use \PHPSpec\Specification\ExampleRunner;

require_once __DIR__ . '/_files/BehaveLikeAnotherSpec.php';
require_once __DIR__ . '/_files/Fake.php';

class DescribeExampleRunner extends \PHPSpec\Context
{
    const NUM_OF_METHODS_EXAMPLE_HAS = 2;
    
    function before()
    {
        $this->reporter = $this->getReporter();
        $this->example = $this->getExample();
        $this->exampleFactory = $this->getExampleFactory();
    }
    
    function itWillCallCreateForEachExampleOfTheGroup()
    {
        $this->exampleRunner = $this->spec(new ExampleRunner(new \Fake));
        $this->exampleFactory->shouldReceive('create')
                             ->times(self::NUM_OF_METHODS_EXAMPLE_HAS)
                             ->andReturn($this->example);
        $this->exampleRunner->setExampleFactory($this->exampleFactory);
        include_once __DIR__ . DIRECTORY_SEPARATOR . '_files'
                             . DIRECTORY_SEPARATOR . 'Fake.php';
        $this->exampleRunner->run($this->reporter);
    }
    
    function itWillIncludeBehavioursShared()
    {
        $this->exampleRunner = $this->spec(new ExampleRunner(new \DescribeBehaveLikeAnother));
        $this->exampleFactory->shouldReceive('create')
                             ->times(2);
        $this->exampleRunner->run($this->reporter);
    }
    
    function getReporter()
    {
        $reporter = $this->mock('PHPSpec\Runner\Reporter');
        $reporter->shouldReceive('exampleGroupStarted');
        $reporter->shouldReceive('exampleGroupFinished');
        $reporter->shouldIgnoreMissing();
        return $reporter;
    }
    
    function getExample()
    {
        $example = $this->mock('PHPSpec\Specification\Example');
        $example->shouldReceive('run');
        return $example;
    }
    
    function getExampleFactory()
    {
        return $this->mock('PHPSpec\Specification\ExampleFactory');
    }
}