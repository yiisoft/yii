<?php

class CLocalizedFormatterTest extends CTestCase
{
	public function tearDown()
	{
		parent::tearDown();
		Yii::app()->language=null; // reset language to not affect other tests
	}

	/**
	 * Test boolean format translation
	 */
	public function testBooleanFormat()
	{
		Yii::app()->setComponent('format', new CLocalizedFormatter());

		$this->assertEquals('Yes', Yii::app()->format->boolean(true));
		$this->assertEquals('No', Yii::app()->format->boolean(false));

		Yii::app()->setComponent('format', new CLocalizedFormatter());
		Yii::app()->setLanguage('de');

		$this->assertEquals('Ja', Yii::app()->format->boolean(true));
		$this->assertEquals('Nein', Yii::app()->format->boolean(false));

		Yii::app()->setComponent('format', new CLocalizedFormatter());
		Yii::app()->setLanguage('en_US');

		$this->assertEquals('Yes', Yii::app()->format->boolean(true));
		$this->assertEquals('No', Yii::app()->format->boolean(false));
	}
}
