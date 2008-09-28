<?php

define('YII_ENABLE_AUTOLOAD',false);
define('YII_ENABLE_EXCEPTION_HANDLER',false);
define('YII_ENABLE_ERROR_HANDLER',false);

require_once(dirname(__FILE__).'/../../framework/YiiBase.php');

class Yii extends YiiBase
{
	private static $_testApp;

	public static function app()
	{
		return self::$_testApp;
	}

	public static function setApplication($app)
	{
		self::$_testApp=$app;
	}

	public static function setPathOfAlias($alias,$path)
	{
		if(self::getPathOfAlias($alias)===null)
			parent::setPathOfAlias($alias,$path);
	}
}

require_once(dirname(__FILE__).'/TestWebApplication.php');
require_once(dirname(__FILE__).'/TestApplication.php');