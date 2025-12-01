<?php

require_once(dirname(__FILE__).'/../../../framework/yii.php');
$configFile=dirname(__FILE__).'/protected/config/main.php';
Yii::createWebApplication($configFile)->run();
