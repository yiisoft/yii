<?php
/**
 * ServeCommand class file.
 *
 * @author resurtm <resurtm@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @since 1.1.11
 */

/**
 * @property string $help The help information for the serve command.
 *
 * @author resurtm <resurtm@gmail.com>
 * @package system.cli.commands
 * @since 1.1.11
 */
class ServeCommand extends CConsoleCommand
{
	/**
	 * @return string the help information for the serve command
	 */
	public function getHelp()
	{
		return <<<EOD
USAGE
  yiic serve [address[:port [webroot]]]

DESCRIPTION
  This command starts serving the Yii web application for incoming
  HTTP requests using PHP built-in server.

  PHP built-in server command: $ php -S localhost:8000 -t /home/user/dir
  This command:                $ protected/yiic serve

  Note that you should have PHP version 5.4 or newer to use this command.

PARAMETERS
 * address: optional, listening address. Default value is 'localhost'.
 * port: optional, listening port. Default value is '8000'.
 * webroot: optional, document root path. Default is a current directory.

EOD;
	}

	/**
	 * Execute the action.
	 * @param array $args command line parameters specific for this command
	 */
	public function run($args)
	{
		// minimal PHP version is 5.4.0
		list($major,$minor)=explode('.',PHP_VERSION);
		if($major<5 || $major==5 && $minor<4)
			throw new CException('The minimal PHP version for ServeCommand is 5.4.');

		// extract arguments
		$address=isset($args[0]) ? $args[0] : 'localhost:8000';
		$webroot=isset($args[1]) ? $args[1] : getcwd();

		// append default port if it was not specified
		if(strpos($address,':')===false)
			$address.=':8000';

		echo "Serving at:  {$address}\n";
		echo "Web root is: {$webroot}\n";

		exec(PHP_BINARY." -S {$address} -t {$webroot}");
	}
}
