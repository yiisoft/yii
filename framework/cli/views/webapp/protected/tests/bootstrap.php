<?php

// change the following paths if necessary
$yiit=dirname(__FILE__).'/../../../framework/yiit.php';
$config=dirname(__FILE__).'/../config/test.php';

require_once($yiit);

Yii::createWebApplication($config);
