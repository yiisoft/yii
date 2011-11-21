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
}
