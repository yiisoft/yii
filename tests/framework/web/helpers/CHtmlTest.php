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