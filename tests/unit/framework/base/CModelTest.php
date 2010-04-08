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

	public function testModifyValidators()
	{
		$model=new NewModel;
		$model->attr1=2;
		$model->attr2=2;
		$this->assertTrue($model->validate());
		$model->validators->insertAt(0,CValidator::createValidator('numerical',$model,'attr1,attr2',array('min'=>3)));
		$this->assertFalse($model->validate());
		$model->attr1=6;
		$model->attr2=6;
		$this->assertFalse($model->validate());
		$model->attr1=4;
		$model->attr2=4;
		$this->assertTrue($model->validate());
		$model=new NewModel;
		$model->attr1=3;
		$model->validators->add(CValidator::createValidator('required',$model,'attr2',array()));
		$this->assertFalse($model->validate());
		$model->attr2=3;
		$this->assertTrue($model->validate());
	}
}