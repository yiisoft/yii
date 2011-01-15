<?php
class CLocaleTest extends CTestCase
{
	private $noPlurals = array(
		'az', 'bm', 'fa', 'ig', 'hu', 'ja', 'kde', 'kea', 'ko', 'my', 'ses', 'sg',
		'to', 'tr', 'vi', 'wo', 'yo', 'zh', 'bo', 'dz', 'id', 'jv', 'ka', 'km',
		'kn', 'ms', 'th'
	);

	/**
	 * Codes of locales where official guide translation exists
	 */
	protected $criticalLocaleCodes = array(
		'en',
		'bg',
		'bs',
		'cs',
		'de',
		'el',
		'es',
		'fr',
		'he',
		'hu',
		'id',
		'it',
		'ja',
		'lv',
		'nl',
		'no',
		'pl',
		'pt',
		'ro',
		'ru',
		'sk',
		'sr',
		'sr_yu',
		'sv',
		'ta_in',
		'th',
		'tr',
		'uk',
		'vi',
		'zh_cn',
		'zh_tw',
	);

	function setUp()
	{
		$config = array(
			'language' => 'en',
		);

		new TestApplication($config);
		Yii::app()->configure($config);
	}

	function testRequiredDataExistence(){
		foreach($this->criticalLocaleCodes as $localeCode){
			$locale = Yii::app()->getLocale($localeCode);
			// AM/PM
			$this->assertNotNull($locale->getAMName(), "$localeCode: getAMName failed.");
			$this->assertNotNull($locale->getPMName(), "$localeCode: getPMName failed.");

			// currency
			$this->assertNotNull($locale->getCurrencySymbol("USD"), "$localeCode: getCurrencySymbol USD failed.");
			$this->assertNotNull($locale->getCurrencySymbol("EUR"), "$localeCode: getCurrencySymbol EUR failed.");

			// numbers
			$this->assertNotNull($locale->getNumberSymbol('decimal'), "$localeCode: getNumberSymbol failed.");
			$this->assertNotNull($locale->getDecimalFormat(), "$localeCode: getDecimalFormat failed.");
			$this->assertNotNull($locale->getCurrencyFormat(), "$localeCode: getCurrencyFormat failed.");
			$this->assertNotNull($locale->getPercentFormat(), "$localeCode: getPercentFormat failed.");
			$this->assertNotNull($locale->getScientificFormat(), "$localeCode: getScientificFormat failed.");

			// date and time formats
			$this->assertNotNull($locale->getMonthName(1), "$localeCode: getMonthName 1 failed.");
			$this->assertNotNull($locale->getMonthName(12, 'abbreviated'), "$localeCode: getMonthName 12 abbreviated failed.");
			$this->assertNotNull($locale->getMonthName(1, 'narrow', true), "$localeCode: getMonthName 1 narrow standalone failed.");
			$this->assertEquals(12, count($locale->getMonthNames()), "$localeCode: getMonthNames failed.");
			$this->assertNotNull($locale->getWeekDayName(0), "$localeCode: getWeekDayName failed.");
			$this->assertNotNull($locale->getWeekDayNames(), "$localeCode: getWeekDayNames failed.");
			$this->assertNotNull($locale->getEraName(1), "$localeCode: getEraName failed.");
			$this->assertNotNull($locale->getDateFormat(), "$localeCode: getDateFormat failed.");
			$this->assertNotNull($locale->getTimeFormat(), "$localeCode: getTimeFormat failed.");
			$this->assertNotNull($locale->getDateTimeFormat(), "$localeCode: getDateTimeFormat failed.");

			// ORIENTATION
			$this->assertTrue(in_array($locale->getOrientation(), array('ltr', 'rtl')), "$localeCode: getOrientation failed.");

			// plurals
			$l = explode('_', $localeCode);
			if(!in_array($l[0], $this->noPlurals)){
				$pluralRules = $locale->getPluralRules();
				$this->assertNotEmpty($pluralRules, $localeCode.": no plural rules");
			}
		}
	}
}
