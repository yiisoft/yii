<?php

class CCodeFile extends CComponent
{
	const OP_NEW='new';
	const OP_OVERWRITE='overwrite';
	const OP_SKIP='skip';

	public $path;
	public $content;
	public $operation;
	public $error;

	public function __construct($path,$content)
	{
		$this->path=strtr($path,array('/'=>DIRECTORY_SEPARATOR,'\\'=>DIRECTORY_SEPARATOR));
		$this->content=$content;
		if(is_file($path))
			$this->operation=file_get_contents($path)===$content ? self::OP_SKIP : self::OP_OVERWRITE;
		else
			$this->operation=self::OP_NEW;
	}

	public function save()
	{
		if($this->operation===self::OP_NEW)
		{
			$dir=dirname($this->path);
			if(!is_dir($dir) && !@mkdir($dir,0755,true))
			{
				$this->error="Unable to create the directory '$dir'.";
				return false;
			}
		}
		if(!@file_put_contents($this->path,$this->content))
		{
			$this->error="Unable to write the file '{$this->path}'.";
			return false;
		}
		return true;
	}

	public function getRelativePath()
	{
		if(strpos($this->path,Yii::app()->basePath)===0)
			return substr($this->path,strlen(Yii::app()->basePath)+1);
		else
			return $this->path;
	}

	public function getType()
	{
		if(($pos=strrpos($this->path,'.'))!==false)
			return substr($this->path,$pos+1);
		else
			return 'unknown';
	}
}