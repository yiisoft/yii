<?php

class CDateTimeParserTest extends CTestCase
{
	public function testAllPatterns()
	{
		$this->assertEquals(
			'02 Aug, 2010, 05:09:07',
			date('d M, Y, H:i:s', CDateTimeParser::parse('XX, 2.8.10, 5:9:7 AM', '??, d.M.yy, h:m:s a'))
		);
		$this->assertEquals(
			'02 Aug, 2010, 05:09:07',
			date('d M, Y, H:i:s', CDateTimeParser::parse('02/08/2010, yyy, 05:9:7', 'dd/MM/yyyy, ???, hh:m:s'))
		);
		$this->assertEquals(
			'02 Aug, 2010, 05:09:07',
			date('d M, Y, H:i:s', CDateTimeParser::parse('2\AUG\2010, 5:09:07 Am, ZzZ', 'd\MMM\yyyy, H:mm:ss a, ???'))
		);
		$this->assertEquals(
			'02 Aug, 2010, 05:09:07',
			date('d M, Y, H:i:s', CDateTimeParser::parse('02_augUST|10, W, 05-09-07', 'dd_MMMM|yy, ?, HH-mm-ss'))
		);
	}

	public function testParseDefaults()
	{
		$this->assertEquals(
			'31-12-2011 23:59:59',
			date('d-m-Y H:i:s', CDateTimeParser::parse('2011-12-31', 'yyyy-MM-dd', array('hour' => 23, 'minute' => 59, 'second' => 59)))
		);
		// test matching with wildcards, this example is mssql timestamp
		$this->assertEquals(
			'2011-02-10 23:43:04',
			date('Y-m-d H:i:s', CDateTimeParser::parse('2011-02-10 23:43:04.973', 'yyyy-MM-dd hh:mm:ss.???'))
		);
		$this->assertEquals(
			'2011-01-10 23:43:04',
			date('Y-m-d H:i:s', CDateTimeParser::parse('2011-01-10 23:43:04.973', 'yyyy?MM?dd?hh?mm?ss????'))
		);
	}

	public function testShortMonthTitle()
	{
		$this->assertEquals(
			'21 Sep, 2011, 13:37',
			date('d M, Y, H:i', CDateTimeParser::parse('21 SEP, 2011, 13:37', 'dd MMM, yyyy, HH:mm'))
		);
		$this->assertEquals(
			'05, 1991, 01:09, Mar',
			date('d, Y, H:i, M', CDateTimeParser::parse('05, 1991, 01:09, mar', 'dd, yyyy, HH:mm, MMM'))
		);
		$this->assertEquals(
			'Dec 01, 1971, 23:59',
			date('M d, Y, H:i', CDateTimeParser::parse('Dec 01, 1971, 23:59', 'MMM dd, yyyy, HH:mm'))
		);
	}

	public function testMonthTitle()
	{
		$this->assertEquals(
			'21 Sep, 2011, 13:37',
			date('d M, Y, H:i', CDateTimeParser::parse('21 September, 2011, 13:37', 'dd MMMM, yyyy, HH:mm'))
		);
		$this->assertEquals(
			'05, 1991, 01:09, Mar',
			date('d, Y, H:i, M', CDateTimeParser::parse('05, 1991, 01:09, march', 'dd, yyyy, HH:mm, MMMM'))
		);
		$this->assertEquals(
			'Dec 01, 1971, 23:59',
			date('M d, Y, H:i', CDateTimeParser::parse('DECEMBER 01, 1971, 23:59', 'MMMM dd, yyyy, HH:mm'))
		);
	}

