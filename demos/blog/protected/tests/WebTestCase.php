<?php

/**
 * Change the following URL based on your server configuration
 * Make sure the URL ends with a slash so that we can use relative URLs in test cases
 */
define('TEST_BASE_URL','http://localhost/yii/demos/blog/index-test.php/');

/**
 * The base class for functional test cases.
 * In this class, we set the base URL for the test application.
 * We also provide some common methods to be used by concrete test classes.
 */
class WebTestCase extends CWebTestCase
{
	/**
	 * Sets up before each test method runs.
	 * This mainly sets the base URL for the test application.
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->setBrowserUrl(TEST_BASE_URL);
	}

	/**
	 * Logs in a user
	 */
	protected function login()
	{
		if($this->isElementPresent('name=LoginForm[username]'))
		{
			$this->type('name=LoginForm[username]','demo');
			$this->type('name=LoginForm[password]','demo');
			$this->clickAndWait("//input[@value='Login']");
		}
	}

	/**
	 * Logs out a user
	 */
	protected function logout()
	{
		if($this->isElementPresent("link=Logout"))
			$this->clickAndWait("link=Logout");
	}

	/**
	 * Ensures the user is logged out
	 */
	protected function ensureLogout()
	{
		$this->open('');
		$this->logout();
	}
}
