<?php
define('YII_DEBUG',true);
$webRoot=dirname(__FILE__);
require_once(dirname($webRoot) . '/framework/yee.php');
$configFile=$webRoot.'/viewer/config/main.php';
Yee::createWebApplication($configFile)->run();