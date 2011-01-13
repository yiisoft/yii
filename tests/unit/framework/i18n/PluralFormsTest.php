<?php
/**
 * Plural forms test for translations
 * http://code.google.com/p/yii/issues/detail?id=1875
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
}
