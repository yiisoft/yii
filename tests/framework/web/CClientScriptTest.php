<?php

Yii::import('system.web.CClientScript');

/**
 *  @group web
 */
class CClientScriptTest extends CTestCase
{
	/**
	 * @var CClientScript
	 */
	private $_clientScript;
	
	public function setUp()
	{
		$this->_clientScript = new CClientScript();
		$this->_clientScript->setCoreScriptUrl("assets/12345");
		$this->_clientScript->registerCoreScript('jquery');
		$this->_clientScript->registerCoreScript('yii');
	}
	
	/* Test Script Getters */
	
	public function testGetCoreScriptUrl()
	{
		$this->assertEquals('assets/12345', $this->_clientScript->getCoreScriptUrl());
	}
	
	
	public function providerGetPackageBaseUrl()
	{
		return array(
			array('jquery', 'assets/12345'),
			array('yii', 'assets/12345')
		);
	}	
	
	/**
	 * @dataProvider providerGetPackageBaseUrl
	 * 
	 * @param string $name
	 * @param string $assertion 
	 */
	public function testGetPackageBaseUrl($name, $assertion)
	{
		$this->assertEquals($assertion,$this->_clientScript->getPackageBaseUrl($name));
	}
	
	/* Test Script Registers */
	
	public function providerCoreScripts()
	{
		return array(
			array('jquery', array('js'=>array('jquery.js'))),
			array('yiitab', array('js'=>array('jquery.yiitab.js'), 'depends'=>array('jquery'))),
			array('yiiactiveform', array('js'=>array('jquery.yiiactiveform.js'), 'depends'=>array('jquery')))

		);
	}
	/**
	 * @dataProvider providerCoreScripts
	 * 
	 * @param string $name
	 * @param array $assertion 
	 */
	public function testRegisterCoreScript($name, $assertion)
	{
		$returnedClientScript = $this->_clientScript->registerCoreScript($name);
		$this->assertEquals($assertion, $returnedClientScript->corePackages[$name]);
	}
	
	/**
	 * @dataProvider providerCoreScripts
	 * 
	 * @param string $name
	 * @param array $assertion 
	 */
	public function testRegisterPackage($name, $assertion)
	{
		$returnedClientScript = $this->_clientScript->registerPackage($name);
		$this->assertEquals($assertion, $returnedClientScript->corePackages[$name]);
	}

	public function providerScriptFiles()
	{
		return array(
			array('/some/script.js', CClientScript::POS_HEAD, '/some/script.js'),
			array('http://some/script.js', CClientScript::POS_BEGIN, 'http://some/script.js'),
			array('/some/script.js', CClientScript::POS_END, '/some/script.js'),
		);
	}

	/**
	 * @dataProvider providerScriptFiles
	 *
	 * @param string $url
	 * @param integer $position
	 * @param string $assertion
	 */
	public function testRegisterScriptFile($url, $position, $assertion)
	{
		$returnedClientScript = $this->_clientScript->registerScriptFile($url, $position);
		$scriptFiles = $this->readAttribute($returnedClientScript, 'scriptFiles');
		$this->assertEquals($assertion, $scriptFiles[$position][$url]);
	}

	public function providerScriptFilesWithHtmlOptions()
	{
		return array(
			array(
				'/some/script.js',
				CClientScript::POS_HEAD,
				array('defer'=>true),
				array(
					'src'=>'/some/script.js',
					'defer'=>true
				)
			),
		);
	}

