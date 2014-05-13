<?php
define('YII_DEBUG',true);
$webRoot=dirname(__FILE__);
require_once(dirname($webRoot) . '/framework/yii.php');
$configFile=$webRoot.'/viewer/config/main.php';
Yii::createWebApplication($configFile)->run();