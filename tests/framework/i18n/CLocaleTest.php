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

	public function providerGetLocaleDisplayName()
	{
		return array(
			array('de','en_US','amerikanisches englisch'),
			array('de','en','englisch'),
			array('de_DE','en_US','amerikanisches englisch'),
			array('de_DE','en','englisch'),

			array('es_MX',null,null),
			array('es_ES',null,null),

			// https://github.com/yiisoft/yii/issues/2087
			array('en_us','en','english'),
			array('en_us','en_us','u.s. english'),
			array('en_us','pt','portuguese'),
			array('en_us','pt','portuguese'),
			array('en_us','pt_br','brazilian portuguese'),
			array('en_us','pt_pt','european portuguese'),
		);
	}

	/**
	 * @dataProvider providerGetLocaleDisplayName
	 */
	public function testGetLocaleDisplayName($ctorLocale,$methodLocale,$assertion)
	{
		$locale=CLocale::getInstance($ctorLocale);
		$this->assertEquals(mb_strtolower($assertion),mb_strtolower($locale->getLocaleDisplayName($methodLocale)));
	}

	public function providerGetLanguage()
	{
		return array(
			array('en','fr_FR','french'),
			array('en','fr','french'),
			array('en_US','fr_FR','french'),
			array('en_US','fr','french'),
			array('ru','de_DE','немецкий'),
			array('ru','de','немецкий'),
			array('ru_RU','de_DE','немецкий'),
			array('ru_RU','de','немецкий'),
			array('de','en_US','englisch'),
			array('de','en','englisch'),
			array('de','US',null),
			array('de_DE','en_US','englisch'),
			array('de_DE','en','englisch'),
			array('de_DE','US',null),

			array('es_MX',null,null),
			array('es_ES',null,null),

			array('ru_RU','zh-Hans-CN','китайский'),
			array('en_US','zh-Hans-CN','chinese'),
			array('ru_RU','zh-Hant-HK','китайский'),
			array('en_US','zh-Hant-HK','chinese'),
			array('ru','zh-Hant-HK','китайский'),
			array('en','zh-Hant-HK','chinese'),
			array('ru','CN',null),
			array('en','CN',null),
			array('ru','Hant',null),
			array('en','Hant',null),

			// https://github.com/yiisoft/yii/issues/2087
			array('en_us','en','English'),
			array('en_us','en_us','English'),
			array('en_us','us',null),
			array('en_us','pt','Portuguese'),
			array('en_us','pt','Portuguese'),
			array('en_us','pt_br','Portuguese'),
			array('en_us','br','Breton'),
			array('en_us','pt_pt','Portuguese'),
		);
	}

	/**
	 * @dataProvider providerGetLanguage
	 */
	public function testGetLanguage($ctorLocale,$methodLocale,$assertion)
	{
		$locale=CLocale::getInstance($ctorLocale);
		$this->assertEquals(mb_strtolower($assertion),mb_strtolower($locale->getLanguage($methodLocale)));
	}

	public function providerGetScript()
	{
		return array(
			array('en','fr_FR',null),
			array('en','fr',null),
			array('en_US','fr_FR',null),
			array('en_US','fr',null),
			array('ru','de_DE',null),
			array('ru','de',null),
			array('ru_RU','de_DE',null),
			array('ru_RU','de',null),
			array('de','en_US',null),
			array('de','en',null),
			array('de','US',null),
			array('de_DE','en_US',null),
			array('de_DE','en',null),
			array('de_DE','US',null),

			array('es_MX',null,null),
			array('es_ES',null,null),

			array('ru_RU','zh-Hans-CN','Упрощенный китайский'),
			array('en_US','zh-Hans-CN','Simplified Han'),
			array('ru_RU','zh-Hant-HK','Традиционный китайский'),
			array('en_US','zh-Hant-HK','Traditional Han'),
			array('ru','zh-Hant-HK','Традиционный китайский'),
			array('en','zh-Hant-HK','Traditional Han'),
			array('en','zh-CN',null),
			array('en','zh-HK',null),
		);
	}

	/**
	 * @dataProvider providerGetScript
	 */
	public function testGetScript($ctorLocale,$methodLocale,$assertion)
	{
		$locale=CLocale::getInstance($ctorLocale);
		$this->assertEquals($assertion,$locale->getScript($methodLocale));
	}

	public function providerGetTerritory()
	{
		return array(
			array('en','fr_FR','France'),
			array('en','fr','France'),
			array('en_US','fr_FR','France'),
			array('en_US','fr','France'),
			array('ru','de_DE','Германия'),
			array('ru','de','Германия'),
			array('ru_RU','de_DE','Германия'),
			array('ru_RU','de','Германия'),
			array('de','en_US','Vereinigte Staaten'),
			array('de','en',null),
			array('de_DE','en_US','Vereinigte Staaten'),
			array('de_DE','en',null),

			array('es_MX',null,null),
			array('es_ES',null,null),

			array('ru_RU','zh-Hans-CN','Китай'),
			array('en_US','zh-Hans-CN','China'),
			array('ru_RU','zh-CN','Китай'),
			array('en_US','zh-CN','China'),
			array('ru_RU','Hans-CN','Китай'),
			array('en_US','Hans-CN','China'),
			array('ru_RU','CN','Китай'),
			array('en_US','CN','China'),
			array('ru_RU','zh',null),
			array('en_US','zh',null),
			array('ru_RU','Hans',null),
			array('en_US','Hans',null),

			array('fi_fi','se','Ruotsi'),
			array('fi_fi','sv_se','Ruotsi'),
			array('fi_fi','sv','El Salvador'),
		);
	}

	/**
	 * @dataProvider providerGetTerritory
	 */
	public function testGetTerritory($ctorLocale,$methodLocale,$assertion)
	{
		$locale=CLocale::getInstance($ctorLocale);
		$this->assertEquals($assertion,$locale->getTerritory($methodLocale));
	}
}
