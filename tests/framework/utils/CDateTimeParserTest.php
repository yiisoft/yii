<?php
class CDateTimeParserTest extends CTestCase
{
	function testParseDefaults()
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

	function testShortMonthTitle()
	{
		$this->assertEquals(
			'21 Sep, 2011, 13:37',
			date('d M, Y, H:i', CDateTimeParser::parse('21 Sep, 2011, 13:37', 'dd MMM, yyyy, HH:mm'))
		);
		$this->assertEquals(
			'05, 1991, 01:09, Mar',
			date('d, Y, H:i, M', CDateTimeParser::parse('05, 1991, 01:09, Mar', 'dd, yyyy, HH:mm, MMM'))
		);
		$this->assertEquals(
			'Dec 01, 1971, 23:59',
			date('M d, Y, H:i', CDateTimeParser::parse('Dec 01, 1971, 23:59', 'MMM dd, yyyy, HH:mm'))
		);
	}
}
