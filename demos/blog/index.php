<?php

// change the following paths if necessary
$yee=dirname(__FILE__).'/../../framework/yee.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// remove the following line when in production mode
// defined('YII_DEBUG') or define('YII_DEBUG',true);

require_once($yee);
Yee::createWebApplication($config)->run();
