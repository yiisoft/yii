<?php

// remove the following line if running in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);

// include Yii bootstrap file
require_once(dirname(__FILE__).'/../../framework/yii.php');

// create a Web application instance and run
Yii::createWebApplication()->run();