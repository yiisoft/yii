<?php

namespace Spec\PHPSpec\Specification;

use PHPSpec\Specification\Example;

require_once __DIR__ . DIRECTORY_SEPARATOR . '_files'
                     . DIRECTORY_SEPARATOR . 'BreaksBeforeTheAfter.php';

class DescribeExample extends \PHPSpec\Context
{
    public function itCallsAfterEvenIfAnExceptionIsThrown()
    {
        $contextObject = new \BreaksBeforeTheAfter;
                     
        $example = new Example($contextObject, 'itThrowsException');
        $reporter = $this->mock('PHPSpec\Runner\Reporter');
        
        $reporter->shouldReceive('addException');
        
        $example->run($reporter);
        
        $contextObject->afterValue->should->be('changed');
    }
    
    public function itCallsAfterEvenIfExampleFails()
    {
        $contextObject = new \BreaksBeforeTheAfter;
                     
        $example = new Example($contextObject, 'itFails');
        $reporter = $this->mock('PHPSpec\Runner\Reporter');
        
        $reporter->shouldReceive('addFailure');
        
        $example->run($reporter);
        
        $contextObject->afterValue->should->be('changed');
    }
    
    public function itCallsAfterEvenIfExampleIsPending()
    {
        $contextObject = new \BreaksBeforeTheAfter;
                     
        $example = new Example($contextObject, 'itIsPending');
        $reporter = $this->mock('PHPSpec\Runner\Reporter');
        
        $reporter->shouldReceive('addPending');
        
        $example->run($reporter);
        
        $contextObject->afterValue->should->be('changed');
    }
    
    public function itCallsAfterEvenIfExampleRaisesError()
    {
        $contextObject = new \BreaksBeforeTheAfter;
                     
        $example = new Example($contextObject, 'itHasError');
        $reporter = $this->mock('PHPSpec\Runner\Reporter');
        
        $reporter->shouldReceive('addError');
        
        $example->run($reporter);
        
        $contextObject->afterValue->should->be('changed');
    }
    
}