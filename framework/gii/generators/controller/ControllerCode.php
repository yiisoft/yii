<?php

class ControllerCode extends CCodeModel
{
	public $controller;
	public $baseClass='Controller';
	public $actions='index';

	public function rules()
	{
		return array(
			array('controller, actions, baseClass', 'filter', 'filter'=>'trim'),
			array('controller, baseClass', 'required'),
			array('controller', 'match', 'pattern'=>'/^\w+[\w+\\/]*$/', 'message'=>'{attribute} should only contain word characters and slashes.'),
			array('actions', 'match', 'pattern'=>'/^\w+[\w\s,]*$/', 'message'=>'{attribute} should only contain word characters, spaces and commas.'),
			array('baseClass', 'match', 'pattern'=>'/^\w+$/', 'message'=>'{attribute} should only contain word characters.'),
		);
	}

	public function attributeLabels()
	{
		return array(
			'controller'=>'Controller ID',
			'actions'=>'Action IDs',
			'baseClass'=>'Base Controller Class',
		);
	}

	public function prepare($templatePath)
	{
		$this->files=array();

		$controllerFile=Yii::app()->controllerPath;
		if(($pos=strrpos($this->controller,'/'))!==false)
			$controllerFile.='/'.substr($this->controller,0,$pos);
		$controllerFile.='/'.$this->getControllerClass().'.php';

		$this->files[]=new CCodeFile(
			$controllerFile,
			$this->render($templatePath.'/controller.php')
		);

		foreach($this->getActionIDs() as $action)
		{
			$this->files[]=new CCodeFile(
				Yii::app()->viewPath.'/'.$this->controller.'/'.$action.'.php',
				$this->render($templatePath.'/view.php', array('action'=>$action))
			);
		}
	}

	public function getActionIDs()
	{
		$actions=preg_split('/[\s,]+/',$this->actions,-1,PREG_SPLIT_NO_EMPTY);
		$actions=array_unique($actions);
		sort($actions);
		return $actions;
	}

	public function getControllerID()
	{
		if(($pos=strrpos($this->controller,'/'))!==false)
			return substr($this->controller,$pos+1);
		else
			return $this->controller;
	}

	public function getControllerClass()
	{
		if(($pos=strrpos($this->controller,'/'))!==false)
			return ucfirst(substr($this->controller,$pos+1)).'Controller';
		else
			return ucfirst($this->controller).'Controller';
	}
}