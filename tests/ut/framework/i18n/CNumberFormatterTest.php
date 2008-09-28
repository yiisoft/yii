<?php

Yii::import('system.i18n.CNumberFormatter');

class CNumberFormatterTest extends CTestCase
{
	public function setUp()
	{
	}

	public function tearDown()
	{
	}

	public function testConstruct()
	{
		$formatter=new CNumberFormatter('zh');
	}
}
