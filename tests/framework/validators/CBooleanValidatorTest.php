<?php

require_once 'ModelMock.php';

/**
 * CBooleanValidatorTest
 * 
 * @author   Kevin Bradwick <kbradwick@gmail.com>
 */
class CBooleanValidatorTest extends CTestCase
{
    /**
     * Test we can catch validation errors using custom false/true values
     * 
     * @return null
     */
    public function testValidationUsingCustomFalseTrueValues()
    {
        $model = $this->getModelMock(array('falseValue' => 'false', 'trueValue' => 'true'));
        $model->foo = 'blah';
        $this->assertFalse($model->hasErrors('foo'));

        $model->foo = 'false';
        $this->assertTrue($model->validate());

        // client script
        $validator = new CBooleanValidator;
        $validator->trueValue = 'foo';
        $validator->falseValue = 'bar';
        $script = $validator->clientValidateAttribute($model, 'foo');
        $this->assertContains('Foo must be either foo or bar', $script);
    }

    /**
     * Test allow empty
     *
     * @return null
     */
    public function testValidateAndAllowEmpty()
    {
        $model = $this->getModelMock(array('allowEmpty' => false));
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));

        $model = $this->getModelMock(array('allowEmpty' => true));
        $this->assertTrue($model->validate());
    }

    /**
     * Test using strict
     *
     * @return null
     */
    public function testValidationUsingStrict()
    {
        $model = $this->getModelMock(array('strict' => true));
        $model->foo = 1;
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));

        $model->foo = '1';
        $this->assertTrue($model->hasErrors('foo'));
    }

    public function testValidateValue()
    {
	    $validator = new CBooleanValidator();
	    $this->assertTrue($validator->validateValue('1'));
	    $this->assertTrue($validator->validateValue('0'));
	    $this->assertTrue($validator->validateValue(true));
	    $this->assertTrue($validator->validateValue(false));
	    $this->assertTrue($validator->validateValue(1));
	    $this->assertTrue($validator->validateValue(0));
	    $this->assertFalse($validator->validateValue(null));
	    $this->assertFalse($validator->validateValue('foo'));

	    $validator->strict = true;
	    $this->assertTrue($validator->validateValue('1'));
	    $this->assertTrue($validator->validateValue('0'));
	    $this->assertFalse($validator->validateValue(true));
	    $this->assertFalse($validator->validateValue(false));
	    $this->assertFalse($validator->validateValue(1));
	    $this->assertFalse($validator->validateValue(0));
	    $this->assertFalse($validator->validateValue(null));
	    $this->assertFalse($validator->validateValue('foo'));
    }

    /**
     * Test array typed value
     * https://github.com/yiisoft/yii/issues/1955
     *
     * @return null
     */
    public function testValidateArrayValue()
    {
        $model = $this->getModelMock();
        $model->foo = array(1);
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        $this->assertSame(array('Foo must be either 1 or 0.'), $model->getErrors('foo'));
    }
    
    /**
     * Mocks up an object to test with
     *
     * @param array $params additional parameters sent to rules
     *
     * @return null
     */
    protected function getModelMock($params=array())
    {
        $rules = array(
            array('foo', 'boolean')
        );

        foreach ($params as $rule => $value) {
            $rules[0][$rule] = $value;
        }
        
        $stub = $this->getMock('ModelMock', array('rules'));
        $stub->expects($this->any())
             ->method('rules')
             ->will($this->returnValue($rules));
        
        return $stub;
    }
}
