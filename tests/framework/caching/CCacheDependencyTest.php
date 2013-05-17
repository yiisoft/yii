<?php
Yii::import('system.caching.dependencies.CFileCacheDependency');

class CCacheDependencyTest extends CTestCase
{
    protected $_cacheDependentData=null;

    public function setCacheDependentData($cacheDependentData)
    {
        $this->_cacheDependentData = $cacheDependentData;
    }

    public function getCacheDependentData()
    {
        return $this->_cacheDependentData;
    }

    public function testReuseDependentData()
    {
        MockCacheDependency::$generateDependentDataCallback=array($this,'getCacheDependentData');
        $dependency1=new MockCacheDependency();
        $dependency1->reuseDependentData = true;
        $dependency2=new MockCacheDependency();
        $dependency2->reuseDependentData = true;

        CCacheDependency::resetReusableData();
        $this->setCacheDependentData('start');
        $dependency1->evaluateDependency();
        $dependency2->evaluateDependency();
        $this->assertFalse($dependency1->getHasChanged(),'Initial dependency1 changed!');
        $this->assertFalse($dependency2->getHasChanged(),'Initial dependency2 changed!');
        $this->assertEquals(1,MockCacheDependency::$generateDependentDataCalled,'Extra invokations of "generateDependentData()"!');

        // New request:
        CCacheDependency::resetReusableData();
        MockCacheDependency::$generateDependentDataCalled=0;
        $this->assertFalse($dependency1->getHasChanged(),'Dependency1 changed for new request!');
        $this->assertFalse($dependency2->getHasChanged(),'Dependency2 changed for new request!');
        $this->assertEquals(1,MockCacheDependency::$generateDependentDataCalled,'Extra invokations of "generateDependentData()"!');

        // New request:
        CCacheDependency::resetReusableData();
        MockCacheDependency::$generateDependentDataCalled=0;
        $this->setCacheDependentData('change1');
        $this->assertTrue($dependency1->getHasChanged(),'Dependency1 is not changed after source change!');
        $dependency1->evaluateDependency();

        // New request:
        CCacheDependency::resetReusableData();
        MockCacheDependency::$generateDependentDataCalled=0;
        $this->assertFalse($dependency1->getHasChanged(),'Dependency1 has been changed!');
        $this->assertTrue($dependency2->getHasChanged(),'Dependency2 has not been changed!');
        $this->assertEquals(1,MockCacheDependency::$generateDependentDataCalled,'Extra invokations of "generateDependentData()"!');
    }
}

class MockCacheDependency extends CCacheDependency
{
    public static $generateDependentDataCallback;
    public static $generateDependentDataCalled = 0;

    public function generateDependentData()
    {
        self::$generateDependentDataCalled++;
        return call_user_func(self::$generateDependentDataCallback);
    }
}