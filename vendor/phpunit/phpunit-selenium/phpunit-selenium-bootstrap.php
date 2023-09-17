<?php
require_once __DIR__ . '/vendor/autoload.php';

PHPUnit_Extensions_SeleniumTestCase::shareSession(true);
require_once 'Tests/SeleniumTestCase/BaseTestCase.php';
require_once 'Tests/Selenium2TestCase/BaseTestCase.php';
PHPUnit_Extensions_Selenium2TestCase::shareSession(true);
