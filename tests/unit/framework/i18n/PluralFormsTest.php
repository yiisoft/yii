<?php
/**
 * Plural forms test for translations
 * http://code.google.com/p/yii/issues/detail?id=1875
 * http://code.google.com/p/yii/issues/detail?id=1987
 *
 * Basically if the translation has more or less choices than what CLDR specifies,
 * we should still try to make it work without exception.
 *
 *
 * forceTranslation && | in translation && [0] param is number = choice format
 * !forceTranslation && | in source msg && [0] param is number = choice format
 *
 * when a developer writes code, he should be aware if a message embeds a number,
 * it is better he also embeds a '|', even if the source language doesnt have plural form
 */
class PluralFormsTest extends CTestCase
{
	function setUp()
	{
		$config = array(
			'sourceLanguage' => 'es',
			'components' => array(
				'messages' => array(
					'class' => 'CPhpMessageSource',
					'basePath' => dirname(__FILE__).'/data',
					'forceTranslation' => true,
				),
			),
		);

		new TestApplication($config);
		Yii::app()->configure($config);
	}

	function testEnglish()
	{
		Yii::app()->setLanguage('en');

		$this->assertEquals('test', Yii::t('plural', 'test', array(1)));
		$this->assertEquals('tests', Yii::t('plural', 'test', array(2)));
		$this->assertEquals('tests', Yii::t('plural', 'test', array(0)));
	}

	function testRussian()
	{
		Yii::app()->setLanguage('ru');
		$this->assertEquals('тест', Yii::t('plural', 'test', array(1)));
		$this->assertEquals('тест', Yii::t('plural', 'test', array(101)));
		$this->assertEquals('тест', Yii::t('plural', 'test', array(51)));
		$this->assertEquals('теста', Yii::t('plural', 'test', array(2)));
		$this->assertEquals('теста', Yii::t('plural', 'test', array(62)));
		$this->assertEquals('теста', Yii::t('plural', 'test', array(104)));
		$this->assertEquals('тестов', Yii::t('plural', 'test', array(5)));
		$this->assertEquals('тестов', Yii::t('plural', 'test', array(78)));
		$this->assertEquals('тестов', Yii::t('plural', 'test', array(320)));
		$this->assertEquals('тестов', Yii::t('plural', 'test', array(0)));
	}

	function testParametersShortForm(){
		Yii::app()->setLanguage('en');
		$this->assertEquals('tests', Yii::t('plural', 'test', 2));
	}

	function testSpecialCases(){
		Yii::app()->setLanguage('en');
		$this->assertEquals('single', Yii::t('plural', 'single', 2));

		$this->assertEquals('tests', Yii::t('plural', '|test', 2));
	}

	function testPlaceholders(){
		Yii::app()->setLanguage('en');
		$this->assertEquals('51 apples', Yii::t('app', '1 apple|{n} apple', 51));

		Yii::app()->setLanguage('ru');
		$this->assertEquals('51 яблоко', Yii::t('app', '1 apple|{n} apple', 51));
	}

	function testChoiceFormat(){
		//$this->assertEquals('51 apples', Yii::t('app', '1#1apple|n>1|{n} apples', array(51, 'n'=>51)));
	}

	function testNoForcePlurals(){

	}
}
