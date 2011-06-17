<?php

require_once 'ModelMock.php';

/**
 * CDateValidatorTest
 * 
 * @author   Kevin Bradwick <kbradwick@gmail.com>
 */
class CDateValidatorTest extends CTestCase
{
    /**
     * Test allow empty
     * 
     * @return null
     */
    public function testAllowEmptyOption()
    {
        $model = $this->getModelMock(array('allowEmpty' => true));
        $this->assertTrue($model->validate());

        $model = $this->getModelMock(array('allowEmpty' => false));
        $this->assertFalse($model->validate());
        $this->assertTrue($model->hasErrors('foo'));
    }

    /**
     * Test can validate different formats
     *
     * @return null
     */
    public function testFormatOption()
    {
        // default format
        $model = $this->getModelMock();
        $model->foo = '01/01/2011';
        $this->assertTrue($model->validate());
        $model->foo = '42/01/2011';
        $this->assertFalse($model->validate());

        // custom format
        $model = $this->getModelMock(array('format' => 'dd-MM-yyyy'));
        $model->foo = '01-01-2011';
        $this->assertTrue($model->validate());
        $model->foo = '01-24-2011';
        $this->assertFalse($model->validate());
    }

    /**
     * Test the timestamp option
     *
     * @return null
     */
    public function testTimestampOption()
    {
        $model = $this->getModelMock(array('timestampAttribute' => 'bar'));
        $model->foo = '01/01/2011';
        $this->assertTrue($model->validate());
        $this->assertInternalType('integer', $model->bar);
        $this->assertEquals(strtotime('1 Jan 2011'), $model->bar);
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
            array('foo', 'date')
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
