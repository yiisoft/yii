<?php

Yii::import('system.i18n.CDateFormatter');

class CDateFormatterTest extends CTestCase
{
	public function setUp()
	{
	}

	public function tearDown()
	{
	}

	public function testConstruct()
	{
		$formatter=new CDateFormatter('zh');
		file_put_contents('test.txt',$formatter->formatDateTime(time(),'full'));
	}
}
