<?php
class CActiveRecordCollection extends CModelCollection
{
	public function insert($attributes=null)
	{
		$result=true;

		foreach($this as $model)
			$result=$model->insert($attributes) && $result;

		return $result;
	}

	public function update($attributes=null)
	{
		$result=true;

		foreach($this as $model)
			$result=$model->update($attributes) && $result;

		return $result;
	}

	public function save($runValidation=true,$attributes=null)
	{
		if(!$runValidation || $this->validate($attributes))
			return $this->getIsNewRecord() ? $this->insert($attributes) : $this->update($attributes);
		else
			return false;
	}
}