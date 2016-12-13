<?php
/**
 * Tests for various usages of Yee::t
 *
 * http://code.google.com/p/yee/issues/detail?id=1875
 * http://code.google.com/p/yee/issues/detail?id=1987
 *
 * http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html
 */
class YeeTTest extends CTestCase
{
	function setUp()
	{
		$config = array(
			'sourceLanguage' => 'es',
			'components' => array(
				'messages' => array(
					'class' => 'CPhpMessageSource',
					'basePath' => dirname(__FILE__).'/data',
					//'forceTranslation' => true,
				),
			),
		);

		new TestApplication($config);
		Yee::app()->configure($config);
	}

	function tearDown()
	{
		Yee::app()->sourceLanguage = 'en_us';
	}

	// Simple: 'msg'
	function testSimple(){
		Yee::app()->setLanguage('ru');
		$this->assertEquals('апельсины', Yee::t('test', 'oranges'));
	}

	function testSimpleSameLanguage(){
		Yee::app()->setLanguage('es');
		$this->assertEquals('no_changes', Yee::t('test', 'no_changes'));
	}

	function testSimplePlaceholders(){
		Yee::app()->setLanguage('ru');
		$this->assertEquals('сумочки caviar', Yee::t('test', '{brand} bags', array('{brand}' => 'caviar')));
		$this->assertEquals('в корзине: 10', Yee::t('test', 'in the cart: {n}', 10));
	}

	function testSimplePlaceholdersSameLanguage(){
		Yee::app()->setLanguage('es');
		$this->assertEquals('10 changes', Yee::t('test', '{n} changes', 10));
	}

	// Plural: 'msg1|msg2|msg3'
	function testPlural(){
		// CLDR
		Yee::app()->setLanguage('ru');

		// array notation
		$this->assertEquals('огурец', Yee::t('test', 'cucumber|cucumbers', array(1)));

		//ru
		$this->assertEquals('огурец', Yee::t('test', 'cucumber|cucumbers', 1));
		$this->assertEquals('огурец', Yee::t('test', 'cucumber|cucumbers', 101));
		$this->assertEquals('огурец', Yee::t('test', 'cucumber|cucumbers', 51));
		$this->assertEquals('огурца', Yee::t('test', 'cucumber|cucumbers', 2));
		$this->assertEquals('огурца', Yee::t('test', 'cucumber|cucumbers', 62));
		$this->assertEquals('огурца', Yee::t('test', 'cucumber|cucumbers', 104));
		$this->assertEquals('огурцов', Yee::t('test', 'cucumber|cucumbers', 5));
		$this->assertEquals('огурцов', Yee::t('test', 'cucumber|cucumbers', 78));
		$this->assertEquals('огурцов', Yee::t('test', 'cucumber|cucumbers', 320));
		$this->assertEquals('огурцов', Yee::t('test', 'cucumber|cucumbers', 0));

		// fractions (you should specify fourh variant to use these in Russian)
		$this->assertEquals('огурца', Yee::t('test', 'cucumber|cucumbers', 1.5));

		// en
		Yee::app()->setLanguage('en');

        $this->assertEquals('cucumber', Yee::t('test', 'cucumber|cucumbers', 1));
        $this->assertEquals('cucumbers', Yee::t('test', 'cucumber|cucumbers', 2));
        $this->assertEquals('cucumbers', Yee::t('test', 'cucumber|cucumbers', 0));

		// short forms
		Yee::app()->setLanguage('ru');

		$this->assertEquals('огурец', Yee::t('test', 'cucumber|cucumbers', 1));

		// explicit params
		$this->assertEquals('огурец', Yee::t('test', 'cucumber|cucumbers', array(0 => 1)));
	}

