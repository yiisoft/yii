<?php

class CHtmlTest extends CTestCase
{
	public function setUp()
	{
		// clean up any possible garbage in global clientScript app component
		Yii::app()->clientScript->reset();

		// reset CHtml ID counter
		CHtml::$count=0;

		Yii::app()->request->baseUrl=null; // reset so that it can be determined based on scriptUrl
		Yii::app()->request->scriptUrl='/bootstrap.php';
	}

	public function tearDown()
	{
		// do not keep any garbage in global clientScript app component
		Yii::app()->clientScript->reset();
	}

	/* HTML characters encode/decode tests */

	public static function providerEncodeArray()
	{
		return array(
				array( array('lessThanExpression'=>'4 < 9'), array('lessThanExpression'=>'4 &lt; 9') ),
				array( array(array('lessThanExpression'=>'4 < 9')), array(array('lessThanExpression'=>'4 &lt; 9')) ),
				array( array(array('lessThanExpression'=>'4 < 9'), 'greaterThanExpression'=>'4 > 9'), array(array('lessThanExpression'=>'4 &lt; 9'), 'greaterThanExpression'=>'4 &gt; 9') )
			);
	}

	/**
	 * @dataProvider providerEncodeArray
	 *
	 * @param type $data
	 * @param type $assertion
	 */
	public function testEncodeArray($data, $assertion)
	{
		$this->assertEquals($assertion, CHtml::encodeArray($data));
	}

	/* Javascript generator tests */

	public static function providerAjax()
	{
		return array(
				array(array("url" => "index"), "jQuery.ajax({'url':'index','cache':false});"),
				array(array("url" => "index", "success" => "function() { this.alert(\"HI\"); }"), "jQuery.ajax({'url':'index','success':function() { this.alert(\"HI\"); },'cache':false});"),
				array(array("async" => true, "success" => "function() { this.alert(\"HI\"); }"), "jQuery.ajax({'async':true,'success':function() { this.alert(\"HI\"); },'url':location.href,'cache':false});"),
				array(array("update" =>"#my-div", "success" => "function() { this.alert(\"HI\"); }"), "jQuery.ajax({'success':function() { this.alert(\"HI\"); },'url':location.href,'cache':false});"),
				array(array("update" =>"#my-div"), "jQuery.ajax({'url':location.href,'cache':false,'success':function(html){jQuery(\"#my-div\").html(html)}});"),
				array(array("replace" =>"#my-div", "success" => "function() { this.alert(\"HI\"); }"), "jQuery.ajax({'success':function() { this.alert(\"HI\"); },'url':location.href,'cache':false});"),
				array(array("replace" =>"#my-div"), "jQuery.ajax({'url':location.href,'cache':false,'success':function(html){jQuery(\"#my-div\").replaceWith(html)}});")
			);
	}

	/**
	 * @dataProvider providerAjax
	 *
	 * @param type $options
	 * @param type $assertion
	 */
	public function testAjax($options, $assertion)
	{
		$this->assertEquals($assertion, CHtml::ajax($options));
	}

	/* DOM element generated from model attribute tests */

	public static function providerActiveDOMElements()
	{
		return array(
				array(new CHtmlTestModel(array('attr1'=>true)), 'attr1', array(), '<input id="ytCHtmlTestModel_attr1" type="hidden" value="0" name="CHtmlTestModel[attr1]" /><input name="CHtmlTestModel[attr1]" id="CHtmlTestModel_attr1" value="1" type="checkbox" />'),
				array(new CHtmlTestModel(array('attr1'=>false)), 'attr1', array(), '<input id="ytCHtmlTestModel_attr1" type="hidden" value="0" name="CHtmlTestModel[attr1]" /><input name="CHtmlTestModel[attr1]" id="CHtmlTestModel_attr1" value="1" type="checkbox" />')
			);
	}

	/**
	 * @dataProvider providerActiveDOMElements
	 *
	 * @param string $action
	 * @param string $method
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testActiveCheckbox($model,$attribute,$htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::activeCheckBox($model,$attribute,$htmlOptions));
	}

	/* Static DOM element generator tests */

	public static function providerBeginForm()
	{
		return array(
				array("index", "get", array(), '<form action="index" method="get">'),
				array("index", "post", array(), '<form action="index" method="post">'),
				array("index?myFirstParam=3&mySecondParam=true", "get", array(),
"<form action=\"index?myFirstParam=3&amp;mySecondParam=true\" method=\"get\">\n".
"<input type=\"hidden\" value=\"3\" name=\"myFirstParam\" />\n".
"<input type=\"hidden\" value=\"true\" name=\"mySecondParam\" />"),

			);
	}

