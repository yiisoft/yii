<?php

defined('YII_ENABLE_EXCEPTION_HANDLER') or define('YII_ENABLE_EXCEPTION_HANDLER', false);
defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', false);
defined('YII_DEBUG') or define('YII_DEBUG', true);
$_SERVER['SCRIPT_NAME'] = '/' . basename(__FILE__);
$_SERVER['SCRIPT_FILENAME'] = __FILE__;

if (PHP_VERSION_ID >= 50300) {
    require_once(__DIR__ . '/compatibility.php');
}

require_once(__DIR__ . '/../framework/yii.php');
require_once(__DIR__ . '/TestApplication.php');
// Support PHPUnit <=3.7 and >=3.8
if (@include ('PHPUnit/Framework/TestCase.php') === false) { // <= 3.7
    require_once('src/Framework/TestCase.php');
} // >= 3.8

// make sure non existing PHPUnit classes do not break with Yii autoloader
Yii::$enableIncludePath = false;
Yii::setPathOfAlias('tests', __DIR__);
Yii::import('tests.*');

class CTestCase extends PHPUnit_Framework_TestCase
{
}


class CActiveRecordTestCase extends CTestCase
{
}

new TestApplication();
