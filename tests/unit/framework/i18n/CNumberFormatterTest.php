<?php

class CNumberFormatterTest extends CTestCase
{
	public $usFormatter;
	public $deFormatter;

	public function setUp()
	{
		$this->usFormatter=new CNumberFormatter('en_us');
		$this->deFormatter=new CNumberFormatter('de');
	}

	public function testFormatCurrency()
	{
		$numbers=array(
			array(0, '$0.00', '0,00 $'),
			array(100, '$100.00', '100,00 $'),
			array(-100, '($100.00)', '-100,00 $'),
			array(100.123, '$100.12', '100,12 $'),
			array(100.1, '$100.10', '100,10 $'),
			array(100.126, '$100.13', '100,13 $'),
			array(1000.126, '$1,000.13', '1.000,13 $'),
			array(1000000.123, '$1,000,000.12', '1.000.000,12 $'),
		);

		foreach($numbers as $number)
		{
			$this->assertEquals($number[1],$this->usFormatter->formatCurrency($number[0],'USD'));
			$this->assertEquals($number[2],$this->deFormatter->formatCurrency($number[0],'USD'));
		}
	}

	public function testFormatDecimal()
	{
		$numbers=array(
			array(0, '0', '0'),
			array(100, '100', '100'),
			array(-100, '-100', '-100'),
			array(100.123, '100.123', '100,123'),
			array(100.1, '100.1', '100,1'),
			array(100.1206, '100.121', '100,121'),
			array(1000.1206, '1,000.121', '1.000,121'),
			array(1000000.123, '1,000,000.123', '1.000.000,123'),
		);

		foreach($numbers as $number)
		{
			$this->assertEquals($number[1],$this->usFormatter->formatDecimal($number[0]));
			$this->assertEquals($number[2],$this->deFormatter->formatDecimal($number[0]));
		}
	}

	public function testFormatPercentage()
	{
		$numbers=array(
			array(0, '0%', '0 %'),
			array(0.123, '12%', '12 %'),
			array(-0.123, '-12%', '-12 %'),
			array(10.12, '1,012%', '1.012 %'),
			array(10000.1, '1,000,010%', '1.000.010 %'),
		);

		foreach($numbers as $number)
		{
			$this->assertEquals($number[1],$this->usFormatter->formatPercentage($number[0]));
			$this->assertEquals($number[2],$this->deFormatter->formatPercentage($number[0]));
		}
	}
}
