<?php
/**
 * CrudCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 */

/**
 * CrudCommand generates code implementing CRUD operations.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.cli.commands.shell
 * @since 1.0
 */
class CrudCommand extends CConsoleCommand
{
	/**
	 * @var string the directory that contains templates for crud commands.
	 * Defaults to null, meaning using 'framework/cli/views/shell/crud'.
	 * If you set this path and some views are missing in the directory,
	 * the default views will be used.
	 */
	public $templatePath;

	public function getHelp()
	{
		return <<<EOD
USAGE
  crud <model-class> [controller-name] ...

DESCRIPTION
  This command generates a controller and views that accomplish
  CRUD operations for the specified data model.

PARAMETERS
 * model-class: required, the name of the data model class. This can
   also be specified using dot syntax (e.g. application.models.Post)
 * controller-name: optional, the name of the controller. If not given,
   it will use the model class as the controller name.

EOD;
	}

	/**
	 * Execute the action.
	 * @param array command line parameters specific for this command
	 */
	public function run($args)
	{
		if(!isset($args[0]))
		{
			echo "Error: data model class is required.\n";
			echo $this->getHelp();
			return;
		}
		$modelClass=$args[0];
		if(strpos($modelClass,'.')===false)
			$modelClass='application.models.'.$modelClass;
		$modelClass=Yii::import($modelClass);
		$controllerName=isset($args[1])?$args[1]:strtolower($modelClass);
		$controllerClass=ucfirst($controllerName).'Controller';
		$templatePath=$this->templatePath===null?YII_PATH.'/cli/views/shell/crud':$this->templatePath;
		$viewPath=Yii::app()->viewPath.DIRECTORY_SEPARATOR.$controllerName;
		$list=array(
			$controllerClass.'.php'=>array(
				'source'=>$templatePath.'/controller.php',
				'target'=>Yii::app()->controllerPath.'/'.$controllerClass.'.php',
				'callback'=>array($this,'generateController'),
				'params'=>array($controllerClass,$modelClass),
			),
		);
		foreach(array('create','update','list','show') as $action)
		{
			$list[$action.'.php']=array(
				'source'=>$templatePath.'/'.$action.'.php',
				'target'=>$viewPath.'/'.$action.'.php',
				'callback'=>array($this,'generateView'),
				'params'=>$modelClass,
			);
		}

		$this->copyFiles($list);

		echo "\nCrud '{$controllerName}' has been successfully created. You may access it via:\n";
		echo "http://hostname/path/to/index.php?r={$controllerName}\n";
	}

	public function generateController($source,$params)
	{
		list($controllerClass,$modelClass)=$params;
		$model=CActiveRecord::model($modelClass);
		$id=$model->tableSchema->primaryKey;
		if($id===null)
			throw new ShellException(Yii::t('yii','Error: Table "{table}" does not have a primary key.',array('{table}'=>$model->tableName())));
		else if(is_array($id))
			throw new ShellException(Yii::t('yii','Error: Table "{table}" has a composite primary key which is not supported by crud command.',array('{table}'=>$model->tableName())));

		if(!is_file($source))  // fall back to default ones
			$source=YII_PATH.'/cli/views/shell/crud/'.basename($source);

		$content=file_get_contents($source);
		return strtr($content,array(
			'{ClassName}'=>$controllerClass,
			'{ID}'=>$id,
			'{ModelClass}'=>$modelClass,
			'{ModelVar}'=>strtolower($modelClass),
			'{ModelName}'=>strtolower($modelClass)));
	}

	public function generateView($source,$modelClass)
	{
		$model=CActiveRecord::model($modelClass);
		$table=$model->getTableSchema();
		$columns=$table->columns;
		unset($columns[$table->primaryKey]);
		if(basename($source)==='list.php')
		{
			foreach($columns as $name=>$column)
				if(stripos($column->dbType,'text')!==false)
					unset($columns[$name]);
		}
		if(!is_file($source))  // fall back to default ones
			$source=YII_PATH.'/cli/views/shell/crud/'.basename($source);
		return $this->renderFile($source,array(
			'ID'=>$table->primaryKey,
			'model'=>$model,
			'modelClass'=>$modelClass,
			'modelVar'=>strtolower($modelClass),
			'columns'=>$columns),true);
	}

	public function generateInputField($model,$modelVar,$column)
	{
		if($column->type==='boolean')
			return "CHtml::activeCheckBox(\${$modelVar},'{$column->name}')";
		else if(stripos($column->dbType,'text')!==false)
			return "CHtml::activeTextArea(\${$modelVar},'{$column->name}',array('rows'=>6, 'cols'=>50))";
		else if($column->type!=='string' || $column->size===null)
			return "CHtml::activeTextField(\${$modelVar},'{$column->name}')";
		else
		{
			if(($size=$maxLength=$column->size)>60)
				$size=60;
			return "CHtml::activeTextField(\${$modelVar},'{$column->name}',array('size'=>$size,'maxlength'=>$maxLength))";
		}
	}
}
