<?php

class CPhpMessageSourceTest extends CTestCase
{
	public function testExtensionTranslation()
	{
		Yii::setPathOfAlias('CPhpMessageSourceTestRoot', dirname(__FILE__));
		Yii::app()->setLanguage('de_DE');
		Yii::app()->messages->extensionPaths['MyTestExtension'] = 'CPhpMessageSourceTestRoot.messages';
		$this->assertEquals('Hallo Welt!', Yii::t('MyTestExtension.testcategory', 'Hello World!'));
	}

	public function testModuleTranslation()
	{
		Yii::app()->setLanguage('de_DE');
		$this->assertEquals('Hallo Welt!', Yii::t('CPhpMessageSourceTest.testcategory', 'Hello World!'));
	}
}
