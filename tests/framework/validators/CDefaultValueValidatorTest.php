<?php

require_once 'ModelMock.php';

/**
 * CDateValidatorTest
 * 
 * @author   Kevin Bradwick <kbradwick@gmail.com>
 */
class CDefaultValueValidatorTest extends CTestCase
{
    /**
     * When value is empty|null, ensure a default value is set
     *
     * @return null
     */
    public function testDefaultValueIsSetWhenSetOnEmptyIsTrue()
    {
        $model = $this->getModelMock(array('value' => 'foo'));
        $this->assertTrue($model->validate());
        $this->assertEquals('foo', $model->foo);

        $model->foo = 'bar';
        $this->assertTrue($model->validate());
        $this->assertEquals('bar', $model->foo);

    }

    /**
     * By setting setOnEmpty to false, the value will always be overriden
     *
     * @return null
     */
    public function testDefaultValueIsSetWhenSetOnEmptyIsFalse()
    {
        $model = $this->getModelMock(array('value' => 'foo', 'setOnEmpty' => false));
        $model->foo = 'bar';
        $this->assertTrue($model->validate());
        $this->assertEquals('foo', $model->foo);
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
            array('foo', 'default')
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
