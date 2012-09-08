<?php

class SanatizeTestModel extends CFormModel
{
    public $foo;
    public $bar;
    public $foobar;
    public $barfoo;
    
    public function sanitizationRules()
    {
        return array(
            array('foo', 'trim'),
            array('bar', 'trim', 'mode'=>'ltrim'),
            array('foobar', 'sanatizeFooBar'),
        );
    }
    
    public function sanatizeFooBar($attribute, $params)
    {
        $this->foobar = 'succeeded';
        return true;
    }
}
