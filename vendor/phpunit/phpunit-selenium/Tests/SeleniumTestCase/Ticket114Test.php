<?php
class Tests_SeleniumTestCase_Ticket114Test extends Tests_SeleniumTestCase_BaseTestCase
{
    public function testDependable()
    {
        return 'dependsValue';
    }

    /**
     * @dataProvider exampleDataProvider
     * @depends testDependable
     */
    public function testDependent($dataProvider, $depends)
    {
        $this->assertSame($dataProvider, 'dataProviderValue');
        $this->assertSame($depends, 'dependsValue');
    }

    public function exampleDataProvider()
    {
        return array(
            array('dataProviderValue'),
        );
    }
}
