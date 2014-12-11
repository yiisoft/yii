<?php

defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER',false);
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER',false);
defined('YII_DEBUG') or define('YII_DEBUG',true);
$_SERVER['SCRIPT_NAME']='/'.basename(__FILE__);
$_SERVER['SCRIPT_FILENAME']=__FILE__;

require_once(dirname(__FILE__).'/../framework/yii.php');
require_once(dirname(__FILE__).'/TestApplication.php');
// Support PHPUnit <=3.7 and >=3.8
if (@include_once('PHPUnit/Framework/TestCase.php')===false) // <= 3.7
	require_once('src/Framework/TestCase.php'); // >= 3.8

// make sure non existing PHPUnit classes do not break with Yii autoloader
Yii::$enableIncludePath = false;
Yii::setPathOfAlias('tests', dirname(__FILE__));
Yii::import('tests.*');

class CTestCase extends PHPUnit_Framework_TestCase
{
}


class CActiveRecordTestCase extends CTestCase
{
}

new TestApplication();
