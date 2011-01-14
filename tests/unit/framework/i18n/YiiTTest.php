<?php
/**
 * Tests for various usages of Yii::t
 *
 * http://code.google.com/p/yii/issues/detail?id=1875
 * http://code.google.com/p/yii/issues/detail?id=1987
 *
 * http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_plural_rules.html
 */
class YiiTTest extends CTestCase
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
		Yii::app()->configure($config);
	}

	// Simple: 'msg'
	function testSimple(){
		Yii::app()->setLanguage('ru');
		$this->assertEquals('апельсины', Yii::t('test', 'oranges'));
	}

	function testSimpleSameLanguage(){

	}

	function testSimplePlaceholders(){
		Yii::app()->setLanguage('ru');
		$this->assertEquals('сумочки caviar', Yii::t('test', '{brand} bags', array('{brand}' => 'caviar')));
		$this->assertEquals('в корзине: 10', Yii::t('test', 'in the cart: {n}', 10));
	}

	function testSimplePlaceholdersSameLanguage(){

	}

	// Plural: 'msg1|msg2|msg3'
	function testPlural(){
		// CLDR
		Yii::app()->setLanguage('ru');

		$this->assertEquals('огурец', Yii::t('test', 'cucumber|cucumbers', array(1)));
		$this->assertEquals('огурец', Yii::t('test', 'cucumber|cucumbers', array(101)));
		$this->assertEquals('огурец', Yii::t('test', 'cucumber|cucumbers', array(51)));
		$this->assertEquals('огурца', Yii::t('test', 'cucumber|cucumbers', array(2)));
		$this->assertEquals('огурца', Yii::t('test', 'cucumber|cucumbers', array(62)));
		$this->assertEquals('огурца', Yii::t('test', 'cucumber|cucumbers', array(104)));
		$this->assertEquals('огурцов', Yii::t('test', 'cucumber|cucumbers', array(5)));
		$this->assertEquals('огурцов', Yii::t('test', 'cucumber|cucumbers', array(78)));
		$this->assertEquals('огурцов', Yii::t('test', 'cucumber|cucumbers', array(320)));
		$this->assertEquals('огурцов', Yii::t('test', 'cucumber|cucumbers', array(0)));

		Yii::app()->setLanguage('en');

        $this->assertEquals('cucumber', Yii::t('plural', 'cucumber|cucumbers', array(1)));
        $this->assertEquals('cucumbers', Yii::t('plural', 'cucumber|cucumbers', array(2)));
        $this->assertEquals('cucumbers', Yii::t('plural', 'cucumber|cucumbers', array(0)));

		// short forms
		Yii::app()->setLanguage('ru');

		$this->assertEquals('огурец', Yii::t('test', 'cucumber|cucumbers', 1));

		// explicit params
		$this->assertEquals('огурец', Yii::t('test', 'cucumber|cucumbers', array(0 => 1)));
	}

	function testPluralPlaceholders(){
		Yii::app()->setLanguage('ru');

		$this->assertEquals('1 огурец', Yii::t('test', '{n} cucumber|{n} cucumbers', 1));
		$this->assertEquals('2 огурца', Yii::t('test', '{n} cucumber|{n} cucumbers', 2));
		$this->assertEquals('5 огурцов', Yii::t('test', '{n} cucumber|{n} cucumbers', 5));

		// more placeholders
		$this->assertEquals('+ 5 огурцов', Yii::t('test', '{sign} {n} cucumber|{sign} {n} cucumbers', array(5, '{sign}' => '+')));

		// placeholder swapping
		$this->assertEquals('один огурец', Yii::t('test', '{n} cucumber|{n} cucumbers', array(1, '{n}' => 'один')));
	}

	function testPluralSameLanguage(){

	}

	function testPluralPlaceholdersSameLanguage(){

	}

	// Choice: 'expr1#msg1|expr2#msg2|expr3#msg3'
	function testChoice(){
		//$this->assertEquals('51 apples', Yii::t('app', '1#1apple|n>1|{n} apples', array(51, 'n'=>51)));
	}

	function testChoiceSameLanguage(){

	}

	function testChoicePlaceholders(){

	}

	function testChoicePlaceholdersSameLanguage(){

	}
}