	/**
	 * @dataProvider providerScriptFilesWithHtmlOptions
	 *
	 * @param string $url
	 * @param integer $position
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testRegisterScriptFileWithHtmlOptions($url, $position, $htmlOptions, $assertion)
	{
		$returnedClientScript = $this->_clientScript->registerScriptFile($url, $position, $htmlOptions);
		$scriptFiles = $this->readAttribute($returnedClientScript, 'scriptFiles');
		$this->assertEquals($assertion, $scriptFiles[$position][$url]);
	}

	public function providerScripts()
	{
		return array(
			array('jsId', "function() {alert('alert')}", CClientScript::POS_HEAD, "function() {alert('alert')}"),
			array('jsId', "function() {alert('alert')}", CClientScript::POS_BEGIN, "function() {alert('alert')}"),
		);
	}

	/**
	 * @dataProvider providerScripts
	 *
	 * @param string $id
	 * @param string $script
	 * @param integer $position
	 * @param string $assertion
	 */
	public function testRegisterScript($id, $script, $position, $assertion) {
		$returnedClientScript = $this->_clientScript->registerScript($id, $script, $position);
		$this->assertEquals($assertion, $returnedClientScript->scripts[$position][$id]);
	}

	public function providerScriptsWithHtmlOptions()
	{
		return array(
			array(
				'jsId',
				"function() {alert('alert')}",
				CClientScript::POS_HEAD,
				array('defer'=>true),
				array(
					'content'=>"function() {alert('alert')}",
					'defer'=>true,
				)
			),
		);
	}

	/**
	 * @dataProvider providerScriptsWithHtmlOptions
	 *
	 * @param string $id
	 * @param string $script
	 * @param integer $position
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testRegisterScriptWithHtmlOptions($id, $script, $position, $htmlOptions, $assertion) {
		$returnedClientScript = $this->_clientScript->registerScript($id, $script, $position, $htmlOptions);
		$this->assertEquals($assertion, $returnedClientScript->scripts[$position][$id]);
	}
	
	public function providerRegisterCss()
	{
		return array(
			array('myCssDiv', 'float:right;', '', array('myCssDiv'=>array('float:right;', ''))),
			array('myCssDiv', 'float:right;', 'screen', array('myCssDiv'=>array('float:right;', 'screen')))
		);
	}
	
	/**
	 * @dataProvider providerRegisterCss
	 * 
	 * @param string $id
	 * @param string $css
	 * @param string $media
	 * @param array $assertion 
	 */
	public function testRegisterCss($id, $css, $media, $assertion)
	{
		$returnedClientScript = $this->_clientScript->registerCss($id, $css, $media);
		$this->assertAttributeEquals($assertion, 'css', $returnedClientScript);
	}

	public function providerRegisterMetaTag()
	{
		$data = array();

		// Simple:
		$metaTagData = array(
			'name'=>'testMetaTagName',
			'http-equiv'=>false,
			'content'=>'testMetaTagContent',
		);
		$assertion = array(
			$metaTagData
		);
		$data[] = array($metaTagData['content'],$metaTagData['name'],$metaTagData['http-equiv'],array(),$assertion);

		// Http Equiv:
		$metaTagData = array(
			'name'=>'testMetaTagHttpEquiv',
			'http-equiv'=>true,
			'content'=>'testMetaTagHttpEquivContent',
		);
		$assertion = array(
			$metaTagData
		);
		$data[] = array($metaTagData['content'],$metaTagData['name'],$metaTagData['http-equiv'],array(),$assertion);

		return $data;
	}

	/**
	 * @dataProvider providerRegisterMetaTag
	 *
	 * @param string $content
	 * @param string $name
	 * @param boolean $httpEquiv
	 * @param array $options
	 * @param array $assertion
	 */
	public function testRegisterMetaTag($content,$name,$httpEquiv,$options,$assertion)
	{
		$returnedClientScript = $this->_clientScript->registerMetaTag($content,$name,$httpEquiv,$options);
		$this->assertAttributeEquals($assertion, 'metaTags', $returnedClientScript);
	}

	/**
	 * @depends testRegisterMetaTag
	 */
	public function testRegisterDuplicatingMetaTag() {
		$content='Test meta tag content';
		$name='test_meta_tag_name';
		$this->_clientScript->registerMetaTag($content,$name);
		$this->_clientScript->registerMetaTag($content,$name);

		$metaTagData=array(
			'name'=>$name,
			'content'=>$content,
		);
		$assertion=array(
			$metaTagData,
			$metaTagData
		);
		$this->assertAttributeEquals($assertion, 'metaTags', $this->_clientScript);
	}

