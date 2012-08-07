<?php

require_once 'ModelMock.php';

/**
 * CCompareDateTimeValidatorTest
 * 
 * @author   Mariusz Wyszomierski <wyszomierski.mariusz@gmail.com>
 */
class CCompareDateTimeValidatorTest extends CTestCase
{ 
    /**
     * Test allow empty
     * 
     * @return null
     */
    public function testAllowEmptyOption()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar', 'allowEmpty' => true));
        $model->bar = '01/01/2011';
        $this->assertTrue($model->validate());

        $model = $this->getModelMock(array('compareAttribute'=>'bar', 'allowEmpty' => false));
        $model->bar = '01/01/2011';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
    }

    /**
     * Test equals dates and times in attributes
     *
     * @return null
     */
    public function testEqualsAttributes()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar'));
        $model->foo = '01/01/2011';
        $model->bar = '01/01/2011';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar'));
        $model->foo = '02/02/2011';
        $model->bar = '01/01/2011';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar'));
        $model->foo = '01/01/2011 18:00';
        $model->bar = '01/01/2011 18:00';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar'));
        $model->foo = '01/01/2011 18:00';
        $model->bar = '01/01/2011 18:01';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
    }
    
    /**
     * Test not equals dates and times in attributes
     *
     * @return null
     */
    public function testNotEqualsAttributes()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'!='));
        $model->foo = '01/01/2011';
        $model->bar = '01/01/2011';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'!='));
        $model->foo = '02/02/2011';
        $model->bar = '01/01/2011';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'!='));
        $model->foo = '01/01/2011 18:00';
        $model->bar = '01/01/2011 18:00';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'!='));
        $model->foo = '01/01/2011 18:00';
        $model->bar = '01/01/2011 18:01';
        $this->assertTrue($model->validate());
    }
    
    /**
     * Test we can catch validation errors
     * 
     * @return null
     */
    public function testValidationErrorsWitGreaterThan()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'>'));
        $model->foo = '01/01/2011';
        $model->bar = '01/01/2011';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'>'));
        $model->foo = '02/02/2011';
        $model->bar = '01/01/2011';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'>'));
        $model->foo = '01/01/2011 18:00';
        $model->bar = '01/01/2011 18:00';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'>'));
        $model->foo = '01/01/2011 18:01';
        $model->bar = '01/01/2011 18:00';
        $this->assertTrue($model->validate());
    }
    
    /**
     * Test we can catch validation errors
     * 
     * @return null
     */
    public function testValidationErrorsWitGreaterThanOrEqual()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'>='));
        $model->foo = '01/01/2010';
        $model->bar = '01/01/2011';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'>='));
        $model->foo = '01/01/2011';
        $model->bar = '01/01/2011';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'>='));
        $model->foo = '02/02/2011';
        $model->bar = '01/01/2011';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'>='));
        $model->foo = '01/01/2011 17:00';
        $model->bar = '01/01/2011 18:00';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'>='));
        $model->foo = '01/01/2011 18:00';
        $model->bar = '01/01/2011 18:00';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'>='));
        $model->foo = '01/01/2011 19:00';
        $model->bar = '01/01/2011 18:00';
        $this->assertTrue($model->validate());
    }
    
    /**
     * Test we can catch validation errors
     * 
     * @return null
     */
    public function testValidationErrorsWitLessThan()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'<'));
        $model->foo = '01/01/2011';
        $model->bar = '01/01/2011';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'<'));
        $model->foo = '01/01/2010';
        $model->bar = '01/01/2011';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'<'));
        $model->foo = '01/01/2011 18:00';
        $model->bar = '01/01/2011 18:00';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'<'));
        $model->foo = '01/01/2011 17:59';
        $model->bar = '01/01/2011 18:00';
        $this->assertTrue($model->validate());
    }
    
    /**
     * Test we can catch validation errors
     * 
     * @return null
     */
    public function testValidationErrorsWitLessThanOrEqual()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'<='));
        $model->foo = '02/01/2011';
        $model->bar = '01/01/2011';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'<='));
        $model->foo = '01/01/2011';
        $model->bar = '01/01/2011';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'<='));
        $model->foo = '01/02/2010';
        $model->bar = '01/01/2011';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'<='));
        $model->foo = '01/01/2011 18:01';
        $model->bar = '01/01/2011 18:00';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'<='));
        $model->foo = '01/01/2011 18:00';
        $model->bar = '01/01/2011 18:00';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'<='));
        $model->foo = '01/01/2011 17:59';
        $model->bar = '01/01/2011 18:00';
        $this->assertTrue($model->validate());
    }
    
    /**
     * Test overriding value by setting compareValue
     *
     * @return null
     */
    public function testOverrideCompareValue()
    {
        $model = $this->getModelMock(array('compareValue'=>'01/01/2011'));
        $model->foo = '01/01/2011';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareValue'=>'01/01/2011'));
        $model->foo = '02/02/2011';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareValue'=>'01/01/2011 18:00'));
        $model->foo = '01/01/2011 18:00';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareValue'=>'01/01/2011 18:01'));
        $model->foo = '01/01/2011 18:00';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
    }
    
    /**
     * Test different formats of dates
     * 
     * @return null
     */
    public function testDifferentFormats()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar'));
        $model->foo = '01/01/2011';
        $model->bar = '01-01-2011';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar'));
        $model->foo = '2011-01-01';
        $model->bar = '01.01.2011';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','operator'=>'<'));
        $model->foo = 'now';
        $model->bar = '+1 day';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar'));
        $model->foo = 'March 2012';
        $model->bar = '2012-03';
        $this->assertTrue($model->validate());
    }
    
    /**
     * Test flag DATE 
     * 
     * @return null
     */
    public function testFlagDATE()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::DATE));
        $model->foo = '2012-08-01';
        $model->bar = '2012-08-01';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::DATE));
        $model->foo = '2012-08-01 19:52';
        $model->bar = '2012-08-01 18:11';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::DATE));
        $model->foo = '2012-08-01';
        $model->bar = '2012-08-02';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
    }
    
    /**
     * Test flag TIME 
     * 
     * @return null
     */
    public function testFlagTIME()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::TIME));
        $model->foo = '18:05:03';
        $model->bar = '18:05:03';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::TIME));
        $model->foo = '2012-08-01 18:05:03';
        $model->bar = '2012-07-31 18:05:03';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::TIME));
        $model->foo = '18:05:03';
        $model->bar = '18:05:04';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::TIME));
        $model->foo = '2012-08-01 18:05:03';
        $model->bar = '2012-08-01 18:05:04';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
    }
    
    /**
     * Test flag HOUR_MINUTE 
     * 
     * @return null
     */
    public function testFlagHOUR_MINUTE()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::HOUR_MINUTE));
        $model->foo = '18:05';
        $model->bar = '18:05';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::HOUR_MINUTE));
        $model->foo = '2012-08-01 18:05:03';
        $model->bar = '2012-07-31 18:05:15';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::HOUR_MINUTE));
        $model->foo = '18:05';
        $model->bar = '18:06';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::HOUR_MINUTE));
        $model->foo = '2012-08-01 18:05';
        $model->bar = '2012-08-01 18:06';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
    }
    
    /**
     * Test flag YEAR 
     * 
     * @return null
     */
    public function testFlagYEAR()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::YEAR));
        $model->foo = '2012-08-01';
        $model->bar = '2012-08-02';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::YEAR));
        $model->foo = '2011-08-01';
        $model->bar = '2012-08-02';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
    }
    
    /**
     * Test flag MONTH 
     * 
     * @return null
     */
    public function testFlagMONTH()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::MONTH));
        $model->foo = '2011-08-01';
        $model->bar = '2012-08-02';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::MONTH));
        $model->foo = '2011-07-01';
        $model->bar = '2011-08-01';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
    }
    
    /**
     * Test flag DAY 
     * 
     * @return null
     */
    public function testFlagDAY()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::DAY));
        $model->foo = '2011-08-01';
        $model->bar = '2012-07-01';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::DAY));
        $model->foo = '2011-07-01';
        $model->bar = '2011-07-02';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
    }
    
    /**
     * Test flag HOUR 
     * 
     * @return null
     */
    public function testFlagHOUR()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::HOUR));
        $model->foo = '18:15';
        $model->bar = '18:37';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::HOUR));
        $model->foo = '18:15';
        $model->bar = '08:37';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
    }
    
    /**
     * Test flag MINUTE 
     * 
     * @return null
     */
    public function testFlagMINUTE()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::MINUTE));
        $model->foo = '18:15';
        $model->bar = '20:15';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::MINUTE));
        $model->foo = '18:15';
        $model->bar = '20:16';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
    }
    
    /**
     * Test flag SECOND 
     * 
     * @return null
     */
    public function testFlagSECOND()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::SECOND));
        $model->foo = '18:15:14';
        $model->bar = '20:15:14';
        $this->assertTrue($model->validate());
        
        $model = $this->getModelMock(array('compareAttribute'=>'bar','compareFlag'=>CCompareDateTimeValidator::SECOND));
        $model->foo = '18:15:14';
        $model->bar = '20:15:13';
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
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
     * @expectedException CException
     */
    public function testThrowsExcpetionForValueThatCantBeParsed()
    {
        $model = $this->getModelMock(array('compareValue' => '03.2012'));
        $model->validate();
    }
    
    /**
     * Test error when compared attribute contains date that can't be parsed
     * 
     * @return null
     */
    public function testThrowsExcpetionForAttributeThatCantBeParsed()
    {
        $model = $this->getModelMock(array('compareAttribute'=>'bar'));
        $model->bar='03.2012';
        $model->validate();
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('bar'));
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
            array('foo', 'compareDateTime')
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