	public function testLocaleShortMonthTitle()
	{
		mb_internal_encoding('UTF-8');
		mb_regex_encoding('UTF-8');

		// remember active application language and locale
		$oldLanguage=Yii::app()->getLanguage();

		// ru_RU.UTF-8
		Yii::app()->setLanguage('ru_RU');
		$this->assertEquals(
			'21 Sep, 2011, 13:37',
			date('d M, Y, H:i', CDateTimeParser::parse('21 СЕНТ, 2011, 13:37', 'dd MMM, yyyy, HH:mm'))
		);
		$this->assertEquals(
			'05, 1991, 01:09, Mar',
			date('d, Y, H:i, M', CDateTimeParser::parse('05, 1991, 01:09, март', 'dd, yyyy, HH:mm, MMM'))
		);
		$this->assertEquals(
			'Dec 01, 1971, 23:59',
			date('M d, Y, H:i', CDateTimeParser::parse('Дек 01, 1971, 23:59', 'MMM dd, yyyy, HH:mm'))
		);

		// de_DE.UTF-8
		Yii::app()->setLanguage('de_DE');
		$this->assertEquals(
			'21 Sep, 2011, 13:37',
			date('d M, Y, H:i', CDateTimeParser::parse('21 sep, 2011, 13:37', 'dd MMM, yyyy, HH:mm'))
		);
		$this->assertEquals(
			'05, 1991, 01:09, Mar',
			date('d, Y, H:i, M', CDateTimeParser::parse('05, 1991, 01:09, mär', 'dd, yyyy, HH:mm, MMM'))
		);
		$this->assertEquals(
			'Dec 01, 1971, 23:59',
			date('M d, Y, H:i', CDateTimeParser::parse('Dez 01, 1971, 23:59', 'MMM dd, yyyy, HH:mm'))
		);

		// zh_CN.UTF-8
		Yii::app()->setLanguage('zh_CN');
		$this->assertEquals(
			'21 Sep, 2011, 13:37',
			date('d M, Y, H:i', CDateTimeParser::parse('21 九月, 2011, 13:37', 'dd MMM, yyyy, HH:mm'))
		);
		$this->assertEquals(
			'05, 1991, 01:09, Mar',
			date('d, Y, H:i, M', CDateTimeParser::parse('05, 1991, 01:09, 三月', 'dd, yyyy, HH:mm, MMM'))
		);
		$this->assertEquals(
			'Dec 01, 1971, 23:59',
			date('M d, Y, H:i', CDateTimeParser::parse('十二月 01, 1971, 23:59', 'MMM dd, yyyy, HH:mm'))
		);

		// reestablish old active language and locale
		Yii::app()->setLanguage($oldLanguage);
	}

	public function testLocaleMonthTitle()
	{
		mb_internal_encoding('UTF-8');
		mb_regex_encoding('UTF-8');

		// remember active application language and locale
		$oldLanguage=Yii::app()->getLanguage();

		// ru_RU.UTF-8
		Yii::app()->setLanguage('ru_RU');
		$this->assertEquals(
			'21 Sep, 2011, 13:37',
			date('d M, Y, H:i', CDateTimeParser::parse('21 СЕНТЯБРЯ, 2011, 13:37', 'dd MMMM, yyyy, HH:mm'))
		);
		$this->assertEquals(
			'05, 1991, 01:09, Mar',
			date('d, Y, H:i, M', CDateTimeParser::parse('05, 1991, 01:09, март', 'dd, yyyy, HH:mm, MMMM'))
		);
		$this->assertEquals(
			'Dec 01, 1971, 23:59',
			date('M d, Y, H:i', CDateTimeParser::parse('Декабря 01, 1971, 23:59', 'MMMM dd, yyyy, HH:mm'))
		);

		// de_DE.UTF-8
		Yii::app()->setLanguage('de_DE');
		$this->assertEquals(
			'21 Sep, 2011, 13:37',
			date('d M, Y, H:i', CDateTimeParser::parse('21 SEPTEMBER, 2011, 13:37', 'dd MMMM, yyyy, HH:mm'))
		);
		$this->assertEquals(
			'05, 1991, 01:09, Mar',
			date('d, Y, H:i, M', CDateTimeParser::parse('05, 1991, 01:09, März', 'dd, yyyy, HH:mm, MMMM'))
		);
		$this->assertEquals(
			'Dec 01, 1971, 23:59',
			date('M d, Y, H:i', CDateTimeParser::parse('dezember 01, 1971, 23:59', 'MMMM dd, yyyy, HH:mm'))
		);

		// zh_CN.UTF-8
		Yii::app()->setLanguage('zh_CN');
		$this->assertEquals(
			'21 Sep, 2011, 13:37',
			date('d M, Y, H:i', CDateTimeParser::parse('21 九月, 2011, 13:37', 'dd MMMM, yyyy, HH:mm'))
		);
		$this->assertEquals(
			'05, 1991, 01:09, Mar',
			date('d, Y, H:i, M', CDateTimeParser::parse('05, 1991, 01:09, 三月', 'dd, yyyy, HH:mm, MMMM'))
		);
		$this->assertEquals(
			'Dec 01, 1971, 23:59',
			date('M d, Y, H:i', CDateTimeParser::parse('十二月 01, 1971, 23:59', 'MMMM dd, yyyy, HH:mm'))
		);

		// reestablish old active language and locale
		Yii::app()->setLanguage($oldLanguage);
	}
}
