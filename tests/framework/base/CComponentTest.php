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
		$this->assertEquals('default', $this->component->Text);
		$this->assertEquals('default', $this->component->text);
		$this->assertInstanceOf('NewComponent', $this->component->Object);
		$this->assertInstanceOf('NewComponent', $this->component->object);
	}

	/**
	 * @expectedException CException
	 * @expectedExceptionMessage Property "NewComponent.caption" is not defined.
	 */
	public function testGetException()
	{
		$this->component->caption;
	}

	public function testSetProperty()
	{
		$this->component->text = 'new value';
		$this->assertEquals('new value', $this->component->text);
	}

	/**
	 * @expectedException CException
	 * @expectedExceptionMessage Property "NewComponent.newMember" is not defined.
	 */
	public function testSetException()
	{
		$this->component->newMember = 'new value';
	}

	public function testIsset()
	{
		$this->assertTrue(isset($this->component->Text));
		$this->assertTrue(!empty($this->component->Text));

		unset($this->component->Text);
		$this->assertFalse(isset($this->component->Text));
		$this->assertFalse(!empty($this->component->Text));

		$this->component->Text='';
		$this->assertTrue(isset($this->component->Text));
		$this->assertTrue(empty($this->component->Text));
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
		$this->assertEquals($list->getCount(),0);
		$this->component->attachEventHandler('OnMyEvent','foo');
		$this->assertEquals($list->getCount(),1);
		$this->setExpectedException('CException');
		$list=$this->component->getEventHandlers('YourEvent');
	}

	public function testAttachEventHandler()
	{
		$this->component->attachEventHandler('OnMyEvent','foo');
		$this->assertTrue($this->component->getEventHandlers('OnMyEvent')->getCount()===1);
		$this->setExpectedException('CException');
		$this->component->attachEventHandler('YourEvent','foo');
	}

	public function testDettachEventHandler()
	{
		$this->component->attachEventHandler('OnMyEvent','foo');
		$this->component->attachEventHandler('OnMyEvent',array($this->component,'myEventHandler'));
		$this->assertEquals($this->component->getEventHandlers('OnMyEvent')->getCount(),2);

		$this->assertTrue($this->component->detachEventHandler('OnMyEvent','foo'));
		$this->assertEquals($this->component->getEventHandlers('OnMyEvent')->getCount(),1);

		$this->assertFalse($this->component->detachEventHandler('OnMyEvent','foo'));
		$this->assertEquals($this->component->getEventHandlers('OnMyEvent')->getCount(),1);

		$this->assertTrue($this->component->detachEventHandler('OnMyEvent',array($this->component,'myEventHandler')));
		$this->assertEquals($this->component->getEventHandlers('OnMyEvent')->getCount(),0);

		$this->assertFalse($this->component->detachEventHandler('OnMyEvent','foo'));
	}

	public function testRaiseEvent()
	{
		$this->component->attachEventHandler('OnMyEvent',array($this->component,'myEventHandler'));
		$this->assertFalse($this->component->eventHandled);
		$this->component->raiseEvent('OnMyEvent',new CEvent($this));
		$this->assertTrue($this->component->eventHandled);

		//$this->setExpectedException('CException');
		//$this->component->raiseEvent('OnUnknown',new CEvent($this));
	}

	public function testEventAccessor()
	{
		$component=new NewComponent;
		$this->assertEquals($component->onMyEvent->getCount(),0);
		$component->onMyEvent='globalEventHandler';
		$component->onMyEvent=array($this->component,'myEventHandler');
		$this->assertEquals($component->onMyEvent->getCount(),2);
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
		$this->component->onMyEvent=array(1,2,3);
		$this->setExpectedException('CException');
		$this->component->onMyEvent();
	}

	public function testInvalidHandler2()
	{
		$this->component->onMyEvent=array($this->component,'nullHandler');
		$this->setExpectedException('CException');
		$this->component->onMyEvent();
	}
	public function testDetachBehavior() {
		$component=new NewComponent;
		$behavior = new NewBehavior; 
		$component->attachBehavior('a',$behavior);
		$this->assertSame($behavior,$component->detachBehavior('a'));
	}
	public function testDetachingBehaviors() {
		$component=new NewComponent;
		$behavior = new NewBehavior; 
		$component->attachBehavior('a',$behavior);
		$component->detachBehaviors();
		$this->setExpectedException('CException');
		$component->test();
	}
	public function testEnablingBehavior() {
		$component=new NewComponent;
		$behavior = new NewBehavior; 
		$component->attachBehavior('a',$behavior);
		$component->disableBehavior('a');
		$this->assertFalse($behavior->getEnabled());
		$component->enableBehavior('a');
		$this->assertTrue($behavior->getEnabled());
	}
	public function testEnablingBehaviors() {
		$component=new NewComponent;
		$behavior = new NewBehavior; 
		$component->attachBehavior('a',$behavior);
		$component->disableBehaviors();
		$this->assertFalse($behavior->getEnabled());
		$component->enableBehaviors();
		$this->assertTrue($behavior->getEnabled());
	}
	public function testAsa() {
		$component=new NewComponent;
		$behavior = new NewBehavior; 
		$component->attachBehavior('a',$behavior);
		$this->assertSame($behavior,$component->asa('a'));
	}
	public function testEvaluateExpression() {
		$component = new NewComponent;
		$this->assertEquals('Hello world',$component->evaluateExpression('"Hello $who"',array('who' => 'world')));
		$this->assertEquals('Hello world',$component->evaluateExpression(array($component,'exprEvaluator'),array('who' => 'world')));
	}
}
