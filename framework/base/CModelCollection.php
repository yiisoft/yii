<?php
abstract class CModelCollection extends CMap
{
	public function validate($attributes=null,$clearErrors=true)
	{
		$result=true;

		foreach($this as $model)
			$result=$model->validate($attributes,$clearErrors) && $result;

		return $result;
	}

	public function setAttributes($values,$safeOnly=true)
	{
		foreach($values as $k=>$v)
		{
			if(isset($this[$k]))
				$this[$k]->setAttributes($v,$safeOnly);
		}
	}
}