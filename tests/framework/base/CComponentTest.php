<?php
require_once dirname(__FILE__) . '/NewComponent.php';
require_once dirname(__FILE__) . '/NewBehavior.php';

function globalEventHandler($event)
{
	$event->sender->eventHandled=true;
}

function globalEventHandler2($event)
{
	$event->sender->eventHandled=true;
	$event->handled=true;
}

class CComponentTest extends CTestCase
{
	protected $component;

	public function setUp()
	{
		$this->component = new NewComponent();
	}

	public function tearDown()
	{
		$this->component = null;
	}

	public function testHasProperty()
	{
		$this->assertTrue($this->component->hasProperty('Text'), "Component hasn't property Text");
		$this->assertTrue($this->component->hasProperty('text'), "Component hasn't property text");
		$this->assertTrue($this->component->hasProperty('Object'), "Component hasn't property Object");
		$this->assertTrue($this->component->hasProperty('object'), "Component hasn't property object");
		$this->assertFalse($this->component->hasProperty('Caption'), "Component has property Caption");
	}

	public function testCanGetProperty()
	{
		$this->assertTrue($this->component->canGetProperty('Text'));
		$this->assertTrue($this->component->canGetProperty('text'));
		$this->assertFalse($this->component->canGetProperty('Caption'));
		$this->assertTrue($this->component->canGetProperty('Object'));
		$this->assertTrue($this->component->canGetProperty('object'));
	}

	public function testCanSetProperty()
	{
		$this->assertTrue($this->component->canSetProperty('Text'));
		$this->assertTrue($this->component->canSetProperty('text'));
		$this->assertFalse($this->component->canSetProperty('Caption'));
		$this->assertFalse($this->component->canSetProperty('Object'));
		$this->assertFalse($this->component->canSetProperty('object'));
	}

	public function testGetProperty()
	{
		$this->assertSame('default', $this->component->Text);
		$this->assertSame('default', $this->component->text);
		$this->assertInstanceOf('NewComponent', $this->component->Object);
		$this->assertInstanceOf('NewComponent', $this->component->object);

		try {
			$this->component->caption;
		} catch (Exception $e) {
			$this->assertInstanceOf('CException', $e);
			$this->assertSame('Property "NewComponent.caption" is not defined.', $e->getMessage());
		}
	}

	public function testSetProperty()
	{
		$this->component->text = 'new value';
		$this->assertSame('new value', $this->component->text);

		try {
			$this->component->object = new NewComponent();
		} catch (Exception $e) {
			$this->assertInstanceOf('CException', $e);
			$this->assertSame('Property "NewComponent.object" is read only.', $e->getMessage());
		}

		try {
			$this->component->newMember = 'new value';
		} catch (Exception $e) {
			$this->assertInstanceOf('CException', $e);
			$this->assertSame('Property "NewComponent.newMember" is not defined.', $e->getMessage());
		}
	}

	public function testIsset()
	{
		$this->assertTrue(isset($this->component->Text));
		$this->assertFalse(empty($this->component->Text));
		$this->assertTrue(isset($this->component->object));
	}

	public function testUnset()
	{
		unset($this->component->Text);
		$this->assertFalse(isset($this->component->Text));
		$this->assertTrue(empty($this->component->Text));

		$this->component->Text='new text';
		$this->assertTrue(isset($this->component->Text));
		$this->assertFalse(empty($this->component->Text));

		try {
			unset($this->component->object);
		} catch (Exception $e) {
			$this->assertInstanceOf('CException', $e);
			$this->assertSame('Property "NewComponent.object" is read only.', $e->getMessage());
		}
	}

	public function testCallMethodFromBehavior()
	{
		$this->component->attachBehavior('newBehavior', new NewBehavior);
		$this->assertSame(2, $this->component->test());

		try {
			$this->component->otherMethod();
		} catch (Exception $e) {
			$this->assertInstanceOf('CException', $e);
			$this->assertSame('NewComponent and its behaviors do not have a method or closure named "otherMethod".', $e->getMessage());
		}
	}

	public function testHasEvent()
	{
		$this->assertTrue($this->component->hasEvent('OnMyEvent'));
		$this->assertTrue($this->component->hasEvent('onmyevent'));
		$this->assertFalse($this->component->hasEvent('onYourEvent'));
	}

	public function testHasEventHandler()
	{
		$this->assertFalse($this->component->hasEventHandler('OnMyEvent'));
		$this->component->attachEventHandler('OnMyEvent','foo');
		$this->assertTrue($this->component->hasEventHandler('OnMyEvent'));
	}

	public function testGetEventHandlers()
	{
		$list=$this->component->getEventHandlers('OnMyEvent');
		$this->assertSame($list->getCount(),0);
		$this->component->attachEventHandler('OnMyEvent','foo');
		$this->assertSame($list->getCount(),1);

		try {
			$this->component->getEventHandlers('YourEvent');
		} catch (Exception $e) {
			$this->assertInstanceOf('CException', $e);
			$this->assertSame('Event "NewComponent.YourEvent" is not defined.', $e->getMessage());
		}
	}

	public function testAttachEventHandler()
	{
		$this->component->attachEventHandler('OnMyEvent','foo');
		$this->assertInstanceOf('CList', $this->component->getEventHandlers('onMyEvent'));
		$this->assertSame(1, $this->component->getEventHandlers('onMyEvent')->getCount());

		try {
			$this->component->attachEventHandler('onYourEvent', 'foo');
		} catch (Exception $e) {
			$this->assertInstanceOf('CException', $e);
			$this->assertSame('Event "NewComponent.onYourEvent" is not defined.', $e->getMessage());
		}
	}

