<?php

$yee=dirname(__FILE__).'/../../framework/yee.php';
$config=dirname(__FILE__).'/protected/config/main.php';

require_once($yee);
Yee::createWebApplication($config)->run();