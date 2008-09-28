<?php

require_once(dirname(__FILE__).'/cut.php');

$testName='Yii Framework Unit Tests';
$testPath=dirname(__FILE__).DIRECTORY_SEPARATOR.'framework';

$tester=new YiiUnitTester($testPath,$testName);

$tester->run($argv);