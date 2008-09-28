<?php

// change the following paths if necessary
$yiiFramework='{YiiPath}';
$configFile=dirname(__FILE__).'/protected/config/main.php';

// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once($yiiFramework);
Yii::createWebApplication($configFile)->run();
