<?php
/**
 * HelpCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 */

/**
 * HelpCommand displays help information for commands under yiic shell.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.cli.commands.shell
 * @since 1.0
 */
class HelpCommand extends CConsoleCommand
{
	/**
	 * Execute the action.
	 * @param array command line parameters specific for this command
	 */
	public function run($args)
	{
		$runner=$this->getCommandRunner();
		$commands=$runner->commands;
		if(isset($args[0]))
			$name=strtolower($args[0]);
		if(!isset($args[0]) || !isset($commands[$name]))
		{
			echo <<<EOD
At the prompt, you may enter a PHP statement or one of the following commands:

EOD;
			echo ' - '.implode("\n - ",array_keys($commands));
			echo "\nType 'help <command-name>' for details about a command.\n";
		}
		else
			echo $runner->createCommand($name)->getHelp();
	}

	/**
	 * Provides the command description.
	 * @return string the command description.
	 */
	public function getHelp()
	{
		return <<<EOD
USAGE
  help [command-name]

DESCRIPTION
  Display the help information for the specified command.
  If the command name is not given, all commands will be listed.

PARAMETERS
 * command-name: optional, the name of the command to show help information.

EOD;
	}
}