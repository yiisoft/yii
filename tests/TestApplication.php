<?php

class TestApplication extends CWebApplication
{
	private $tmpDir;
	public function __construct($config=null, $tmpDir=null)
	{
		Yii::setApplication(null);
		clearstatcache();
		$this->tmpDir = $tmpDir ? $tmpDir : __DIR__;
		parent::__construct($config);
	}

	public function reset()
	{
		$this->removeDirectory($this->getRuntimePath());
		$this->removeDirectory($this->getAssetPath());
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

	public function getAssetPath()
	{
		return $this->tmpDir.DIRECTORY_SEPARATOR.'assets';
	}

	public function getRuntimePath()
	{
		return $this->tmpDir.DIRECTORY_SEPARATOR.'runtime';
	}

	public function getBasePath()
	{
		return $this->tmpDir;
	}

	public function setBasePath($value)
	{
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
