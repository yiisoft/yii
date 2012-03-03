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

	function testBeforeValidate(){
		$model=new NewModel;
		$model->attr1=4;
		$this->assertTrue($model->validate());

		$model->onBeforeValidate = array($this, 'beforeValidate');
		$this->assertFalse($model->validate());
	}

	function beforeValidate($event){
		$event->isValid = false;
	}

	function testIsAttributeRequired(){
		$model=new NewModel;
		$this->assertTrue($model->isAttributeRequired('attr1'));
		$this->assertFalse($model->isAttributeRequired('attr2'));
	}

	function testIsAttributeSafe(){
		$model=new NewModel;
		$this->assertTrue($model->isAttributeSafe('attr1'));
		$this->assertFalse($model->isAttributeSafe('attr3'));
		$this->assertFalse($model->isAttributeSafe('attr4'));
	}

	function testGetSafeAttributeNames(){
		$model=new NewModel;
		$safeAttributes = $model->getSafeAttributeNames();
		$this->assertContains('attr2', $safeAttributes);
		$this->assertContains('attr1', $safeAttributes);
	}

	public function testModifyValidators()
	{
		$model=new NewModel;
		$model->attr1=2;
		$model->attr2=2;
		$this->assertTrue($model->validate());
		$model->validatorList->insertAt(0,CValidator::createValidator('numerical',$model,'attr1,attr2',array('min'=>3)));
		$this->assertFalse($model->validate());
		$model->attr1=6;
		$model->attr2=6;
		$this->assertFalse($model->validate());
		$model->attr1=4;
		$model->attr2=4;
		$this->assertTrue($model->validate());
		$model=new NewModel;
		$model->attr1=3;
		$model->validatorList->add(CValidator::createValidator('required',$model,'attr2',array()));
		$this->assertFalse($model->validate());
		$model->attr2=3;
		$this->assertTrue($model->validate());
	}

	function testErrors(){
		$model=new NewModel;
		$model->attr1=3;
		$model->validatorList->add(CValidator::createValidator('required',$model,'attr2',array()));
		$model->validatorList->add(CValidator::createValidator('required',$model,'attr4',array()));
		$model->validate();

		$this->assertTrue($model->hasErrors());
		$this->assertTrue($model->hasErrors('attr2'));
		$this->assertFalse($model->hasErrors('attr1'));

		$model->clearErrors('attr2');
		$this->assertFalse($model->hasErrors('attr2'));

		$model->clearErrors();
		$this->assertFalse($model->hasErrors());
	}

	function testGetAttributes(){
		$model = new NewModel();
		$model->attr1 = 1;
		$model->attr2 = 2;

		$attributes = $model->getAttributes();
		$this->assertEquals(1, $attributes['attr1']);
		$this->assertEquals(2, $attributes['attr2']);

		$attributes = $model->getAttributes(array('attr1', 'non_existing'));
		$this->assertEquals(1, $attributes['attr1']);
		$this->assertEquals(null, $attributes['non_existing']);
	}

	function testUnsetAttributes(){
		$model = new NewModel();
		$model->attr1 = 1;
		$model->attr2 = 2;

		$model->unsetAttributes(array('attr1'));
		$this->assertEquals(null, $model->attr1);
		$this->assertEquals(2, $model->attr2);

		$model->unsetAttributes();
		$this->assertEquals(null, $model->attr1);
		$this->assertEquals(null, $model->attr2);
	}

	public function testGeneratingAttributeLabel()
	{
		$model = new NewModel();
		$this->assertSame('Department Name', $model->generateAttributeLabel('department_name'));
		$this->assertSame('First Name', $model->generateAttributeLabel('firstName'));
		$this->assertSame('Last Name', $model->generateAttributeLabel('LastName'));
	}

	public function testErrorsForAllAttributes()
	{
		$model = new NewModel();
		$model->validate();
		$this->assertNotEmpty($model->getErrors());
	}

	public function testErrorsForSingleAttribute()
	{
		$model = new NewModel();
		$model->validate();
		$this->assertNotEmpty($model->getErrors('attr1'));
		$this->assertEmpty($model->getErrors('attr2'));
	}

	public function testAddingAnErrorToSpecifiedAttribute()
	{
		$model = new NewModel();
		$model->validate();
		$model->clearErrors();
		$model->addError('firstName', 'This field is required');
		$this->assertSame(1, count($model->getErrors('firstName')));
	}

	public function testAddingAnErrorToASingleAttribute()
	{
		$model = new NewModel();
		$model->validate();
		$model->clearErrors();
		$model->addErrors(array('firstName' => 'This field is required'));
		$this->assertSame(1, count($model->getErrors('firstName')));
	}

	public function testAddingAnErrorToMultipleAttributes()
	{
		$model = new NewModel();
		$model->validate();
		$model->clearErrors();
		$model->addErrors(array(
			'firstName' => array('This attribute is required'),
			'LastName' => array('This field is required'),
		));
		$this->assertSame(1, count($model->getErrors('firstName')));
		$this->assertSame(1, count($model->getErrors('LastName')));
	}

	public function testAddingMultipleErrorsToASingleAttribute()
	{
		$model = new NewModel();
		$model->validate();
		$model->clearErrors();
		$model->addErrors(array('firstName' => array(
			'This attribute is required',
			'This field is required',
		)));
		$this->assertSame(2, count($model->getErrors('firstName')));
	}

	public function testAddingMultipleErrorsToMultipleAttributes()
	{
		$model = new NewModel();
		$model->validate();
		$model->clearErrors();
		$model->addErrors(array(
			'firstName' => array(
				'This attribute is required',
				'This field is required',
			),
			'LastName' => array(
				'This attribute is required',
				'This field is required',
			),
		));
		$this->assertSame(2, count($model->getErrors('firstName')));
		$this->assertSame(2, count($model->getErrors('LastName')));
	}

	public function testFirstErrorMessageForInvalidAttribute()
	{
		$model = new NewModel();
		$model->validate();
		$model->clearErrors();
		$model->addErrors(array(
			'attr1' => array(
				'This attribute is required.',
				'This field is required.',
			),
		));
		$this->assertSame('This attribute is required.', $model->getError('attr1'));
	}

	public function testNoErrorMessageForValidAttribute()
	{
		$model = new NewModel();
		$model->validate();
		$this->assertNull($model->getError('attr2'));
	}

	public function testModelWithInvalidRules()
	{
		$model = new InvalidModel();
		try {
			$this->assertFalse($model->validate());
		} catch (Exception $e) {
			$this->assertInstanceOf('CException', $e);
			$this->assertSame(
				'InvalidModel has an invalid validation rule. '.
				'The rule must specify attributes to be validated and the validator name.',
				$e->getMessage());
		}
	}
}
