<?php
require_once dirname(__FILE__) . '/NewComponent.php';
require_once dirname(__FILE__) . '/NewBehavior.php';

require_once dirname(__FILE__) . '/NewBeforeValidateBehavior.php';
require_once dirname(__FILE__) . '/NewFormModel.php';

require_once dirname(__FILE__) . '/BehaviorTestController.php';
require_once dirname(__FILE__) . '/TestBehavior.php';

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

	/**
	 * https://github.com/yiisoft/yii/issues/162
	 */
	public function testDuplicateEventHandlers()
	{
		$controller = new BehaviorTestController('behaviorTest');

		$b = new TestBehavior();
		$this->assertFalse($b->enabled);

		$b->attach($controller);
		$this->assertTrue($b->enabled);

		$b->setEnabled(true);
		$this->assertTrue($b->enabled);

		$controller->onTestEvent();
		$this->assertEquals(1, $controller->behaviorEventHandled);

		$b->setEnabled(false);
		$this->assertFalse($b->enabled);
		$controller->onTestEvent();
		$this->assertEquals(1, $controller->behaviorEventHandled);

		$b->setEnabled(true);
		$this->assertTrue($b->enabled);
		$controller->onTestEvent();
		$this->assertEquals(2, $controller->behaviorEventHandled);

		$b->detach($controller);
		$this->assertFalse($b->enabled);
		$controller->onTestEvent();
		$this->assertEquals(2, $controller->behaviorEventHandled);

		$b->setEnabled(true);
		$this->assertTrue($b->enabled);
		$controller->onTestEvent();
		$this->assertEquals(2, $controller->behaviorEventHandled);
	}
}

