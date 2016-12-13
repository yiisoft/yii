<?php

// change the following paths if necessary
$yeet=dirname(__FILE__).'/../../../framework/yeet.php';
$config=dirname(__FILE__).'/../config/test.php';

require_once($yeet);
require_once(dirname(__FILE__).'/WebTestCase.php');

Yee::createWebApplication($config);
