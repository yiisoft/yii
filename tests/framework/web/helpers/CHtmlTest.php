<?php

class CHtmlTest extends CTestCase
{
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
"<form action=\"index?myFirstParam=3&amp;mySecondParam=true\" method=\"get\">
<div style=\"display:none\"><input type=\"hidden\" value=\"3\" name=\"myFirstParam\" />
<input type=\"hidden\" value=\"true\" name=\"mySecondParam\" /></div>"),
                
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

	public function providerCloseTag()
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

	public function providerCdata()
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

	public function providerCss()
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

	public function providerCssFile()
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

	public function providerScript()
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

	public function providerScriptFile()
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

	public function providerGetIdByName()
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

		$this->assertEquals(null, CHtml::resolveValue($testModel, 'arrayAttr[k7]'));
		$this->assertEquals(null, CHtml::resolveValue($testModel, 'arrayAttr[k7][k8]'));

		$this->assertEquals($testModel->arrayAttr, CHtml::resolveValue($testModel, '[ignored-part]arrayAttr'));
		$this->assertEquals('v1', CHtml::resolveValue($testModel, '[ignored-part]arrayAttr[k1]'));
		$this->assertEquals('v4', CHtml::resolveValue($testModel, '[ignore-this]arrayAttr[k3][k4]'));
	}

	public function providerPageStateField()
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

	public function providerEncodeDecode()
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

	public function providerRefresh()
	{
		return array(
			array(
				10,
				'http://yiiframework.com/',
				'<meta http-equiv="refresh" content="10;http://yiiframework.com/" />'."\n",
			),
			array(
				15,
				array('site/index'),
				// assertion contains two lines because CClientScript::$registerMetaTag does not
				// rewrites already added refresh meta tag (accumulates)
				'<meta http-equiv="refresh" content="10;http://yiiframework.com/" />'."\n".
				'<meta http-equiv="refresh" content="15;/bootstrap.php?r=site/index" />'."\n",
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

    public function __constructor(array $properties)
    {
        foreach($properties as $property=>$value)
        {
            if(!property_exists($this, $property))
            {
                throw new Exception("$property is not a property of this class, and I'm not allowing you to add it!");
            }
            $this->{$property} = $value;
        }
    }
    
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