	/**
	 * @dataProvider providerBeginForm
	 *
	 * @param string $action
	 * @param string $method
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testBeginForm($action, $method, $htmlOptions, $assertion)
	{
		/* TODO - Steven Wexler - 3/5/11 - Mock out static methods in this function when CHtml leverages late static method binding
		 * because PHPUnit.  This is only possible Yii supports only >= PHP 5.3   - */
		$this->assertEquals($assertion, CHtml::beginForm($action, $method, $htmlOptions));
		$this->assertEquals($assertion, CHtml::form($action, $method, $htmlOptions));
	}

	public static function providerTextArea()
	{
		return array(
				array("textareaone", '', array(), "<textarea name=\"textareaone\" id=\"textareaone\"></textarea>"),
				array("textareaone", '', array("id"=>"MyAwesomeTextArea", "dog"=>"Lassie", "class"=>"colorful bright"), "<textarea id=\"MyAwesomeTextArea\" dog=\"Lassie\" class=\"colorful bright\" name=\"textareaone\"></textarea>"),
				array("textareaone", '', array("id"=>false), "<textarea name=\"textareaone\"></textarea>"),
			);
	}

	/**
	 * @dataProvider providerTextArea
	 *
	 * @param string $name
	 * @param string $value
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testTextArea($name, $value, $htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::textArea($name, $value, $htmlOptions));
	}

	public function providerOpenTag()
	{
		return array(
			array('div', array(), '<div>'),
			array('h1', array('id'=>'title', 'class'=>'red bold'), '<h1 id="title" class="red bold">'),
			array('ns:tag', array('attr1'=>'attr1value1 attr1value2'), '<ns:tag attr1="attr1value1 attr1value2">'),
			array('option', array('checked'=>true, 'disabled'=>false, 'defer'=>true), '<option checked="checked" defer="defer">'),
			array('another-tag', array('some-attr'=>'<>/\\<&', 'encode'=>true), '<another-tag some-attr="&lt;&gt;/\&lt;&amp;">'),
			array('tag', array('attr-no-encode'=>'<&', 'encode'=>false), '<tag attr-no-encode="<&">'),
		);
	}

	/**
	 * @dataProvider providerOpenTag
	 *
	 * @param string $tag
	 * @param string $htmlOptions
	 * @param string $assertion
	 */
	public function testOpenTag($tag, $htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::openTag($tag, $htmlOptions));
	}

	public static function providerCloseTag()
	{
		return array(
			array('div', '</div>'),
			array('h1', '</h1>'),
			array('ns:tag', '</ns:tag>'),
			array('minus-tag', '</minus-tag>'),
		);
	}

    /**
	 * @dataProvider providerCloseTag
	 *
	 * @param string $tag
	 * @param string $assertion
	 */
	public function testCloseTag($tag, $assertion)
	{
		$this->assertEquals($assertion, CHtml::closeTag($tag));
	}

	public static function providerCdata()
	{
		return array(
			array('cdata-content', '<![CDATA[cdata-content]]>'),
			array('123321', '<![CDATA[123321]]>'),
		);
	}

	/**
	 * @dataProvider providerCdata
	 *
	 * @param string $data
	 * @param string $assertion
	 */
	public function testCdata($data, $assertion)
	{
		$this->assertEquals($assertion, CHtml::cdata($data));
	}

	public function providerMetaTag()
	{
		return array(
			array('simple-meta-tag', null, null, array(),
				'<meta content="simple-meta-tag" />'),
			array('test-name-attr', 'random-name', null, array(),
				'<meta name="random-name" content="test-name-attr" />'),
			array('text/html; charset=UTF-8', null, 'Content-Type', array(),
				'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'),
			array('test-attrs', null, null, array('xhtml-invalid-attr'=>'attr-value'),
				'<meta xhtml-invalid-attr="attr-value" content="test-attrs" />'),
			array('complex-test', 'testing-name', 'Content-Type', array('attr1'=>'value2'),
				'<meta attr1="value2" name="testing-name" http-equiv="Content-Type" content="complex-test" />'),
		);
	}

	/**
	 * @dataProvider providerMetaTag
	 *
	 * @param string $content
	 * @param string $name
	 * @param string $httpEquiv
	 * @param array $options
	 * @param string $assertion
	 */
	public function testMetaTag($content, $name, $httpEquiv, $options, $assertion)
	{
		$this->assertEquals($assertion, CHtml::metaTag($content, $name, $httpEquiv, $options));
	}

	public function providerLinkTag()
	{
		return array(
			array(null, null, null, null, array(), '<link />'),
			array('stylesheet', null, null, null, array(), '<link rel="stylesheet" />'),
			array(null, 'text/css', null, null, array(), '<link type="text/css" />'),
			array(null, null, '/css/style.css', null, array(), '<link href="/css/style.css" />'),
			array(null, null, null, 'screen', array(), '<link media="screen" />'),
			array(null, null, null, null, array('attr'=>'value'), '<link attr="value" />'),
			array('stylesheet', 'text/css', '/css/style.css', 'screen', array('attr'=>'value'),
				'<link attr="value" rel="stylesheet" type="text/css" href="/css/style.css" media="screen" />'),
		);
	}

	/**
	 * @dataProvider providerLinkTag
	 *
	 * @param string $relation
	 * @param string $type
	 * @param string $href
	 * @param string $media
	 * @param array $options
	 * @param string $assertion
	 */
	public function testLinkTag($relation, $type, $href, $media, $options, $assertion)
	{
		$this->assertEquals($assertion, CHtml::linkTag($relation, $type, $href, $media, $options));
	}

	public static function providerCss()
	{
		return array(
			array('h1{font-size:20px;line-height:26px;}', '',
				"<style type=\"text/css\">\n/*<![CDATA[*/\nh1{font-size:20px;line-height:26px;}\n/*]]>*/\n</style>"),
			array('h2{font-size:16px;line-height:22px;}', 'screen',
				"<style type=\"text/css\" media=\"screen\">\n/*<![CDATA[*/\nh2{font-size:16px;line-height:22px;}\n/*]]>*/\n</style>"),
		);
	}

	/**
	 * @dataProvider providerCss
	 *
	 * @param string $text
	 * @param string $media
	 * @param string $assertion
	 */
	public function testCss($text, $media, $assertion)
	{
		$this->assertEquals($assertion, CHtml::css($text, $media));
	}

	public static function providerCssFile()
	{
		return array(
			array('/css/style.css?a=1&b=2', '', '<link rel="stylesheet" type="text/css" href="/css/style.css?a=1&amp;b=2" />'),
			array('/css/style.css?c=3&d=4', 'screen', '<link rel="stylesheet" type="text/css" href="/css/style.css?c=3&amp;d=4" media="screen" />'),
		);
	}

	/**
	 * @dataProvider providerCssFile
	 *
	 * @param string $url
	 * @param string $media
	 * @param string $assertion
	 */
	public function testCssFile($url, $media, $assertion)
	{
		$this->assertEquals($assertion, CHtml::cssFile($url, $media));
	}

	public static function providerScript()
	{
		return array(
			array('var a = 10;', "<script type=\"text/javascript\">\n/*<![CDATA[*/\nvar a = 10;\n/*]]>*/\n</script>"),
			array("\t(function() { var x = 100; })();\n\tvar y = 200;",
				"<script type=\"text/javascript\">\n/*<![CDATA[*/\n\t(function() { var x = 100; })();\n\tvar y = 200;\n/*]]>*/\n</script>"),
		);
	}

	/**
	 * @dataProvider providerScript
	 *
	 * @param string $text
	 * @param string $assertion
	 */
	public function testScript($text, $assertion)
	{
		$this->assertEquals($assertion, CHtml::script($text));
	}

	public static function providerScriptWithHtmlOptions()
	{
		return array(
			array(
				'var a = 10;',
				array('defer'=>true),
				"<script type=\"text/javascript\" defer=\"defer\">\n/*<![CDATA[*/\nvar a = 10;\n/*]]>*/\n</script>"
			),
			array(
				'var a = 10;',
				array('async'=>true),
				"<script type=\"text/javascript\" async=\"async\">\n/*<![CDATA[*/\nvar a = 10;\n/*]]>*/\n</script>"
			),
            array(
                'var a = 10;',
                array('async'=>false),
                "<script type=\"text/javascript\" async=\"false\">\n/*<![CDATA[*/\nvar a = 10;\n/*]]>*/\n</script>"
            ),
		);
	}

	/**
	 * @depends testScript
	 * @dataProvider providerScriptWithHtmlOptions
	 *
	 * @param string $text
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testScriptWithHtmlOptions($text, $htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::script($text,$htmlOptions));
	}

	public static function providerScriptFile()
	{
		return array(
			array('/js/main.js?a=2&b=4', '<script type="text/javascript" src="/js/main.js?a=2&amp;b=4"></script>'),
			array('http://company.com/get-user-by-name?name=Василий&lang=ru',
				'<script type="text/javascript" src="http://company.com/get-user-by-name?name=Василий&amp;lang=ru"></script>'),
		);
	}

	/**
	 * @dataProvider providerScriptFile
	 *
	 * @param string $text
	 * @param string $assertion
	 */
	public function testScriptFile($text, $assertion)
	{
		$this->assertEquals($assertion, CHtml::scriptFile($text));
	}

	public static function providerScriptFileWithHtmlOptions()
	{
		return array(
			array(
				'/js/main.js?a=2&b=4',
				array('defer'=>true),
				'<script type="text/javascript" src="/js/main.js?a=2&amp;b=4" defer="defer"></script>'
			),
			array(
				'/js/main.js?a=2&b=4',
				array('async'=>true),
				'<script type="text/javascript" src="/js/main.js?a=2&amp;b=4" async="async"></script>'
			),
			array(
				'/js/main.js?a=2&b=4',
				array('onload'=>"some_js_function();"),
				'<script type="text/javascript" src="/js/main.js?a=2&amp;b=4" onload="some_js_function();"></script>'
			),
		);
	}

	/**
	 * @depends testScriptFile
	 * @dataProvider providerScriptFileWithHtmlOptions
	 *
	 * @param string $text
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testScriptFileWithHtmlOptions($text, $htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::scriptFile($text, $htmlOptions));
	}

	public function testEndForm()
	{
		$this->assertEquals('</form>', CHtml::endForm());
	}

	public function testActiveId()
	{
		$testModel=new CHtmlTestModel();
		$this->assertEquals('CHtmlTestModel_attr1', CHtml::activeId($testModel, 'attr1'));
		$this->assertEquals('CHtmlTestModel_attr2', CHtml::activeId($testModel, 'attr2'));
		$this->assertEquals('CHtmlTestModel_attr3', CHtml::activeId($testModel, 'attr3'));
		$this->assertEquals('CHtmlTestModel_attr4', CHtml::activeId($testModel, 'attr4'));
	}

	public function testActiveName()
	{
		$testModel=new CHtmlTestModel();
		$this->assertEquals('CHtmlTestModel[attr1]', CHtml::activeName($testModel, 'attr1'));
		$this->assertEquals('CHtmlTestModel[attr2]', CHtml::activeName($testModel, 'attr2'));
		$this->assertEquals('CHtmlTestModel[attr3]', CHtml::activeName($testModel, 'attr3'));
		$this->assertEquals('CHtmlTestModel[attr4]', CHtml::activeName($testModel, 'attr4'));
	}

	public static function providerGetIdByName()
	{
		return array(
			array('ContactForm[name]', 'ContactForm_name'),
			array('Order[name][first]', 'Order_name_first'),
			array('Order[name][last]', 'Order_name_last'),
			array('Recipe[photo][]', 'Recipe_photo'),
			array('Request title', 'Request_title'),
		);
	}

	/**
	 * @dataProvider providerGetIdByName
	 *
	 * @param string $text
	 * @param string $assertion
	 */
	public function testGetIdByName($text, $assertion)
	{
		$this->assertEquals($assertion, CHtml::getIdByName($text));
	}

	public function testResolveName()
	{
		$testModel=new CHtmlTestFormModel();

		$attrName='stringAttr';
		$this->assertEquals('CHtmlTestFormModel[stringAttr]', CHtml::resolveName($testModel, $attrName));
		$this->assertEquals('stringAttr', $attrName);

		$attrName='arrayAttr[k1]';
		$this->assertEquals('CHtmlTestFormModel[arrayAttr][k1]', CHtml::resolveName($testModel, $attrName));
		$this->assertEquals('arrayAttr[k1]', $attrName);

		$attrName='arrayAttr[k3][k5]';
		$this->assertEquals('CHtmlTestFormModel[arrayAttr][k3][k5]', CHtml::resolveName($testModel, $attrName));
		$this->assertEquals('arrayAttr[k3][k5]', $attrName);

		$attrName='[k3][k4]arrayAttr';
		$this->assertEquals('CHtmlTestFormModel[k3][k4][arrayAttr]', CHtml::resolveName($testModel, $attrName));
		$this->assertEquals('arrayAttr', $attrName);

		$attrName='[k3]arrayAttr[k4]';
		$this->assertEquals('CHtmlTestFormModel[k3][arrayAttr][k4]', CHtml::resolveName($testModel, $attrName));
		$this->assertEquals('arrayAttr[k4]', $attrName);

		// next two asserts gives 100% code coverage of the CHtml::resolveName() method
		// otherwise penultimate line (last closing curly bracket) of the CHtml::resolveName() will not be unit tested
		$attrName='[k3';
		$this->assertEquals('CHtmlTestFormModel[[k3]', CHtml::resolveName($testModel, $attrName));
		$this->assertEquals('[k3', $attrName);
	}

	public function testResolveValue()
	{
		$testModel=new CHtmlTestFormModel();

		$this->assertEquals('stringAttrValue', CHtml::resolveValue($testModel, 'stringAttr'));
		$this->assertEquals('v1', CHtml::resolveValue($testModel, 'arrayAttr[k1]'));
		$this->assertEquals('v2', CHtml::resolveValue($testModel, 'arrayAttr[k2]'));
		$this->assertEquals($testModel->arrayAttr['k3'], CHtml::resolveValue($testModel, 'arrayAttr[k3]'));
		$this->assertEquals('v4', CHtml::resolveValue($testModel, 'arrayAttr[k3][k4]'));
		$this->assertEquals('v5', CHtml::resolveValue($testModel, 'arrayAttr[k3][k5]'));
		$this->assertEquals('v6', CHtml::resolveValue($testModel, 'arrayAttr[k6]'));

		$this->assertNull(CHtml::resolveValue($testModel, 'arrayAttr[k7]'));
		$this->assertNull(CHtml::resolveValue($testModel, 'arrayAttr[k7][k8]'));

		$this->assertEquals($testModel->arrayAttr, CHtml::resolveValue($testModel, '[ignored-part]arrayAttr'));
		$this->assertEquals('v1', CHtml::resolveValue($testModel, '[ignored-part]arrayAttr[k1]'));
		$this->assertEquals('v4', CHtml::resolveValue($testModel, '[ignore-this]arrayAttr[k3][k4]'));
	}

	public function providerValue()
	{
		$result=array(
			// $model is array
			array(array('k1'=>'v1','k2'=>'v2','v3','v4'),'k1',null,'v1'),
			array(array('k1'=>'v1','k2'=>'v2','v3','v4'),'k2',null,'v2'),
			array(array('k1'=>'v1','k2'=>'v2','v3','v4'),'k3',null,null),
			array(array('k1'=>'v1','k2'=>'v2','v3','v4'),'k3','defaultValue','defaultValue'),

			array(array('k1'=>'v1','k2'=>'v2','v3','v4'),0,null,'v3'),
			array(array('k1'=>'v1','k2'=>'v2','v3','v4'),1,null,'v4'),
			array(array('k1'=>'v1','k2'=>'v2','v3','v4'),2,null,null),
			array(array('k1'=>'v1','k2'=>'v2','v3','v4'),2,'defaultValue','defaultValue'),

			// $model is stdClass
			array((object)array('k1'=>'v1','k2'=>'v2','v3','v4'),'k1',null,'v1'),
			array((object)array('k1'=>'v1','k2'=>'v2','v3','v4'),'k2',null,'v2'),
			array((object)array('k1'=>'v1','k2'=>'v2','v3','v4'),'k3',null,null),
			array((object)array('k1'=>'v1','k2'=>'v2','v3','v4'),'k3','defaultValue','defaultValue'),

			array((object)array('k1'=>'v1','k2'=>'v2','v3','v4'),0,null,null),
			array((object)array('k1'=>'v1','k2'=>'v2','v3','v4'),1,null,null),
			array((object)array('k1'=>'v1','k2'=>'v2','v3','v4'),2,null,null),
			array((object)array('k1'=>'v1','k2'=>'v2','v3','v4'),2,'defaultValue','defaultValue'),

			// static method
			array(array('k1'=>'v1','k2'=>'v2','v3','v4'),array('CHtmlTest','helperTestValue'),null,'v2'),
			array((object)array('k1'=>'v1','k2'=>'v2','v3','v4'),array('CHtmlTest','helperTestValue'),null,'v2'),

			// create_function is not supported by CHtml::value(), we're just testing this feature/property
			array(array('k1'=>'v1','k2'=>'v2','v3','v4'),create_function('$model','return $model["k2"];'),null,null),
			array((object)array('k1'=>'v1','k2'=>'v2','v3','v4'),create_function('$model','return $model->k2;'),null,null),

			// standard PHP functions should not be treated as callables
			array(array('array_filter'=>'array_filter','sort'=>'sort'),'sort',null,'sort'),
			array(array('array_filter'=>'array_filter','sort'=>'sort'),'array_map','defaultValue','defaultValue'),
			array((object)array('array_filter'=>'array_filter','sort'=>'sort'),'sort',null,'sort'),
			array((object)array('array_filter'=>'array_filter','sort'=>'sort'),'array_map','defaultValue','defaultValue'),

			// dot access, array
			array(array('k1'=>array('k2'=>array('k3'=>'v3')),array('v1','k4'=>'v4')),'k1.k2.k3',null,'v3'),
			array(array('k1'=>array('k2'=>array('k3'=>'v3')),array('v1','k4'=>'v4')),'0.0',null,'v1'),
			array(array('k1'=>array('k2'=>array('k3'=>'v3')),array('v1','k4'=>'v4')),'0.k4',null,'v4'),
			array(array('k1'=>array('k2'=>array('k3'=>'v3')),array('v1','k4'=>'v4')),'0.1',null,null),

			// dot access, object
			array((object)array('k1'=>array('k2'=>array('k3'=>'v3')),array('v1','k4'=>'v4')),'k1.k2.k3',null,'v3'),
			array((object)array('k1'=>array('k2'=>array('k3'=>'v3')),array('v1','k4'=>'v4')),'0.0',null,null),
			array((object)array('k1'=>array('k2'=>array('k3'=>'v3')),array('v1','k4'=>'v4')),'0.k4',null,null),
			array((object)array('k1'=>array('k2'=>array('k3'=>'v3')),array('v1','k4'=>'v4')),'0.1',null,null),

			// $attribute parameter is:
			// 1. null or empty string
			// 2. not "0" string, 0 integer or 0.0 double/float
			// 3. empty array doesn't make sense
			array(array('v1'),null,'defaultValue','defaultValue'),
			array(array('v1'),"",'defaultValue','defaultValue'),
			array(array('v1'),"0",'defaultValue','v1'),
			array(array('v1'),0,'defaultValue','v1'),
			array(array('v1'),0.0,'defaultValue','v1'),
		);
		if(class_exists('Closure',false))
		{
			// anonymous function
			$result=array_merge($result,require(dirname(__FILE__).'/CHtml/providerValue.php'));
		}
		return $result;
	}

	/**
	 * @dataProvider providerValue
	 *
	 * @param array|stdClass $model
	 * @param integer|double|string $attribute
	 * @param mixed $defaultValue
	 * @param string $assertion
	 */
	public function testValue($model, $attribute, $defaultValue, $assertion)
	{
		$this->assertEquals($assertion, CHtml::value($model, $attribute, $defaultValue));
	}

	/**
	 * Helper method for {@link testValue()} and {@link providerValue()} methods.
	 */
	public static function helperTestValue($model)
	{
		return is_array($model) ? $model['k2'] : $model->k2;
	}

	public static function providerPageStateField()
	{
		return array(
			array('testing-value', '<input type="hidden" name="'.CController::STATE_INPUT_NAME.'" value="testing-value" />'),
			array('another-testing&value', '<input type="hidden" name="'.CController::STATE_INPUT_NAME.'" value="another-testing&value" />'),
		);
	}

	/**
	 * @dataProvider providerPageStateField
	 *
	 * @param string $value
	 * @param string $assertion
	 */
	public function testPageStateField($value, $assertion)
	{
		$this->assertEquals($assertion, CHtml::pageStateField($value));
	}

	public static function providerEncodeDecode()
	{
		return array(
			array(
				'<h1 class="header" attr=\'value\'>Text header</h1>',
				'&lt;h1 class=&quot;header&quot; attr=&#039;value&#039;&gt;Text header&lt;/h1&gt;',
			),
			array(
				'<p>testing & text</p>',
				'&lt;p&gt;testing &amp; text&lt;/p&gt;',
			),
		);
	}

	/**
	 * @dataProvider providerEncodeDecode
	 *
	 * @param string $text
	 * @param string $assertion
	 */
	public function testEncode($text, $assertion)
	{
		$this->assertEquals($assertion, CHtml::encode($text));
	}

	/**
	 * @dataProvider providerEncodeDecode
	 *
	 * @param string $assertion
	 * @param string $text
	 */
	public function testDecode($assertion, $text)
	{
		$this->assertEquals($assertion, CHtml::decode($text));
	}

	public static function providerRefresh()
	{
		return array(
			array(
				10,
				'http://yiiframework.com/',
				'<meta http-equiv="refresh" content="10;url=http://yiiframework.com/" />'."\n",
			),
			array(
				15,
				array('site/index'),
				'<meta http-equiv="refresh" content="15;url=/bootstrap.php?r=site/index" />'."\n",
			),
		);
	}

	/**
	 * @dataProvider providerRefresh
	 *
	 * @param $seconds
	 * @param $url
	 * @param $assertion
	 */
	public function testRefresh($seconds, $url, $assertion)
	{
		// this adds element to the CClientScript::$metaTags
		CHtml::refresh($seconds, $url);

		// now render html head with registered meta tags
		$output='';
		Yii::app()->clientScript->renderHead($output);

		// and test it now
		$this->assertEquals($assertion, $output);
	}

	public static function providerStatefulForm()
	{
		// we should keep in mind that CHtml::statefulForm() calls CHtml::beginForm() internally
		// so we can make expected assertion value more readable by using CHtml::beginForm() because
		// we are testing stateful feature of the CHtml::statefulForm(), not <form> tag generation
		// same true for CHtml::pageStateField - it is already tested in another method
		return array(
			array(
				array('site/index'),
				'post',
				array(),
				CHtml::form(array('site/index'), 'post', array())."\n".'<div style="display:none">'.CHtml::pageStateField('').'</div>'
			),
			array(
				'/some-static/url',
				'get',
				array('test-attr'=>'test-value'),
				CHtml::form('/some-static/url', 'get', array('test-attr'=>'test-value'))."\n".'<div style="display:none">'.CHtml::pageStateField('').'</div>'
			),
		);
	}

	/**
	 * @dataProvider providerStatefulForm
	 *
	 * @param string $action
	 * @param string $method
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testStatefulForm($action, $method, $htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::statefulForm($action, $method, $htmlOptions));
	}

	public static function providerMailto()
	{
		return array(
			array(
				'Drop me a line! ;-)',
				'admin@example.com',
				array('class'=>'mail-to-admin'),
				'<a class="mail-to-admin" href="mailto:admin@example.com">Drop me a line! ;-)</a>',
			),
			array(
				'Contact me',
				'foo@bar.baz',
				array(),
				'<a href="mailto:foo@bar.baz">Contact me</a>',
			),
			array(
				'boss@acme.com',
				'',
				array(),
				'<a href="mailto:boss@acme.com">boss@acme.com</a>',
			),
		);
	}

	/**
	 * @dataProvider providerMailto
	 *
	 * @param string $text
	 * @param string $email
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testMailto($text, $email, $htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::mailto($text, $email, $htmlOptions));
	}

	public static function providerImage()
	{
		return array(
			array('/images/logo.png', 'YiiSoft, LLC', array(), '<img src="/images/logo.png" alt="YiiSoft, LLC" />'),
			array('/img/test.jpg', '', array('class'=>'test-img'), '<img class="test-img" src="/img/test.jpg" alt="" />'),
		);
	}

	/**
	 * @dataProvider providerImage
	 *
	 * @param string $src
	 * @param string $alt
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testImage($src, $alt, $htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::image($src, $alt, $htmlOptions));
	}

	public function providerActiveLabel()
	{
		return array(
			array(false, 'userName', array(), '<label for="CHtmlTestActiveModel_userName">User Name</label>'),
			array(false, 'userName', array('for'=>'someTestingInput'), '<label for="someTestingInput">User Name</label>'),
			array(false, 'userName', array('label'=>'Custom Label'), '<label for="CHtmlTestActiveModel_userName">Custom Label</label>'),
			array(false, 'userName', array('label'=>false), ''),
			array(true, 'userName', array(), '<label class="error" for="CHtmlTestActiveModel_userName">User Name</label>'),
			array(true, 'userName', array('for'=>'someTestingInput'), '<label class="error" for="someTestingInput">User Name</label>'),
			array(true, 'firstName', array('label'=>'Custom Label'), '<label for="CHtmlTestActiveModel_firstName">Custom Label</label>'),
			array(true, 'userName', array('label'=>false), ''),
			array(false, '[1]userName', array('for'=>'customFor'), '<label for="customFor">User Name</label>'),
		);
	}

	/**
	 * @dataProvider providerActiveLabel
	 *
	 * @param boolean $validate
	 * @param string $attribute
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testActiveLabel($validate, $attribute, $htmlOptions, $assertion)
	{
		$model=new CHtmlTestActiveModel();
		if($validate)
			$model->validate();
		$this->assertEquals($assertion, CHtml::activeLabel($model, $attribute, $htmlOptions));
	}

	public function providerActiveLabelEx()
	{
		return array(
			array(false, 'firstName', array(), '<label for="CHtmlTestActiveModel_firstName">First Name</label>'),
			array(false, 'firstName', array('for'=>'someTestingInput'), '<label for="someTestingInput">First Name</label>'),
			array(false, 'userName', array('label'=>'Custom Label'), '<label for="CHtmlTestActiveModel_userName" class="required">Custom Label <span class="required">*</span></label>'),
			array(false, 'userName', array('label'=>false), ''),
			array(true, 'userName', array(), '<label class="error required" for="CHtmlTestActiveModel_userName">User Name <span class="required">*</span></label>'),
			array(true, 'userName', array('for'=>'someTestingInput'), '<label class="error required" for="someTestingInput">User Name <span class="required">*</span></label>'),
			array(true, 'firstName', array('label'=>'Custom Label'), '<label for="CHtmlTestActiveModel_firstName">Custom Label</label>'),
			array(true, 'firstName', array('label'=>false), ''),
		);
	}

	/**
	 * @dataProvider providerActiveLabelEx
	 *
	 * @param boolean $addErrors
	 * @param string $attribute
	 * @param array $htmlOptions
	 * @param string $validate
	 */
	public function testActiveLabelEx($validate, $attribute, $htmlOptions, $assertion)
	{
		$model=new CHtmlTestActiveModel();
		if($validate)
			$model->validate();
		$this->assertEquals($assertion, CHtml::activeLabelEx($model, $attribute, $htmlOptions));
	}

	public function providerActiveTextField()
	{
		return array(
			array(false, 'userName', array('class'=>'user-name-field'),
				'<input class="user-name-field" name="CHtmlTestActiveModel[userName]" id="CHtmlTestActiveModel_userName" type="text" />'),
			array(true, 'userName', array('class'=>'user-name-field'),
				'<input class="user-name-field error" name="CHtmlTestActiveModel[userName]" id="CHtmlTestActiveModel_userName" type="text" />'),
			array(false, 'firstName', array('class'=>'first-name-field'),
				'<input class="first-name-field" name="CHtmlTestActiveModel[firstName]" id="CHtmlTestActiveModel_firstName" type="text" />'),
			array(true, 'firstName', array('class'=>'first-name-field'),
				'<input class="first-name-field" name="CHtmlTestActiveModel[firstName]" id="CHtmlTestActiveModel_firstName" type="text" />'),
		);
	}

	/**
	 * @dataProvider providerActiveTextField
	 *
	 * @param boolean $validate
	 * @param string $attribute
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testActiveTextField($validate, $attribute, $htmlOptions, $assertion)
	{
		$model=new CHtmlTestActiveModel();
		if($validate)
			$model->validate();
		$this->assertEquals($assertion, CHtml::activeTextField($model, $attribute, $htmlOptions));
	}

	public function providerActiveUrlField()
	{
		return array(
			array(false, 'userName', array('class'=>'test-class-attr'),
				'<input class="test-class-attr" name="CHtmlTestActiveModel[userName]" id="CHtmlTestActiveModel_userName" type="url" />'),
			array(true, 'userName', array('another-attr'=>'another-attr-value', 'id'=>'changed-id'),
				'<input another-attr="another-attr-value" id="changed-id" name="CHtmlTestActiveModel[userName]" type="url" class="error" />'),
			array(false, 'firstName', array(),
				'<input name="CHtmlTestActiveModel[firstName]" id="CHtmlTestActiveModel_firstName" type="url" />'),
			array(true, 'firstName', array('disabled'=>true, 'name'=>'changed-name'),
				'<input disabled="disabled" name="changed-name" id="changed-name" type="url" />'),
		);
	}

	/**
	 * @dataProvider providerActiveUrlField
	 *
	 * @param boolean $validate
	 * @param string $attribute
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testActiveUrlField($validate, $attribute, $htmlOptions, $assertion)
	{
		$model=new CHtmlTestActiveModel();
		if($validate)
			$model->validate();
		$this->assertEquals($assertion, CHtml::activeUrlField($model, $attribute, $htmlOptions));
	}

	public function providerButton()
	{
		return array(
			array('button1', array('name'=>null, 'class'=>'class1'), '<input class="class1" type="button" value="button1" />'),
			array('button2', array('name'=>'custom-name', 'class'=>'class2'), '<input name="custom-name" class="class2" type="button" value="button2" />'),
			array('button3', array('type'=>'submit'), '<input type="submit" name="yt0" value="button3" />'),
			array('button4', array('value'=>'button-value'), '<input value="button-value" name="yt0" type="button" />'),
			array('button5', array(), '<input name="yt0" type="button" value="button5" />'),
		);
	}

	/**
	 * @dataProvider providerButton
	 *
	 * @param string $label
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testButton($label, $htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::button($label, $htmlOptions));
	}

	public function providerHtmlButton()
	{
		return array(
			array('button1', array('name'=>null, 'class'=>'class1'), '<button name="yt0" class="class1" type="button">button1</button>'),
			array('button2', array('name'=>'custom-name', 'class'=>'class2'), '<button name="custom-name" class="class2" type="button">button2</button>'),
			array('button3', array('type'=>'submit'), '<button type="submit" name="yt0">button3</button>'),
			array('button4', array('value'=>'button-value'), '<button value="button-value" name="yt0" type="button">button4</button>'),
			array('button5', array(), '<button name="yt0" type="button">button5</button>'),
		);
	}

	/**
	 * @dataProvider providerHtmlButton
	 *
	 * @param string $label
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testHtmlButton($label, $htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::htmlButton($label, $htmlOptions));
	}

	public function providerSubmitButton()
	{
		return array(
			array('submit', array(), '<input type="submit" name="yt0" value="submit" />'),
			array('submit1', array('type'=>'button'), '<input type="submit" name="yt0" value="submit1" />'),
		);
	}

	/**
	 * @dataProvider providerSubmitButton
	 *
	 * @param string $label
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testSubmitButton($label, $htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::submitButton($label, $htmlOptions));
	}

	public function providerResetButton()
	{
		return array(
			array('reset', array(), '<input type="reset" name="yt0" value="reset" />'),
			array('reset1', array('type'=>'button'), '<input type="reset" name="yt0" value="reset1" />'),
		);
	}

	/**
	 * @dataProvider providerResetButton
	 *
	 * @param string $label
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testResetButton($label, $htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::resetButton($label, $htmlOptions));
	}

	public function providerImageButton()
	{
		return array(
			array('/images/test-image.png', array('src'=>'ignored-src'), '<input src="/images/test-image.png" type="image" name="yt0" />'),
			array('/images/test-image.jpg', array('type'=>'button'), '<input type="image" src="/images/test-image.jpg" name="yt0" />'),
			array('/images/test-image.gif', array('value'=>'image'), '<input value="image" src="/images/test-image.gif" type="image" name="yt0" />'),
		);
	}

	/**
	 * @dataProvider providerImageButton
	 *
	 * @param string $src
	 * @param array $htmlOptions
	 * @param string $assertion
	 */
	public function testImageButton($label, $htmlOptions, $assertion)
	{
		$this->assertEquals($assertion, CHtml::imageButton($label, $htmlOptions));
	}

	public function providerLinkButton()
	{
		return array(
			array('submit', array(), '<a href="#" id="yt0">submit</a>',
				"jQuery('body').on('click','#yt0',function(){jQuery.yii.submitForm(this,'',{});return false;});"),
			array('link-button', array(), '<a href="#" id="yt0">link-button</a>',
				"jQuery('body').on('click','#yt0',function(){jQuery.yii.submitForm(this,'',{});return false;});"),
			array('link-button', array('href'=>'http://yiiframework.com/'), '<a href="#" id="yt0">link-button</a>',
				"jQuery('body').on('click','#yt0',function(){jQuery.yii.submitForm(this,'http://yiiframework.com/',{});return false;});"),
		);
	}

	/**
	 * @dataProvider providerLinkButton
	 *
	 * @param string $label
	 * @param array $htmlOptions
	 * @param string $assertion
	 * @param string $clientScriptOutput
	 */
	public function testLinkButton($label, $htmlOptions, $assertion, $clientScriptOutput)
	{
		$this->assertEquals($assertion, CHtml::linkButton($label, $htmlOptions));

		$output='';
		Yii::app()->getClientScript()->renderBodyEnd($output);
		$this->assertTrue(mb_strpos($output, $clientScriptOutput)!==false);
	}

	public function testAjaxCallbacks()
	{
		$out=CHtml::ajax(array(
			'success'=>'js:function() { /* callback */ }',
		));
		$this->assertTrue(mb_strpos($out,"'success':function() { /* callback */ }", null, Yii::app()->charset)!==false, "Unexpected JavaScript: ".$out);

		$out=CHtml::ajax(array(
			'success'=>'function() { /* callback */ }',
		));
		$this->assertTrue(mb_strpos($out,"'success':function() { /* callback */ }", null, Yii::app()->charset)!==false, "Unexpected JavaScript: ".$out);

		$out=CHtml::ajax(array(
			'success'=>new CJavaScriptExpression('function() { /* callback */ }'),
		));
		$this->assertTrue(mb_strpos($out,"'success':function() { /* callback */ }", null, Yii::app()->charset)!==false, "Unexpected JavaScript: ".$out);
	}
}

