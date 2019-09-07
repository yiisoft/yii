<?php

class CApplicationComponentTest extends CTestCase
{
    public function testInitialization()
    {
        $c = new class() extends CApplicationComponent
        {
        };

        $this->assertFalse($c->getIsInitialized());
        $c->init();
        $this->assertTrue($c->getIsInitialized());
    }
}
