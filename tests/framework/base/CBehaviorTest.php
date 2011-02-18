<?php
require_once dirname(__FILE__) . '/NewComponent.php';
require_once dirname(__FILE__) . '/NewBehavior.php';

require_once dirname(__FILE__) . '/NewBeforeValidateBehavior.php';
require_once dirname(__FILE__) . '/NewFormModel.php';

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

    public function testDisableBehaviors(){
        $component=new NewComponent;
        $component->attachBehavior('a',new NewBehavior);
        $component->disableBehaviors();
        $this->setExpectedException('CException');
        // test should not be called since behavior is disabled
        echo $component->test();
    }

    /**
     * Since disableBehaviors() was called, validate() should not call beforeValidate() from behavior.
     * @return void
     */
    public function testDisableBehaviorsAndModels(){
        $model = new NewFormModel();
        $model->disableBehaviors();
        $model->validate();
    }

    /**
     * enableBehaviors() should work after disableBehaviors().
     * @return void
     */
    public function testDisableAndEnableBehaviorsAndModels(){
        $this->setExpectedException('NewBeforeValidateBehaviorException');
        $model = new NewFormModel();
        $model->disableBehaviors();
        $model->enableBehaviors();
        $model->validate();
    }
}