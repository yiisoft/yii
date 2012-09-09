<?php

class CTrimSanatizerTest extends CTestCase
{
	public function getTestModel()
	{
		$model = new SanatizeTrimTestModel;
		$model->foo = "\nfoo\n";
		$model->bar = "\r\n this is bar\n";
		$model->foobar = 'foobar';
		$model->barfoo = 'barfoo';
		$model->barfood = "\r\nbarfood\n";
		return $model;
	}
	
	public function testSanatizeAttribute()
	{
		$model = $this->getTestModel();
		$model->sanatize();
		$this->assertEquals('foo',$model->foo);
		$this->assertEquals("this is bar\n",$model->bar);
		$this->assertEquals('succeeded', $model->foobar);
		$this->assertEquals('succeeded2', $model->barfoo);
		$this->assertEquals("\r\nbarfood", $model->barfood);
	}
}

class SanatizeTrimTestModel extends CFormModel
{
    public $foo;
    public $bar;
	public $barfood;
    public $foobar;
    public $barfoo;
    
    public function sanitizationRules()
    {
        return array(
            array('foo', 'trim'),
            array('bar', 'trim', 'mode'=>'ltrim'),
			array('barfood', 'trim', 'mode'=>'rtrim'),
			array('foobar', 'sanatizeFooBar'),
			array('barfoo', 'sanatizeBarFoo'),
        );
    }
    
    public function sanatizeFooBar($attribute, $params)
    {
        $this->foobar = 'succeeded';
        return true;
    }
	
	public function sanatizeBarFoo($attribute, $params)
	{
		$this->$attribute='succeeded2';
		return true;
	}
}