	public function testDettachEventHandler()
	{
		$this->component->attachEventHandler('OnMyEvent','foo');
		$this->component->attachEventHandler('OnMyEvent',array($this->component,'myEventHandler'));
		$this->assertSame($this->component->getEventHandlers('OnMyEvent')->getCount(),2);

		$this->assertTrue($this->component->detachEventHandler('OnMyEvent','foo'));
		$this->assertSame($this->component->getEventHandlers('OnMyEvent')->getCount(),1);

		$this->assertFalse($this->component->detachEventHandler('OnMyEvent','foo'));
		$this->assertSame($this->component->getEventHandlers('OnMyEvent')->getCount(),1);

		$this->assertTrue($this->component->detachEventHandler('OnMyEvent',array($this->component,'myEventHandler')));
		$this->assertSame($this->component->getEventHandlers('OnMyEvent')->getCount(),0);

		$this->assertFalse($this->component->detachEventHandler('OnMyEvent','foo'));
	}

	public function testRaiseEvent()
	{
		$this->component->attachEventHandler('OnMyEvent',array($this->component,'myEventHandler'));
		$this->assertFalse($this->component->eventHandled);
		$this->component->raiseEvent('OnMyEvent',new CEvent($this->component));
		$this->assertTrue($this->component->eventHandled);

		try {
			$this->component->raiseEvent('OnUnknown', new CEvent($this->component));
		} catch (Exception $e) {
			$this->assertInstanceOf('CException', $e);
			$this->assertSame('Event "NewComponent.onunknown" is not defined.', $e->getMessage());
		}
	}

	public function testEventAccessor()
	{
		$component=new NewComponent;
		$this->assertSame($component->onMyEvent->getCount(),0);
		$component->onMyEvent='globalEventHandler';
		$component->onMyEvent=array($this->component,'myEventHandler');
		$this->assertSame($component->onMyEvent->getCount(),2);
		$this->assertFalse($component->eventHandled);
		$this->assertFalse($this->component->eventHandled);
		$component->onMyEvent();
		$this->assertTrue($component->eventHandled);
		$this->assertTrue($this->component->eventHandled);
	}

	public function testStopEvent()
	{
		$component=new NewComponent;
		$component->onMyEvent='globalEventHandler2';
		$component->onMyEvent=array($this->component,'myEventHandler');
		$component->onMyEvent();
		$this->assertTrue($component->eventHandled);
		$this->assertFalse($this->component->eventHandled);
	}

	public function testInvalidHandler1()
	{
		$this->component->onMyEvent = array(1, 2, 3);

		try {
			$this->component->onMyEvent();
		} catch (Exception $e) {
			$this->assertInstanceOf('CException', $e);
			$this->assertSame('Event "NewComponent.onmyevent" is attached with an invalid handler "array".', $e->getMessage());
		}
	}

	public function testInvalidHandler2()
	{
		$this->component->onMyEvent = array($this->component, 'nullHandler');

		try {
			$this->component->onMyEvent();
		} catch (Exception $e) {
			$this->assertInstanceOf('CException', $e);
			$this->assertSame('Event "NewComponent.onmyevent" is attached with an invalid handler "nullHandler".', $e->getMessage());
		}
	}

	public function testAttachingBehavior()
	{
		$this->component->attachBehavior('newBehavior', 'NewBehavior');
		$this->assertInstanceOf('NewBehavior', $this->component->newBehavior);
	}

	public function testDetachingBehavior()
	{
		$behavior = new NewBehavior;
		$this->component->attachBehavior('newBehavior', $behavior);
		$this->assertSame($behavior, $this->component->detachBehavior('newBehavior'));
		$this->assertFalse(isset($this->component->newBehavior));
	}

	public function testDetachingBehaviors()
	{
		$behavior = new NewBehavior;

		$this->component->attachBehavior('newBehavior', $behavior);
		$this->component->detachBehaviors();

		try {
			$this->component->test();
		} catch (Exception $e) {
			$this->assertInstanceOf('CException', $e);
			$this->assertSame('NewComponent and its behaviors do not have a method or closure named "test".', $e->getMessage());
		}
	}

	public function testEnablingBehavior()
	{
		$behavior = new NewBehavior;

		$this->component->attachBehavior('newBehavior', $behavior);
		$this->component->disableBehavior('newBehavior');
		$this->assertFalse($behavior->getEnabled());

		$this->component->enableBehavior('newBehavior');
		$this->assertTrue($behavior->getEnabled());
	}

	public function testEnablingBehaviors()
	{
		$behavior = new NewBehavior;

		$this->component->attachBehavior('newBehavior', $behavior);
		$this->component->disableBehaviors();
		$this->assertFalse($behavior->getEnabled());

		$this->component->enableBehaviors();
		$this->assertTrue($behavior->getEnabled());
	}

	public function testAsa()
	{
		$behavior = new NewBehavior;
		$this->component->attachBehavior('newBehavior', $behavior);
		$this->assertSame($behavior, $this->component->asa('newBehavior'));
	}

	public function testEvaluateExpression()
	{
		$this->assertSame('Hello world', $this->component->evaluateExpression('"Hello $who"', array('who' => 'world')));
		$this->assertSame('Hello world', $this->component->evaluateExpression(array($this->component, 'exprEvaluator'), array('who' => 'world')));
	}
}
