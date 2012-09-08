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
		
	}
}

class SanatizeTrimTestModel extends CFormModel
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
            array('barfoo', 'trim', array($this,'sanatizeBarFoo')),
			array('foobar', 'sanatizeFooBar';
        );
    }
    
    public function sanatizeFooBar($attribute, $params)
    {
        $this->foobar = 'succeeded';
        return true;
    }
	
	public static function sanatizeBarFoo($a, $b, $c, $d)
	{
		$d->$b='succeeded2';
		return true;
	}
}
