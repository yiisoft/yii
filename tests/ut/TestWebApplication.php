<?php

Yii::import('system.web.CWebApplication');

class TestWebApplication extends CWebApplication
{
	/**
	 * Removes all runtime files.
	 */
	public function reset()
	{
		$runtimePath=$this->getRuntimePath();
		$this->removeDirectory($runtimePath);
		$this->removeDirectory(dirname(__FILE__).DIRECTORY_SEPARATOR.'assets');
	}

	protected function removeDirectory($path)
	{
		if(is_dir($path) && ($folder=@opendir($path))!==false)
		{
			while($entry=@readdir($folder))
			{
				if($entry[0]==='.')
					continue;
				$p=$path.DIRECTORY_SEPARATOR.$entry;
				if(is_dir($p))
					$this->removeDirectory($p);
				@unlink($p);
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