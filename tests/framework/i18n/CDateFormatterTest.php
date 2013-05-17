<?php
/**
 * CDateFormatterTest
 */
class CDateFormatterTest extends CTestCase
{
	public function testWeekInMonth()
	{
		for($year=1970; $year<=date('Y'); $year++)
		{
			for($month=1;$month<=12;$month++) {
				$day=date('t',mktime(0,0,0,$month,1,$year));
				$weekNum=Yii::app()->dateFormatter->format("W",mktime(0,0,0,$month,$day,$year));
				//echo sprintf("%d/%d/%d\t%d\n",$year,$month,$day,$weekNum);
			}
		}

		//echo "Week number for 2011/01/01 is ".Yii::app()->dateFormatter->format("W",mktime(0,0,0,1,1,2011));
		//echo "Week number for 2011/01/07 is ".Yii::app()->dateFormatter->format("W",mktime(0,0,0,1,7,2011));
	}

	public function testStringIntegerDate()
	{
		date_default_timezone_set('UTC');
		$this->assertEquals('2012 09 03 07:54:09', Yii::app()->dateFormatter->format("yyyy MM dd hh:mm:ss", 1346702049));
		$this->assertEquals('2012 09 03 07:54:09', Yii::app()->dateFormatter->format("yyyy MM dd hh:mm:ss", '1346702049'));
		$this->assertEquals('1927 04 30 04:05:51', Yii::app()->dateFormatter->format("yyyy MM dd hh:mm:ss", -1346702049));
		$this->assertEquals('1927 04 30 04:05:51', Yii::app()->dateFormatter->format("yyyy MM dd hh:mm:ss", '-1346702049'));
	}

	public function testStrToTimeDate()
	{
		date_default_timezone_set('UTC');
		$this->assertEquals('2012 09 03 09:54:09', Yii::app()->dateFormatter->format("yyyy MM dd hh:mm:ss", '2012-09-03 09:54:09 UTC'));
		$this->assertEquals('1927 04 30 05:05:51', Yii::app()->dateFormatter->format("yyyy MM dd hh:mm:ss", '1927-04-30 05:05:51 UTC'));
	}

}
