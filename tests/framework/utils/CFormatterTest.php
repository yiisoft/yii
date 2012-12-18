<?php

class CFormatterTest extends CTestCase
{
	public function tearDown()
	{
		parent::tearDown();
		Yii::app()->language=null; // reset language to not affect other tests
	}

	/**
	 * Test formatting size numbers in bytes, kilobytes, ...
	 */
	public function testSizeFormat()
	{
		$formatter = new CFormatter();

		Yii::app()->language = 'en';
		$this->assertEquals('1 byte', $formatter->formatSize(1, true));
		$this->assertEquals('10 bytes', $formatter->formatSize(10, true));
		$this->assertEquals('1 kilobyte', $formatter->formatSize(1024, true));
		$this->assertEquals('1.5 kilobytes', $formatter->formatSize(1536, true));
		$this->assertEquals('1.51 kilobytes', $formatter->formatSize(1550, true));
		$formatter->sizeFormat['decimals']=3;
		$this->assertEquals('1.514 kilobytes', $formatter->formatSize(1550, true));
		$formatter->sizeFormat['decimals']=4;
		$this->assertEquals('1.5137 kilobytes', $formatter->formatSize(1550, true));

		$this->assertEquals('1 B', $formatter->formatSize(1, false));
		$this->assertEquals('10 B', $formatter->formatSize(10, false));
		$this->assertEquals('1 KB', $formatter->formatSize(1024, false));
		$this->assertEquals('1.5 KB', $formatter->formatSize(1536, false));

		$this->assertEquals('1 byte', $formatter->formatSize(1, true));
		$this->assertEquals('1 kilobyte', $formatter->formatSize(1024, true));
		$this->assertEquals('1 megabyte', $formatter->formatSize(1024 * 1024, true));
		$this->assertEquals('1 gigabyte', $formatter->formatSize(1024 * 1024 * 1024, true));
		$this->assertEquals('1 terabyte', $formatter->formatSize(1024 * 1024 * 1024 * 1024, true));

		Yii::app()->language = 'de';
		$formatter->sizeFormat['decimalSeparator']=',';
		$this->assertEquals('1 Byte', $formatter->formatSize(1, true));
		$this->assertEquals('10 Byte', $formatter->formatSize(10, true));
		$this->assertEquals('1 Kilobyte', $formatter->formatSize(1024, true));
		$this->assertEquals('1,5 Kilobyte', $formatter->formatSize(1536, true));
	}

	public function languages()
	{
		return array(
			array('cs'),
			array('de'),
			array('ja'),
			array('kk'),
			array('ru'),
			array('sk'),
			array('uk'),
		);
	}

	/**
	 * This test is to check whether messages get actually translated to non-english
	 *
	 * @dataProvider languages
	 * @param $language
	 */
	public function testSizeFormatTranslation($language)
	{
		$formatter = new CFormatter();

		Yii::app()->language=$language;

		$this->assertNotEquals('1 byte', $formatter->formatSize(1, true));
		$this->assertNotEquals('1 kilobyte', $formatter->formatSize(1024, true));
		$this->assertNotEquals('1 megabyte', $formatter->formatSize(1024 * 1024, true));
		$this->assertNotEquals('1 gigabyte', $formatter->formatSize(1024 * 1024 * 1024, true));
		$this->assertNotEquals('1 terabyte', $formatter->formatSize(1024 * 1024 * 1024 * 1024, true));

		// test sizeformat works with non integers
		$formatter->sizeFormat['decimals']=4;
		$this->assertNotEquals('1.5137 kilobytes', $formatter->formatSize(1550, true));
	}
}
