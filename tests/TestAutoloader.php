<?php
class TestAutoloader extends CTestCase
{
	public function testAutoloaderForAnonymousCallableFilter()
	{
		Yii::$autoloaderFilters['smarty1'] = function($className)
		{
			if (strpos($className, 'Smarty') === 0) {
				return true;
			}

			return false;
		};

		$this->assertTrue(Yii::autoload('SmartyFunctionMeta'));
		$this->assertTrue(Yii::autoload('Smarty_Function_Meta'));
	}

	public function testAutoloaderForFunction()
	{
		function exampleFilter($className)
		{
			if (strpos($className, 'Zend') === 0) {
				return true;
			}

			return false;
		}

		Yii::$autoloaderFilters['zend'] = 'exampleFilter';

		$this->assertTrue(Yii::autoload('ZendFunctionMeta'));
		$this->assertTrue(Yii::autoload('Zend_Function_Meta'));
	}

	public function testAutoloaderForStaticMethod()
	{
		eval('Class SmartyAutoloader {
			public static function exampleFilter($className)
			{
				if (strpos($className, \'Smarty\') === 0) {
					return true;
				}

				return false;
			}
		}');


		Yii::$autoloaderFilters['smarty2'] = array('SmartyAutoloader', 'exampleFilter');

		$this->assertTrue(Yii::autoload('SmartyFunctionMeta'));
		$this->assertTrue(Yii::autoload('Smarty_Function_Meta'));
	}

	public function testAutoloaderWithoutFilter()
	{
		Yii::$enableIncludePath = false;
		$this->assertFalse(Yii::autoload('SomeClass'));
		$this->assertTrue(Yii::autoload('Yii'));
	}
}