<?php

class CPhpMessageSourceTest extends CTestCase
{
	public function testExtensionTranslation()
	{
		Yee::setPathOfAlias('CPhpMessageSourceTestRoot', dirname(__FILE__));
		Yee::app()->setLanguage('de_DE');
		Yee::app()->messages->extensionPaths['MyTestExtension'] = 'CPhpMessageSourceTestRoot.messages';
		$this->assertEquals('Hallo Welt!', Yee::t('MyTestExtension.testcategory', 'Hello World!'));
	}

	public function testModuleTranslation()
	{
		Yee::app()->setLanguage('de_DE');
		$this->assertEquals('Hallo Welt!', Yee::t('CPhpMessageSourceTest.testcategory', 'Hello World!'));
	}
}
