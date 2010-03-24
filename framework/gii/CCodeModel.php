<?php

abstract class CCodeModel extends CFormModel
{
	const STATUS_NEW=1;
	const STATUS_PREVIEW=2;
	const STATUS_SUCCESS=3;
	const STATUS_ERROR=4;

	public $files=array();
	public $answers;
	public $status=self::STATUS_NEW;

	abstract public function prepare($templatePath);

	public function save()
	{
		$result=true;
		foreach($this->files as $file)
		{
			if($this->confirmed($file))
				$result=$file->save() && $result;
		}
		return $result;
	}

	public function render($templateFile,$_params_=null)
	{
		if(!is_file($templateFile))
			throw new CException("The template file '$templateFile' does not exist.");

		if(is_array($_params_))
			extract($_params_,EXTR_PREFIX_SAME,'params');
		else
			$params=$_params_;
		ob_start();
		ob_implicit_flush(false);
		require($templateFile);
		return ob_get_clean();
	}

	public function confirmed($file)
	{
		return $this->answers===null && $file->operation===CCodeFile::OP_NEW
			|| is_array($this->answers) && isset($this->answers[md5($file->path)]);
	}

	public function renderConfirmation($file)
	{
		if($file->operation===CCodeFile::OP_SKIP)
			return '&nbsp;';
		$key=md5($file->path);
		if($file->operation===CCodeFile::OP_NEW)
			return CHtml::checkBox("answers[$key]", $this->confirmed($file));
		else if($file->operation===CCodeFile::OP_OVERWRITE)
			return CHtml::checkBox("answers[$key]", $this->confirmed($file));
	}

	public function renderResults()
	{
		$output='';
		foreach($this->files as $file)
		{
			if($file->error!==null)
				$output.="<span class=\"error\">{$file->relativePath}<br/>{$file->error}</span>\n";
			else if($file->operation===CCodeFile::OP_NEW && $this->confirmed($file))
				$output.=' generated '.$file->relativePath."\n";
			else if($file->operation===CCodeFile::OP_OVERWRITE && $this->confirmed($file))
				$output.=' overwrote '.$file->relativePath."\n";
			else
				$output.='   skipped '.$file->relativePath."\n";
		}
		return $output;
	}
}