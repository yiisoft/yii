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

	public function providerFormatNtext()
	{
		return array(
			array(
				"<br/>\nline2\n\nline3\n\n\nline4\n\n\n\nline5",
				false,
				false,
				"&lt;br/&gt;<br />\nline2<br />\n<br />\nline3<br />\n<br />\n<br />\nline4<br />\n<br />\n<br />\n<br />\nline5",
			),
			array(
				"<br/>\nline2\n\nline3\n\n\nline4\n\n\n\nline5",
				false,
				true,
				"&lt;br/&gt;<br />\nline2<br />\n<br />\nline3<br />\n<br />\n<br />\nline4<br />\n<br />\n<br />\n<br />\nline5",
			),
			array(
				"<br/>\nline2\n\nline3\n\n\nline4\n\n\n\nline5",
				true,
				false,
				'<p>&lt;br/&gt;</p><p>line2</p><p></p><p>line3</p><p></p><p></p><p>line4</p><p></p><p></p><p></p><p>line5</p>',
			),
			array(
				"<br/>\nline2\n\nline3\n\n\nline4\n\n\n\nline5",
				true,
				true,
				'<p>&lt;br/&gt;</p><p>line2</p><p>line3</p><p>line4</p><p>line5</p>',
			),
		);
	}

	/**
	 * @dataProvider providerFormatNtext
	 * @param string $value
	 * @param string $paragraphs
	 * @param string $removeEmptyParagraphs
	 * @param string $assertion
	 */
	public function testFormatNtext($value, $paragraphs, $removeEmptyParagraphs, $assertion)
	{
		$formatter = new CFormatter();
		$this->assertEquals($assertion, $formatter->formatNtext($value, $paragraphs, $removeEmptyParagraphs));
	}
}
