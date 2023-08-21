<?php
define('YII_DEBUG',true);
$webRoot= __DIR__;
require_once dirname($webRoot) . '/framework/yii.php';
$configFile=$webRoot.'/viewer/config/main.php';
Yii::createWebApplication($configFile)->run();
