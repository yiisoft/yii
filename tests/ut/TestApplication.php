<?php

Yii::import('system.web.CApplication');

class TestApplication extends CApplication
{
	public function processRequest()
	{
	}

	/**
	 * Removes all runtime files.
	 */
	public function reset()
	{
		$runtimePath=$this->getRuntimePath();
		if(is_dir($runtimePath) && ($folder=@opendir($runtimePath))!==false)
		{
			while($entry=@readdir($folder))
			{
				if($entry==='.' || $entry==='..')
					continue;
				$path=$runtimePath.DIRECTORY_SEPARATOR.$entry;
				@unlink($path);
			}
			@closedir($folder);
		}
	}

	public function loadGlobalState()
	{
		parent::loadGlobalState();
	}

	public function saveGlobalState()
	{
		parent::saveGlobalState();
	}
}