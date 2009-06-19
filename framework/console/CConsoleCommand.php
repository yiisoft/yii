<?php
/**
 * CConsoleCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CConsoleCommand represents an executable user command.
 *
 * The {@link run} method must be implemented with the actual command execution logic.
 * You may override {@link getHelp} to provide more detailed description of the command.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.console
 * @since 1.0
 */
abstract class CConsoleCommand extends CComponent
{
	private $_name;
	private $_runner;

	/**
	 * Executes the command.
	 * @param array command line parameters for this command.
	 */
	public abstract function run($args);

	/**
	 * Constructor.
	 * @param string name of the command
	 * @param CConsoleCommandRunner the command runner
	 */
	public function __construct($name,$runner)
	{
		$this->_name=$name;
		$this->_runner=$runner;
	}

	/**
	 * @return string the command name.
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * @return CConsoleCommandRunner the command runner instance
	 */
	public function getCommandRunner()
	{
		return $this->_runner;
	}

	/**
	 * Provides the command description.
	 * This method may be overridden to return the actual command description.
	 * @return string the command description. Defaults to 'Usage: php entry-script.php command-name'.
	 */
	public function getHelp()
	{
		return 'Usage: '.$this->getCommandRunner()->getScriptName().' '.$this->getName();
	}

	/**
	 * Displays a usage error.
	 * This method will then terminate the execution of the current application.
	 * @param string the error message
	 */
	public function usageError($message)
	{
		die("Error: $message\n\n".$this->getHelp()."\n");
	}

	/**
	 * Copies a list of files from one place to another.
	 * @param array the list of files to be copied (name=>spec).
	 * The array keys are names displayed during the copy process, and array values are specifications
	 * for files to be copied. Each array value must be an array of the following structure:
	 * <ul>
	 * <li>source: required, the full path of the file/directory to be copied from</li>
	 * <li>target: required, the full path of the file/directory to be copied to</li>
	 * <li>callback: optional, the callback to be invoked when copying a file. The callback function
	 *   should be declared as follows:
	 *   <pre>
	 *   function foo($source,$params)
	 *   </pre>
	 *   where $source parameter is the source file path, and the content returned
	 *   by the function will be saved into the target file.</li>
	 * <li>params: optional, the parameters to be passed to the callback</li>
	 * </ul>
	 * @see buildFileList
	 */
	public function copyFiles($fileList)
	{
		$overwriteAll=false;
		foreach($fileList as $name=>$file)
		{
			$source=strtr($file['source'],'/\\',DIRECTORY_SEPARATOR);
			$target=strtr($file['target'],'/\\',DIRECTORY_SEPARATOR);
			$callback=isset($file['callback']) ? $file['callback'] : null;
			$params=isset($file['params']) ? $file['params'] : null;

			if(is_dir($source))
			{
				$this->ensureDirectory($target);
				continue;
			}

			if($callback!==null)
				$content=call_user_func($callback,$source,$params);
			else
				$content=file_get_contents($source);
			if(is_file($target))
			{
				if($content===file_get_contents($target))
				{
					echo "  unchanged $name\n";
					continue;
				}
				if($overwriteAll)
					echo "  overwrite $name\n";
				else
				{
					echo "      exist $name\n";
					echo "            ...overwrite? [Yes|No|All|Quit] ";
					$answer=trim(fgets(STDIN));
					if(!strncasecmp($answer,'q',1))
						return;
					else if(!strncasecmp($answer,'y',1))
						echo "  overwrite $name\n";
					else if(!strncasecmp($answer,'a',1))
					{
						echo "  overwrite $name\n";
						$overwriteAll=true;
					}
					else
					{
						echo "       skip $name\n";
						continue;
					}
				}
			}
			else
			{
				$this->ensureDirectory(dirname($target));
				echo "   generate $name\n";
			}
			file_put_contents($target,$content);
		}
	}

	/**
	 * Builds the file list of a directory.
	 * This method traverses through the specified directory and builds
	 * a list of files and subdirectories that the directory contains.
	 * The result of this function can be passed to {@link copyFiles}.
	 * @param string the source directory
	 * @param string the target directory
	 * @param string base directory
	 * @return array the file list (see {@link copyFiles})
	 */
	public function buildFileList($sourceDir, $targetDir, $baseDir='')
	{
		$list=array();
		$handle=opendir($sourceDir);
		while($file=readdir($handle))
		{
			if($file==='.' || $file==='..' || $file==='.svn' ||$file==='.yii')
				continue;
			$sourcePath=$sourceDir.DIRECTORY_SEPARATOR.$file;
			$targetPath=$targetDir.DIRECTORY_SEPARATOR.$file;
			$name=$baseDir===''?$file : $baseDir.'/'.$file;
			$list[$name]=array('source'=>$sourcePath, 'target'=>$targetPath);
			if(is_dir($sourcePath))
				$list=array_merge($list,$this->buildFileList($sourcePath,$targetPath,$name));
		}
		closedir($handle);
		return $list;
	}

	/**
	 * Creates all parent directories if they do not exist.
	 * @param string the directory to be checked
	 */
	public function ensureDirectory($directory)
	{
		if(!is_dir($directory))
		{
			$this->ensureDirectory(dirname($directory));
			echo "      mkdir ".strtr($directory,'\\','/')."\n";
			mkdir($directory);
		}
	}

	/**
	 * Renders a view file.
	 * @param string view file path
	 * @param array optional data to be extracted as local view variables
	 * @param boolean whether to return the rendering result instead of displaying it
	 * @return mixed the rendering result if required. Null otherwise.
	 */
	public function renderFile($_viewFile_,$_data_=null,$_return_=false)
	{
		if(is_array($_data_))
			extract($_data_,EXTR_PREFIX_SAME,'data');
		else
			$data=$_data_;
		if($_return_)
		{
			ob_start();
			ob_implicit_flush(false);
			require($_viewFile_);
			return ob_get_clean();
		}
		else
			require($_viewFile_);
	}
}