<?php

class FormCode extends CCodeModel
{
	public $modelClass;
	public $viewName;
	public $scenario;
	
	private $viewFile = null;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('modelClass, viewName, scenario', 'filter', 'filter'=>'trim'),
			array('modelClass, viewName', 'required'),
			array('modelClass, viewName', 'match', 'pattern'=>'/^\w+(\.\w+)*$/', 'message'=>'{attribute} should only contain word characters and dots.'),
			array('scenario', 'match', 'pattern'=>'/^\w+*$/', 'message'=>'{attribute} should only contain word characters.'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'modelClass'=>'Model',
			'viewName'=>'View name',
			'scenarion'=>'Scenarion',
		));
	}

	public function prepare()
	{
		$modelClass=Yii::import($this->modelClass,true);
		$model=new $modelClass($scenario);
		$attributes = $model->getSafeAttributeNames();
		$this->viewFile = Yii::getPathOfAlias($this->viewName). '.php';

		$templatePath=$this->templatePath;

		$this->files[]=new CCodeFile(
			$this->viewFile,
			$this->render($templatePath.'/form.php', array('attributes'=>$attributes))
		);
	}
	
	public function getActionFunction(){
		$modelClass=Yii::import($this->modelClass,true);
		$model=new $modelClass($scenario);
		return $this->render($this->templatePath.'/action.php', array('modelClass'=>$modelClass));
	}

	public function class2id($className)
	{
		if(strrpos($className,'Form')===strlen($className)-4)
			$className=substr($className,0,strlen($className)-4);
		return trim(strtolower(str_replace('_','-',preg_replace('/(?<![A-Z])[A-Z]/', '-\0', $className))),'-');
	}
}