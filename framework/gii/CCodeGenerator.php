<?php

class CCodeGenerator extends Controller
{
	public $layout='generator';
	public $templates=array();

	public $codeModel;

	private $_viewPath;

	public function init()
	{
		parent::init();
		$this->breadcrumbs=array(ucwords($this->id.' generator'));
	}

	public function actionIndex()
	{
		$model=$this->prepare();
		if($model->files!=array() && isset($_POST['generate'], $_POST['answers']))
		{
			$model->answers=$_POST['answers'];
			$model->status=$model->save() ? CCodeModel::STATUS_SUCCESS : CCodeModel::STATUS_ERROR;
		}

		$this->render('index',array(
			'model'=>$model,
		));
	}

	public function actionCode()
	{
		$model=$this->prepare();
		if(isset($_GET['id']) && isset($model->files[$_GET['id']]))
		{
			$this->renderPartial('gii.views.common.code', array(
				'file'=>$model->files[$_GET['id']],
			));
		}
		else
			throw new CHttpException(404,'Unable to find the code you requested.');
	}

	public function actionDiff()
	{
		Yii::import('gii.components.TextDiff');

		$model=$this->prepare();
		if(isset($_GET['id']) && isset($model->files[$_GET['id']]))
		{
			$file=$model->files[$_GET['id']];
			if(!in_array($file->type,array('php', 'txt','js','css')))
				$diff=false;
			else if($file->operation===CCodeFile::OP_OVERWRITE)
				$diff=TextDiff::compare(file_get_contents($file->path), $file->content);
			else
				$diff='';

			$this->renderPartial('gii.views.common.diff',array(
				'file'=>$file,
				'diff'=>$diff,
			));
		}
		else
			throw new CHttpException(404,'Unable to find the code you requested.');
	}

	public function getViewPath()
	{
		if($this->_viewPath===null)
		{
			$class=new ReflectionClass(get_class($this));
			$this->_viewPath=dirname($class->getFileName()).DIRECTORY_SEPARATOR.'views';
		}
		return $this->_viewPath;
	}

	public function setViewPath($value)
	{
		$this->_viewPath=$value;
	}

	public function renderGenerator($model)
	{
		$this->renderPartial('gii.views.common.generator', array('model'=>$model));
	}

	public function getSuccessMessage($model)
	{
		return 'The code has been generated successfully.';
	}

	protected function prepare()
	{
		if($this->codeModel===null)
			throw new CException(get_class($this).'.codeModel property must be specified.');
		$modelClass=Yii::import($this->codeModel,true);
		$model=new $modelClass;
		$model->templates=$this->templates;
		if(isset($_POST[$modelClass]))
		{
			$model->attributes=$_POST[$modelClass];
			$model->status=CCodeModel::STATUS_PREVIEW;
			if($model->validate())
				$model->prepare();
		}
		return $model;
	}
}