<?php

class CLocalizedFormatterTest extends CTestCase
{
	public function tearDown()
	{
		parent::tearDown();
		Yee::app()->language=null; // reset language to not affect other tests
	}

	/**
	 * Test boolean format translation
	 */
	public function testBooleanFormat()
	{
		Yee::app()->setComponent('format', new CLocalizedFormatter());

		$this->assertEquals('Yes', Yee::app()->format->boolean(true));
		$this->assertEquals('No', Yee::app()->format->boolean(false));

		Yee::app()->setComponent('format', new CLocalizedFormatter());
		Yee::app()->setLanguage('de');

		$this->assertEquals('Ja', Yee::app()->format->boolean(true));
		$this->assertEquals('Nein', Yee::app()->format->boolean(false));

		Yee::app()->setComponent('format', new CLocalizedFormatter());
		Yee::app()->setLanguage('en_US');

		$this->assertEquals('Yes', Yee::app()->format->boolean(true));
		$this->assertEquals('No', Yee::app()->format->boolean(false));
	}
}
