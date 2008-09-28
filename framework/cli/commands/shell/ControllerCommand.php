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
	public function getHelp()
	{
		return <<<EOD
USAGE
  controller <controller-name> [action-name] ...

DESCRIPTION
  This command generates a controller and views associated with
  the specified actions.

PARAMETERS
 * controller-name: required, the controller name.
 * action-name: optional, action name. You may supply one or
   or several action names. A default 'index' action will always
   be generated.

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
		$controllerName=$args[0];
		$controllerName[0]=strtolower($controllerName);
		$args[]='index';
		$actions=array_unique(array_splice($args,1));

		$controllerFile=ucfirst($controllerName).'Controller.php';

		$list=array(
			$controllerFile=>array(
				'source'=>YII_PATH.'/cli/views/shell/controller/controller.php',
				'target'=>Yii::app()->controllerPath.DIRECTORY_SEPARATOR.$controllerFile,
				'callback'=>array($this,'generateController'),
				'params'=>array(ucfirst($controllerName).'Controller', $actions),
			),
		);

		$viewPath=Yii::app()->viewPath.DIRECTORY_SEPARATOR.$controllerName;
		foreach($actions as $name)
		{
			$list[$name.'.php']=array(
				'source'=>YII_PATH.'/cli/views/shell/controller/view.php',
				'target'=>$viewPath.DIRECTORY_SEPARATOR.$name.'.php',
			);
		}

		$this->copyFiles($list);

		$path=Yii::app()->controllerPath.DIRECTORY_SEPARATOR.$controllerFile;
		echo <<<EOD

Controller '{$controllerName}' has been created in the following file:
    $path

You may access it in the browser using the following URL:
    http://hostname/path/to/index.php?r={$controllerName}

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