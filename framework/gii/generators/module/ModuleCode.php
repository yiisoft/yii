<?php

class ModuleCode extends CCodeModel
{
	public $moduleID;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('moduleID', 'filter', 'filter'=>'trim'),
			array('moduleID', 'required'),
			array('moduleID', 'match', 'pattern'=>'/^\w+$/', 'message'=>'{attribute} should only contain word characters.'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'moduleID'=>'Module ID',
		));
	}

	public function prepare()
	{
		$this->files=array();
		$templatePath=$this->templatePath;
		$modulePath=$this->modulePath;
		$moduleTemplateFile=$templatePath.DIRECTORY_SEPARATOR.'module.php';

		$this->files[]=new CCodeFile(
			$modulePath.'/'.$this->moduleClass.'.php',
			$this->render($moduleTemplateFile)
		);

		$files=CFileHelper::findFiles($templatePath,array(
			'exclude'=>array('.svn'),
		));

		foreach($files as $file)
		{
			if($file!==$moduleTemplateFile)
			{
				if(CFileHelper::getExtension($file)==='php')
					$content=$this->render($file);
				else
					$content=file_get_contents($file);
				$this->files[]=new CCodeFile(
					$modulePath.substr($file,strlen($templatePath)),
					$content
				);
			}
		}
	}

	public function getModuleClass()
	{
		return ucfirst($this->moduleID).'Module';
	}

	public function getModulePath()
	{
		return Yii::app()->modulePath.DIRECTORY_SEPARATOR.$this->moduleID;
	}
}