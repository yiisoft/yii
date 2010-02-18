<?php
require_once dirname(__FILE__) . '/NewComponent.php';
require_once dirname(__FILE__) . '/NewBehavior.php';

class CBehaviorTest extends CTestCase {

	public function testAttachBehavior() {
		$component=new NewComponent;
		$component->attachBehavior('a',new NewBehavior);
		$this->assertFalse($component->behaviorCalled);
		$this->assertFalse(method_exists($component,'test'));
		$this->assertEquals(2,$component->test());
		$this->assertTrue($component->behaviorCalled);
		$this->setExpectedException('CException');
		$component->test2();
	}
}