/* Helper classes */

class CHtmlTestModel extends CModel
{
	private static $_names=array();

	/**
	 * @property mixed $attr1
	 */
	public $attr1;

	/**
	 * @property mixed $attr2
	 */
	public $attr2;

	/**
	 * @property mixed $attr3
	 */
	public $attr3;

	/**
	 * @property mixed $attr4
	 */
	public $attr4;

    /**
	 * Returns the list of attribute names.
	 * @return array list of attribute names. Defaults to all public properties of the class.
	 */
	public function attributeNames()
	{
		$className=get_class($this);
		if(!isset(self::$_names[$className]))
		{
			$class=new ReflectionClass(get_class($this));
			$names=array();
			foreach($class->getProperties() as $property)
			{
				$name=$property->getName();
				if($property->isPublic() && !$property->isStatic())
					$names[]=$name;
			}
			return self::$_names[$className]=$names;
		}
		else
			return self::$_names[$className];
	}

}

class CHtmlTestFormModel extends CFormModel
{
	public $stringAttr;
	public $arrayAttr;

	public function afterConstruct()
	{
		$this->stringAttr='stringAttrValue';
		$this->arrayAttr=array(
			'k1'=>'v1',
			'k2'=>'v2',
			'k3'=>array(
				'k4'=>'v4',
				'k5'=>'v5',
			),
			'k6'=>'v6',
		);
	}
}

class CHtmlTestActiveModel extends CFormModel
{
	public $userName;
	public $firstName;

	public function rules()
	{
		return array(
			array('userName', 'required'),
			array('firstName', 'safe'),
		);
	}
}
