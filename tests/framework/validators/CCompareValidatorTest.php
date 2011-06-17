<?php

require_once 'ModelMock.php';

/**
 * CCompareValidatorTest
 * 
 * @author   Kevin Bradwick <kbradwick@gmail.com>
 */
class CCompareValidatorTest extends CTestCase
{
    /**
     * Test we can catch validation errors
     * 
     * @return null
     */
    public function testValidationErrorsWithEquals()
    {
        $model = $this->getModelMock(array('compareAttribute' => 'bar'));
        $model->foo = 'foo';
        $model->bar = 'bar';
        
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        $model->bar = 'foo';
        $this->assertTrue($model->validate());
        
        // client validation
        $validator = new CCompareValidator;
        $validator->operator = '=';
        $validator->compareAttribute = 'bar';
        $script = $validator->clientValidateAttribute($model, 'foo');
        $this->assertInternalType('string', $script);
        $this->assertContains('Foo must be repeated exactly.', $script);
    }
    
    /**
     * Test we can catch validation errors
     * 
     * @return null
     */
    public function testValidationErrorsWithNotEquals()
    {
        $model = $this->getModelMock(array(
            'operator' => '!=',
            'strict' => true,
            'compareAttribute' => 'bar',
        ));
        $model->foo = 'foo';
        $model->bar = 'bar';
        
        $this->assertTrue($model->validate());
        
        $model->bar = 'foo';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        // client validation
        $validator = new CCompareValidator;
        $validator->operator = '!=';
        $validator->compareAttribute = 'bar';
        $script = $validator->clientValidateAttribute($model, 'foo');
        $this->assertInternalType('string', $script);
        $this->assertContains('Foo must not be equal to \"Bar\".', $script);
    }
    
    /**
     * Test we can catch validation errors
     * 
     * @return null
     */
    public function testValidationErrorsWitGreaterThan()
    {
        $model = $this->getModelMock(array(
            'operator' => '>',
            'strict' => true,
            'compareAttribute' => 'bar',
        ));
        $model->foo = 1;
        $model->bar = 2;
        
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        $model->bar = 0;
        $this->assertTrue($model->validate());
        
        // client validation
        $validator = new CCompareValidator;
        $validator->operator = '>';
        $validator->compareAttribute = 'bar';
        $script = $validator->clientValidateAttribute($model, 'foo');
        $this->assertInternalType('string', $script);
        $this->assertContains('Foo must be greater than \"Bar\"', $script);
    }
    
    /**
     * Test we can catch validation errors
     * 
     * @return null
     */
    public function testValidationErrorsWitGreaterThanOrEqual()
    {
        $model = $this->getModelMock(array(
            'operator' => '>=',
            'strict' => true,
            'compareAttribute' => 'bar',
        ));
        $model->foo = 1;
        $model->bar = 2;
        
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        $model->bar = 1;
        $this->assertTrue($model->validate());
        
        // client validation
        $validator = new CCompareValidator;
        $validator->operator = '>=';
        $validator->compareAttribute = 'bar';
        $script = $validator->clientValidateAttribute($model, 'foo');
        $this->assertInternalType('string', $script);
        $this->assertContains('Foo must be greater than or equal to \"Bar\"', $script);
    }
    
    /**
     * Test we can catch validation errors
     * 
     * @return null
     */
    public function testValidationErrorsWitLessThan()
    {
        $model = $this->getModelMock(array(
            'operator' => '<',
            'strict' => true,
            'compareAttribute' => 'bar',
        ));
        $model->foo = 3;
        $model->bar = 2;
        
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        $model->bar = 4;
        $this->assertTrue($model->validate());
        
        // client validation
        $validator = new CCompareValidator;
        $validator->operator = '<';
        $validator->compareAttribute = 'bar';
        $script = $validator->clientValidateAttribute($model, 'foo');
        $this->assertInternalType('string', $script);
        $this->assertContains('Foo must be less than \"Bar\"', $script);
    }
    
    /**
     * Test we can catch validation errors
     * 
     * @return null
     */
    public function testValidationErrorsWitLessThanOrEqual()
    {
        $model = $this->getModelMock(array(
            'operator' => '<=',
            'strict' => true,
            'compareAttribute' => 'bar',
        ));
        $model->foo = 3;
        $model->bar = 2;
        
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        $model->bar = 3;
        $this->assertTrue($model->validate());
        
        // client validation
        $validator = new CCompareValidator;
        $validator->operator = '<=';
        $validator->compareAttribute = 'bar';
        $script = $validator->clientValidateAttribute($model, 'foo');
        $this->assertInternalType('string', $script);
        $this->assertContains('Foo must be less than or equal to \"Bar\"', $script);
    }
    
    /**
     * @expectedException CException
     */
    public function testClientValidateAttributeThrowsExcpetion()
    {
        $model = $this->getModelMock(array(
            'operator' => '<=',
            'strict' => true,
            'compareAttribute' => 'bar',
        ));
        $validator = new CCompareValidator;
        $validator->operator = '}';
        $validator->clientValidateAttribute($model, 'foo');
    }
    
    /**
     * @expectedException CException
     */
    public function testValidateThrowsExcpetionforBadOperator()
    {
        $model = $this->getModelMock(array(
            'operator' => ']]',
            'strict' => true,
            'compareAttribute' => 'bar',
        ));
        $model->validate();
    }
    
    /**
     * Test overriding value by setting compareValue
     * 
     * @return null
     */
    public function testOverrideCompareValue()
    {
        $rules = array(
            array('foo', 'compare', 'compareValue' => 'hello')
        );
        
        $stub = $this->getMock('ModelMock', array('rules'));
        $stub->expects($this->any())
             ->method('rules')
             ->will($this->returnValue($rules));
        
        $stub->foo = 'foo';
        $this->assertFalse($stub->validate());
        
        // client validation
        $validator = new CCompareValidator;
        $validator->operator = '=';
        $validator->compareValue = 'bar';
        $script = $validator->clientValidateAttribute($stub, 'foo');
        $this->assertInternalType('string', $script);
        $this->assertContains('Foo must be repeated exactly', $script);
    }
    
    /**
     * Mocks up an object to test with
     * 
     * @param array $operator optional parameters to configure rule
     * 
     * @return null
     */
    protected function getModelMock($params=array())
    {
        $rules = array(
            array('foo', 'compare')
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
