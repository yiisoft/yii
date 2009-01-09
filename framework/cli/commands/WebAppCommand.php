<?php
/**
 * WebAppCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 */

/**
 * WebAppCommand creates an Yii Web application at the specified location.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.cli.commands
 * @since 1.0
 */
class WebAppCommand extends CConsoleCommand
{
	public function getHelp()
	{
		return <<<EOD
USAGE
  yiic webapp <app-path>

DESCRIPTION
  This command generates an Yii Web Application at the specified location.

PARAMETERS
 * app-path: required, the directory where the new application will be created.
   If the directory does not exist, it will be created. After the application
   is created, please make sure the directory can be accessed by Web users.

EOD;
	}

	/**
	 * Execute the action.
	 * @param array command line parameters specific for this command
	 */
	public function run($args)
	{
		if(!isset($args[0]))
			$this->usageError('the Web application location is not specified.');
		$path=strtr($args[0],'/\\',DIRECTORY_SEPARATOR);
		if(strpos($path,DIRECTORY_SEPARATOR)===false)
			$path='.'.DIRECTORY_SEPARATOR.$path;
		$dir=realpath(dirname($path));
		if($dir===false || !is_dir($dir))
			$this->usageError("The directory '$path' is not valid. Please make sure the parent directory exists.");
		$path=$dir.DIRECTORY_SEPARATOR.basename($path);
		echo "Create a Web application under '$path'? [Yes|No] ";
		if(!strncasecmp(trim(fgets(STDIN)),'y',1))
		{
			$sourceDir=realpath(dirname(__FILE__).'/../views/webapp');
			$list=$this->buildFileList($sourceDir,$path);
			$list['index.php']['callback']=array($this,'generateIndex');
			$list['protected/yiic.php']['callback']=array($this,'generateYiic');
			$this->copyFiles($list);
			@chmod($path.'/assets',0777);
			@chmod($path.'/protected/runtime',0777);
			@chmod($path.'/protected/yiic',0755);
			echo "\nYour application has been created successfully under {$path}.\n";
		}
	}

	public function generateIndex($source,$params)
	{
		$content=file_get_contents($source);
		return str_replace('{YiiPath}',realpath(dirname(__FILE__).'/../../yii.php'),$content);
	}

	public function generateYiic($source,$params)
	{
		$content=file_get_contents($source);
		return str_replace('{YiicPath}',realpath(dirname(__FILE__).'/../../yiic.php'),$content);
	}
}