	/* Test Script Renderers */
	
	public function providerRenderScriptFiles()
	{
		return array(
			array(
				'/some/script.js',
				CClientScript::POS_HEAD,
				array(),
				'<script type="text/javascript" src="/some/script.js"></script>'
			),
			array(
				'/some/script.js',
				CClientScript::POS_BEGIN,
				array(),
				'<script type="text/javascript" src="/some/script.js"></script>'
			),
			array(
				'/some/script.js',
				CClientScript::POS_END,
				array(),
				'<script type="text/javascript" src="/some/script.js"></script>'
			),
			array(
				'/options/script.js',
				CClientScript::POS_HEAD,
				array('defer'=>true),
				'<script type="text/javascript" src="/options/script.js" defer="defer"></script>'
			),
			array(
				'/options/script.js',
				CClientScript::POS_BEGIN,
				array('defer'=>true),
				'<script type="text/javascript" src="/options/script.js" defer="defer"></script>'
			),
			array(
				'/options/script.js',
				CClientScript::POS_END,
				array('defer'=>true),
				'<script type="text/javascript" src="/options/script.js" defer="defer"></script>'
			),
		);
	}

	/**
	 * @depends testRegisterScriptFile
	 * @depends testRegisterScriptFileWithHtmlOptions
	 * 
	 * @dataProvider providerRenderScriptFiles
	 *
	 * @param string $url
	 * @param integer $position
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testRenderScriptFiles($url, $position, $htmlOptions, $assertion)
	{
		$returnedClientScript = $this->_clientScript->registerScriptFile($url, $position, $htmlOptions);
		$output = '<head></head>';
		$returnedClientScript->render($output);
		$this->assertContains($assertion, $output);
	}

	public function providerRenderScripts()
	{
		return array(
			array(
				'some_js_id',
				"function() {alert('script')}",
				CClientScript::POS_HEAD,
				array(),
				CHtml::script("function() {alert('script')}")
			),
			array(
				'some_js_id',
				"function() {alert('script')}",
				CClientScript::POS_BEGIN,
				array(),
				CHtml::script("function() {alert('script')}")
			),
			array(
				'some_js_id',
				"function() {alert('script')}",
				CClientScript::POS_END,
				array(),
				CHtml::script("function() {alert('script')}")
			),
			array(
				'some_js_id',
				"function() {alert('script')}",
				CClientScript::POS_LOAD,
				array(),
				CHtml::script("function() {alert('script')}")
			),
			array(
				'some_js_id',
				"function() {alert('script')}",
				CClientScript::POS_READY,
				array(),
				CHtml::script("function() {alert('script')}")
			),
			// With HTML options
			array(
				'option_js_id',
				"function() {alert('script')}",
				CClientScript::POS_HEAD,
				array('defer'=>true),
				CHtml::script("function() {alert('script')}",array('defer'=>true))
			),
			array(
				'option_js_id',
				"function() {alert('script')}",
				CClientScript::POS_BEGIN,
				array('defer'=>true),
				CHtml::script("function() {alert('script')}",array('defer'=>true))
			),
			array(
				'option_js_id',
				"function() {alert('script')}",
				CClientScript::POS_END,
				array('defer'=>true),
				CHtml::script("function() {alert('script')}",array('defer'=>true))
			),
		);
	}

	/**
	 * @depends testRegisterScript
	 *
	 * @dataProvider providerRenderScripts
	 *
	 * @param string $id
	 * @param string $script
	 * @param integer $position
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testRenderScripts($id, $script, $position, $htmlOptions, $assertion)
	{
		$returnedClientScript = $this->_clientScript->registerScript($id, $script, $position, $htmlOptions);
		$output = '<head></head>';
		$returnedClientScript->render($output);
		$this->assertContains($assertion, $output);
	}
}
