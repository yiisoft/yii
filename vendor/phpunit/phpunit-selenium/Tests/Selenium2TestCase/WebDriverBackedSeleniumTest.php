<?php
class Tests_Selenium2TestCase_WebDriverBackedSeleniumTest extends PHPUnit_Extensions_SeleniumTestCase
{
    public function setUp()
    {
        $this->setHost(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_HOST);
        $this->setPort((int)PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_PORT);
        $this->setBrowser('*webdriver');
        if (!defined('PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL')) {
            $this->markTestSkipped("You must serve the selenium-1-tests folder from an HTTP server and configure the PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL constant accordingly.");
        }
        $this->setBrowserUrl(PHPUNIT_TESTSUITE_EXTENSION_SELENIUM_TESTS_URL);
        $this->setWebDriverCapabilities(array(
            'browserName' => PHPUNIT_TESTSUITE_EXTENSION_SELENIUM2_BROWSER
        ));
    }

    public function testAPageIsOpenedWithWebDriver()
    {
        $this->markTestIncomplete('Crashes the opened browser deterministically.');
        try {
            $this->open('html/test_open.html');
        } catch (Exception $e) {
            sleep(10);
        }
        $this->assertEquals('This is a test of the open command.', $this->getBodyText());
    }
}
