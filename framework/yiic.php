<?php
/**
 * Yii command line script file.
 *
 * This script is meant to be run on command line to execute
 * one of the pre-defined console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 */

// fix for fcgi
defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

defined('YII_DEBUG') or define('YII_DEBUG',true);
error_reporting(E_ALL ^ E_NOTICE);

require_once(dirname(__FILE__).'/yii.php');

if(isset($config))
{
	$app=Yii::createConsoleApplication($config);
	$app->commandRunner->addCommands(YII_PATH.'/cli/commands');
}
else
	$app=Yii::createConsoleApplication(array('basePath'=>dirname(__FILE__).'/cli'));

$app->run();