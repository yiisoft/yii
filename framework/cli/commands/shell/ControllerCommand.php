<?php
/**
 * ControllerCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 */

/**
 * ControllerCommand generates a controller class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.cli.commands.shell
 * @since 1.0
 */
class ControllerCommand extends CConsoleCommand
{
	/**
	 * @var string the template file for the controller class.
	 * Defaults to null, meaning using 'framework/cli/views/shell/controller/controller.php'.
	 */
	public $templateFile;

	public function getHelp()
	{
		return <<<EOD
USAGE
  controller <controller-ID> [action-ID] ...

DESCRIPTION
  This command generates a controller and views associated with
  the specified actions.

PARAMETERS
 * controller-ID: required, controller ID (e.g. 'post', 'admin.user')
 * action-ID: optional, action ID. You may supply one or several
   action IDs. A default 'index' action will always be generated.

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
			echo "Error: controller name is required.\n";
			echo $this->getHelp();
			return;
		}

		$controllerID=$args[0];
		if(($pos=strrpos($controllerID,'.'))===false)
		{
			$controllerClass=ucfirst($controllerID).'Controller';
			$controllerFile=Yii::app()->controllerPath.DIRECTORY_SEPARATOR.$controllerClass.'.php';
			$controllerID[0]=strtolower($controllerID[0]);
		}
		else
		{
			$controllerClass=ucfirst(substr($controllerID,$pos+1)).'Controller';
			$controllerFile=Yii::app()->controllerPath.DIRECTORY_SEPARATOR.str_replace('.',DIRECTORY_SEPARATOR,substr($controllerID,0,$pos)).DIRECTORY_SEPARATOR.$controllerClass.'.php';
			$controllerID[$pos+1]=strtolower($controllerID[$pos+1]);
		}

		$args[]='index';
		$actions=array_unique(array_splice($args,1));

		$templateFile=$this->templateFile===null?YII_PATH.'/cli/views/shell/controller/controller.php':$this->templateFile;

		$list=array(
			basename($controllerFile)=>array(
				'source'=>$templateFile,
				'target'=>$controllerFile,
				'callback'=>array($this,'generateController'),
				'params'=>array($controllerClass, $actions),
			),
		);

		$viewPath=Yii::app()->viewPath.DIRECTORY_SEPARATOR.str_replace('.',DIRECTORY_SEPARATOR,$controllerID);
		foreach($actions as $name)
		{
			$list[$name.'.php']=array(
				'source'=>YII_PATH.'/cli/views/shell/controller/view.php',
				'target'=>$viewPath.DIRECTORY_SEPARATOR.$name.'.php',
			);
		}

		$this->copyFiles($list);

		echo <<<EOD

Controller '{$controllerID}' has been created in the following file:
    $controllerFile

You may access it in the browser using the following URL:
    http://hostname/path/to/index.php?r={$controllerID}

EOD;
	}

	public function generateController($source,$params)
	{
		list($className,$actions)=$params;
		$content=file_get_contents($source);
		$actionTemplate=<<<EOD

	public function action{Name}()
	{
		\$this->render('{View}');
	}

EOD;
		$actionCode='';
		foreach($actions as $name)
			$actionCode.=strtr($actionTemplate,array('{Name}'=>ucfirst($name),'{View}'=>$name));
		return strtr($content,array(
			'{ClassName}'=>$className,
			'{Actions}'=>$actionCode));
	}
}