<?php

namespace site_demo\_root_;

defined('YII_DEBUG') or define('YII_DEBUG', TRUE);

define('__DIR_YII_BASE__',  realpath(__DIR__.'/../../../..').'/framework');
define('__DIR_LIB_BASE__',  realpath(__DIR__.'/../..').'/lib');
define('__DIR_SITE_BASE__', realpath(__DIR__.'/..'));

define('__ENTRY_NAME__', basename(__FILE__, '.php'));

require_once(__DIR_YII_BASE__.'/yii.php');

\Yii::createWebApplication(__DIR_SITE_BASE__.'/entry/'.__ENTRY_NAME__.'/config/config.php')->run();
