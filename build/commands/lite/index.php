<?php

require_once(dirname(__FILE__).'/../../../framework/yee.php');
$configFile=dirname(__FILE__).'/protected/config/main.php';
Yee::createWebApplication($configFile)->run();
