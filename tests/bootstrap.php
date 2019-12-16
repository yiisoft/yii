<?php

require __DIR__ . '/../vendor/autoload.php';

if (PHP_MAJOR_VERSION>=7 && PHP_MINOR_VERSION>=1) {
	// skip deprecation errors in PHP 7.1 and above
	error_reporting(E_ALL & ~E_DEPRECATED);
}

defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER',false);
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER',false);
defined('YII_DEBUG') or define('YII_DEBUG',true);
$_SERVER['SCRIPT_NAME']='/'.basename(__FILE__);
$_SERVER['SCRIPT_FILENAME']=__FILE__;

// make sure non existing PHPUnit classes do not break with Yii autoloader
Yii::$enableIncludePath = false;

new TestApplication();
