<?php

use PHPSpec\Specification\Result\Error;

class BreaksBeforeTheAfter extends \PHPSpec\Context
{
    public $afterValue;
    
    public function before()
    {
        $this->afterValue = $this->spec('not changed');
    }
    
    public function itThrowsException()
    {
        throw new \Exception;
    }
    
    public function itFails()
    {
        $this->fail();
    }
    
    public function itIsPending()
    {
        $this->pending();
    }
    
    public function itHasError()
    {
        throw new Error;
    }
    
    
    public function after()
    {
        $this->afterValue = $this->spec('changed');
    }
}