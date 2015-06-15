<?php
require_once('ValidatorTestModel.php');

class CStringValidatorTest extends CTestCase
{
	public function testMin()
	{
		// null value
		$model1=new ValidatorTestModel('CStringValidatorTest');
		$model1->validate(array('string1'));
		$this->assertTrue($model1->hasErrors('string1'));
		$this->assertSame(array('Too short message.'),$model1->getErrors('string1'));

		// 9 characters length value
		$model2=new ValidatorTestModel('CStringValidatorTest');
		$model2->string1='123456789';
		$model2->validate(array('string1'));
		$this->assertTrue($model2->hasErrors('string1'));
		$this->assertSame(array('Too short message.'),$model2->getErrors('string1'));

		// 10 characters length value
		$model3=new ValidatorTestModel('CStringValidatorTest');
		$model3->string1='1234567890';
		$model3->validate(array('string1'));
		$this->assertFalse($model3->hasErrors('string1'));
		$this->assertNotSame(array('Too short message.'),$model3->getErrors('string1'));

		// array value: https://github.com/yiisoft/yii/issues/1955
		$model4=new ValidatorTestModel('CStringValidatorTest');
		$model4->string1=array('1234567890');
		$model4->validate(array('string1'));
		$this->assertTrue($model4->hasErrors('string1'));
	}

	public function testMax()
	{
		// null value
		$model1=new ValidatorTestModel('CStringValidatorTest');
		$model1->validate(array('string2'));
		$this->assertFalse($model1->hasErrors('string2'));
		$this->assertNotSame(array('Too long message.'),$model1->getErrors('string2'));

		// 11 characters length value
		$model2=new ValidatorTestModel('CStringValidatorTest');
		$model2->string2='12345678901';
		$model2->validate(array('string2'));
		$this->assertTrue($model2->hasErrors('string2'));
		$this->assertSame(array('Too long message.'),$model2->getErrors('string2'));

		// 10 characters length value
		$model3=new ValidatorTestModel('CStringValidatorTest');
		$model3->string2='1234567890';
		$model3->validate(array('string2'));
		$this->assertFalse($model3->hasErrors('string2'));
		$this->assertNotSame(array('Too long message.'),$model3->getErrors('string2'));

		// array value: https://github.com/yiisoft/yii/issues/1955
		$model4=new ValidatorTestModel('CStringValidatorTest');
		$model4->string2=array('1234567890');
		$model4->validate(array('string2'));
		$this->assertTrue($model4->hasErrors('string2'));
	}

	public function testIs()
	{
		// null value
		$model1=new ValidatorTestModel('CStringValidatorTest');
		$model1->validate(array('string3'));
		$this->assertTrue($model1->hasErrors('string3'));
		$this->assertSame(array('Error message.'),$model1->getErrors('string3'));

		// 9 characters length value
		$model2=new ValidatorTestModel('CStringValidatorTest');
		$model2->string3='123456789';
		$model2->validate(array('string3'));
		$this->assertTrue($model2->hasErrors('string3'));
		$this->assertSame(array('Error message.'),$model2->getErrors('string3'));
		
		// 11 characters length value
		$model3=new ValidatorTestModel('CStringValidatorTest');
		$model3->string3='12345678901';
		$model3->validate(array('string3'));
		$this->assertTrue($model3->hasErrors('string3'));
		$this->assertSame(array('Error message.'),$model3->getErrors('string3'));

		// 10 characters length value
		$model4=new ValidatorTestModel('CStringValidatorTest');
		$model4->string3='1234567890';
		$model4->validate(array('string3'));
		$this->assertFalse($model4->hasErrors('string3'));
		$this->assertNotSame(array('Error message.'),$model4->getErrors('string3'));

		// array value: https://github.com/yiisoft/yii/issues/1955
		$model5=new ValidatorTestModel('CStringValidatorTest');
		$model5->string3=array('1234567890');
		$model5->validate(array('string3'));
		$this->assertTrue($model5->hasErrors('string3'));
	}
}
