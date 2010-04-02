<?php

class FormCode extends CCodeModel
{
	public $model;
	public $view;
	public $scenario;

	private $_modelClass;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('model, view, scenario', 'filter', 'filter'=>'trim'),
			array('model, view', 'required'),
			array('model, view', 'match', 'pattern'=>'/^\w+[\.\w+]*$/', 'message'=>'{attribute} should only contain word characters and dots.'),
			array('model', 'validateModel'),
			array('view', 'validateView'),
			array('scenario', 'match', 'pattern'=>'/^\w+$/', 'message'=>'{attribute} should only contain word characters.'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'model'=>'Model Class',
			'view'=>'View Name',
			'scenario'=>'Scenario',
		));
	}

	public function validateModel($attribute,$params)
	{
		if($this->hasErrors('model'))
			return;
		$class=@Yii::import($this->model,true);
		if(!is_string($class) || !class_exists($class,false))
			$this->addError('model', "Class '{$this->model}' does not exist or has syntax error.");
		else if(!is_subclass_of($class,'CModel'))
			$this->addError('model', "'{$this->model}' must extend from CModel.");
		else
			$this->_modelClass=$class;
	}

	public function validateView($attribute,$params)
	{
		if($this->hasErrors('model'))
			return;
		if(Yii::getPathOfAlias($this->view)===false)
			$this->addError('view','View Name must be a valid path alias.');
	}

	public function prepare()
	{
		$templatePath=$this->templatePath;
		$this->files[]=new CCodeFile(
			Yii::getPathOfAlias($this->view).'.php',
			$this->render($templatePath.'/form.php')
		);
	}

	public function getModelClass()
	{
		return $this->_modelClass;
	}

	public function getModelAttributes()
	{
		$model=new $this->_modelClass($this->scenario);
		return $model->getSafeAttributeNames();
	}
}