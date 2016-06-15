<?php
/**
 * AutoloadCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yeeframework.com/
 * @copyright 2008-2013 Yee Software LLC
 * @license http://www.yeeframework.com/license/
 */

/**
 * AutoloadCommand generates the class map for {@link YeeBase}.
 * The class file YeeBase.php will be modified with updated class map.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.build
 * @since 1.0
 */
class AutoloadCommand extends CConsoleCommand
{
	public function getHelp()
	{
		return <<<EOD
USAGE
  build autoload

DESCRIPTION
  This command updates YeeBase.php with the latest class map.
  The class map is used by Yee::autoload() to quickly include a class on demand.

  Do not run this command unless you change or add core framework classes.

EOD;
	}

	/**
	 * Execute the action.
	 * @param array command line parameters specific for this command
	 */
	public function run($args)
	{
		$options=array(
			'fileTypes'=>array('php'),
			'exclude'=>array(
				'.gitignore',
				'/messages',
				'/views',
				'/cli',
				'/yee.php',
				'/yeet.php',
				'/yeelite.php',
				'/web/js',
				'/vendors',
				'/i18n/data',
				'/utils/mimeTypes.php',
				'/test',
				'/zii',
				'/gii',
			),
		);
		$files=CFileHelper::findFiles(YII_PATH,$options);
		$map=array();
		foreach($files as $file)
		{
			if(($pos=strpos($file,YII_PATH))!==0)
				die("Invalid file '$file' found.");
			$path=str_replace('\\','/',substr($file,strlen(YII_PATH)));
			$className=substr(basename($path),0,-4);
			if($className[0]==='C')
				$map[$path]="\t\t'$className' => '$path',\n";
		}
		ksort($map);
		$map=implode($map);

		$yeeBase=file_get_contents(YII_PATH.'/YeeBase.php');
		$newYeeBase=preg_replace('/private\s+static\s+\$_coreClasses\s*=\s*array\s*\([^\)]*\)\s*;/',"private static \$_coreClasses=array(\n{$map}\t);",$yeeBase);
		if($yeeBase!==$newYeeBase)
		{
			file_put_contents(YII_PATH.'/YeeBase.php',$newYeeBase);
			echo "YeeBase.php is updated successfully.\n";
		}
		else
			echo "Nothing changed.\n";
	}
}
