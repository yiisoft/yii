<?php
class CActiveRecordCollection extends CModelCollection
{
	public function save($runValidation=true,$attributes=null)
	{
		if($runValidation && !$this->validate($attributes))
			return false;

		$result=true;

		foreach($this as $model)
			$result=($model->getIsNewRecord() ? $model->insert($attributes) : $model->update($attributes)) && $result;

		return $result;
	}
}