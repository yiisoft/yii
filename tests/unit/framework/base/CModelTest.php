<?php
require_once dirname(__FILE__).'/NewModel.php';

class CModelTest extends CTestCase
{
	public function testValidate()
	{
		$model=new NewModel;
		$this->assertFalse($model->validate());
		$model->attr1=4;
		$this->assertTrue($model->validate());
		$model->attr1=6;
		$this->assertFalse($model->validate());
		$model->attr2=6;
		$this->assertFalse($model->validate());
		$model->attr1=4;
		$this->assertFalse($model->validate());
		$model->attr2=4;
		$this->assertTrue($model->validate());
	}

	public function testPrependValidator()
	{
		$model=new NewModel;
		$model->attr1=2;
		$model->attr2=2;
		$this->assertTrue($model->validate());
		$model->prependValidator('attr1,attr2','numerical',array('min'=>3));
		$this->assertFalse($model->validate());
		$model->attr1=6;
		$model->attr2=6;
		$this->assertFalse($model->validate());
		$model->attr1=4;
		$model->attr2=4;
		$this->assertTrue($model->validate());
	}

	public function testAppendValidator()
	{
		$model=new NewModel;
		$model->attr1=2;
		$this->assertTrue($model->validate());
		$model->appendValidator('attr2','required');
		$this->assertFalse($model->validate());
		$model->attr2=6;
		$this->assertFalse($model->validate());
		$model->attr2=4;
		$this->assertTrue($model->validate());
	}
}