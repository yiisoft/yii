<?php
class CDateTimeParserTest extends CTestCase
{
	function testParseDefaults()
	{
		$this->assertEquals(
			'31-12-2011 23:59:59',
			date('d-m-Y H:i:s', CDateTimeParser::parse('2011-12-31', 'yyyy-MM-dd', array('hour' => 23, 'minute' => 59, 'second' => 59)))
		);
	}
}
