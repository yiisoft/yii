<?php

// Change the following URL based on your server configuration
// Make sure the URL ends with a slash so that we can use relative URLs in test cases
define('TEST_BASE_URL','http://localhost/yii/demos/blog/index-test.php/');

// Change the following paths if necessary
$yiit=dirname(__FILE__).'/../../../../../framework/yiit.php';
$config=dirname(__FILE__).'/../../config/test.php';

require_once($yiit);

class WebTestCase extends CWebTestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->setBrowserUrl(TEST_BASE_URL);
	}
}

Yii::createWebApplication($config);