	function testPluralPlaceholders(){
		Yee::app()->setLanguage('ru');

		$this->assertEquals('1 огурец', Yee::t('test', '{n} cucumber|{n} cucumbers', 1));
		$this->assertEquals('2 огурца', Yee::t('test', '{n} cucumber|{n} cucumbers', 2));
		$this->assertEquals('5 огурцов', Yee::t('test', '{n} cucumber|{n} cucumbers', 5));

		// more placeholders
		$this->assertEquals('+ 5 огурцов', Yee::t('test', '{sign} {n} cucumber|{sign} {n} cucumbers', array(5, '{sign}' => '+')));

		// placeholder swapping
		$this->assertEquals('один огурец', Yee::t('test', '{n} cucumber|{n} cucumbers', array(1, '{n}' => 'один')));
	}

	/**
	 * If there are useless params in translation just ignore them.
	 */
	function testPluralMoreVariants(){
		Yee::app()->setLanguage('ru');
		$this->assertEquals('шляпы', Yee::t('test', 'hat|hats', array(2)));
	}

	/**
	 * If there are less variants in translation like
	 * 'zombie|zombies' => 'зомби' (CLDR requires 3 variants for Russian
	 * but zombie is too special to be plural)
	 *
	 * Same for Chinese but there are no plurals at all.
	 */
	function testPluralLessVariants(){
		// three variants are required and only one specified (still valid for
		// Russian in some special cases)
		Yee::app()->setLanguage('ru');
		$this->assertEquals('зомби', Yee::t('test', 'zombie|zombies', 10));
		$this->assertEquals('зомби', Yee::t('test', 'zombie|zombies', 1));

		// language with no plurals
		Yee::app()->setLanguage('zh_cn');
		$this->assertEquals('k-s', Yee::t('test', 'kiss|kisses', 1));

		// 3 variants are required while only 2 specified
		// this one is synthetic but still good to know it at least does not
		// produce error
		Yee::app()->setLanguage('ru');
		$this->assertEquals('син1', Yee::t('test', 'syn1|syn2|syn3', 1));
		$this->assertEquals('син2', Yee::t('test', 'syn1|syn2|syn3', 2));
		$this->assertEquals('син2', Yee::t('test', 'syn1|syn2|syn3', 5));
	}

	function pluralLessVariantsInSource(){
		// new doesn't have two forms in English
		Yee::app()->setLanguage('ru');
		$this->assertEquals('новости', Yee::t('test', 'news', 2));
	}

	function testPluralSameLanguage(){
		Yee::app()->setLanguage('es');

		$this->assertEquals('cucumbez', Yee::t('test', 'cucumbez|cucumberz', 1));
        $this->assertEquals('cucumberz', Yee::t('test', 'cucumbez|cucumberz', 2));
        $this->assertEquals('cucumberz', Yee::t('test', 'cucumbez|cucumberz', 0));
	}

	function testPluralPlaceholdersSameLanguage(){
		Yee::app()->setLanguage('es');

		$this->assertEquals('1 cucumbez', Yee::t('test', '{n} cucumbez|{n} cucumberz', 1));
		$this->assertEquals('2 cucumberz', Yee::t('test', '{n} cucumbez|{n} cucumberz', 2));
		$this->assertEquals('5 cucumberz', Yee::t('test', '{n} cucumbez|{n} cucumberz', 5));
	}

	// Choice: 'expr1#msg1|expr2#msg2|expr3#msg3'
	function testChoice(){
		Yee::app()->setLanguage('ru');

		// simple choices
		$this->assertEquals('одна книга', Yee::t('test', 'n==1#one book|n>1#many books', 1));
		$this->assertEquals('много книг', Yee::t('test', 'n==1#one book|n>1#many books', 10));
		$this->assertEquals('одна книга', Yee::t('test', '1#one book|n>1#many books', 1));
		$this->assertEquals('много книг', Yee::t('test', '1#one book|n>1#many books', 10));
	}

	function testChoiceSameLanguage(){
		Yee::app()->setLanguage('es');

		$this->assertEquals('one book', Yee::t('test', 'n==1#one book|n>1#many books', 1));
		$this->assertEquals('many books', Yee::t('test', 'n==1#one book|n>1#many books', 10));
	}

	function testChoicePlaceholders(){
		//$this->assertEquals('51 apples', Yee::t('app', '1#1apple|n>1|{n} apples', array(51, 'n'=>51)));
	}

	function testChoicePlaceholdersSameLanguage(){

	}
}
