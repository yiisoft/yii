<?php
/**
 * ShellCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 */

/**
 * ShellCommand executes the specified Web application and provides a shell for interaction.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.cli.commands
 * @since 1.0
 */
class ShellCommand extends CConsoleCommand
{
	public function getHelp()
	{
		return <<<EOD
USAGE
  yiic shell [app-entry-script]

DESCRIPTION
  This command allows you to interact with a Web application
  on the command line. It provides tools to automatically
  generate new controllers, views and data models.

PARAMETERS
 * app-entry-script: optional, the path to the entry script file
   of the Web application. If not given, it is assumed to be
  'index.php' under the current directory.

EOD;
	}

	/**
	 * Execute the action.
	 * @param array command line parameters specific for this command
	 */
	public function run($args)
	{
		if(!isset($args[0]))
			$args[0]='index.php';
		$entryScript=isset($args[0]) ? $args[0] : 'index.php';
		if(($entryScript=realpath($args[0]))===false || !is_file($entryScript))
			$this->usageError("{$args[0]} does not exist or is not an entry script file.");

		// fake the web server setting
		chdir(dirname($entryScript));
		$_SERVER['SCRIPT_NAME']='/'.basename($entryScript);
		$_SERVER['SCRIPT_FILENAME']=$entryScript;
		$_SERVER['HTTP_HOST']='localhost';
		$_SERVER['SERVER_NAME']='localhost';
		$_SERVER['SERVER_PORT']=80;

		// reset context to run the web application
		restore_error_handler();
		restore_exception_handler();
		Yii::setApplication(null);
		Yii::setPathOfAlias('application',null);

		ob_start();
		require($entryScript);
		ob_end_clean();

		echo <<<EOD
Yii Interactive Tool v1.0
Please type 'help' for help. Type 'exit' to quit.
EOD;
		$this->runShell();
	}

	protected function runShell()
	{
		$_runner_=new CConsoleCommandRunner;
		$_runner_->addCommands(dirname(__FILE__).'/shell');
		$_runner_->addCommands(Yii::getPathOfAlias('application.commands.shell'));
		$_commands_=$_runner_->commands;
		while(true)
		{
			echo "\n>> ";
			$_line_=trim(fgets(STDIN));
			try
			{
				$_args_=preg_split('/[\s,]+/',rtrim($_line_,';'),-1,PREG_SPLIT_NO_EMPTY);
				if(isset($_args_[0]) && isset($_commands_[$_args_[0]]))
				{
					$_command_=$_runner_->createCommand($_args_[0]);
					array_shift($_args_);
					$_command_->run($_args_);
				}
				else
					echo eval($_line_.';');
			}
			catch(Exception $e)
			{
				if($e instanceof ShellException)
					echo $e->getMessage();
				else
					echo $e;
			}
		}
	}
}

class ShellException extends CException
{
}