<?php

/**
 * Used in CBehaviorTest
 */
class BehaviorTestController extends CController
{
	public $behaviorEventHandled=0;

	public function onTestEvent()
	{
		$this->raiseEvent("onTestEvent", new CEvent());
	}
}
