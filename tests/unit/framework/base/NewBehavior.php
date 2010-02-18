<?php
class NewBehavior extends CBehavior
{
	public function test()
	{
		$this->owner->behaviorCalled=true;
		return 2;
	}
}
