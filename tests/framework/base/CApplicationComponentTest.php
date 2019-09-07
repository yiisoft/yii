<?php

class CApplicationComponentTest extends CTestCase {
	public function testInitialization() {
		$c = $this->getMockForAbstractClass('CApplicationComponent',array('init','getIsInitialized'),'',false);
		$c->expects($this->any())
			->method('getIsInitialized')
			->willReturn(false);
		$this->assertFalse($c->getIsInitialized());
		$c->init();
		$this->assertTrue($c->getIsInitialized());
	}
}
