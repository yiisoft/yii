<?php
/**
 * CJuiDatePicker class file.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiInputWidget');

/**
 * CJuiDatePicker displays a datepicker.
 *
 * CJuiDatePicker encapsulates the {@link http://jqueryui.com/demos/datepicker/ JUI
 * datepicker} plugin.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('zii.widgets.jui.CJuiDatePicker', array(
 *     'name'=>'publishDate',
 *     // additional javascript options for the date picker plugin
 *     'options'=>array(
 *         'showAnim'=>'fold',
 *     ),
 *     'htmlOptions'=>array(
 *         'style'=>'height:20px;'
 *     ),
 * ));
 * </pre>
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the JUI datepicker plugin. Please refer to
 * the {@link http://jqueryui.com/demos/datepicker/ JUI datepicker} documentation
 * for possible options (name-value pairs).
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @version $Id$
 * @package zii.widgets.jui
 * @since 1.1
 */
class CJuiDatePicker extends CJuiInputWidget
{
	/**
	 * @var string the locale ID (eg 'fr', 'de') for the language to be used by the date picker.
	 * If this property is not set, I18N will not be involved. That is, the date picker will show in English.
	 * You can force English language by setting the language attribute as '' (empty string)
	 */
	public $language;

	/**
	 * @var string The i18n Jquery UI script file. It uses scriptUrl property as base url.
	 */
	public $i18nScriptFile = 'jquery-ui-i18n.min.js';

	/**
	 * @var array The default options called just one time per request. This options will alter every other CJuiDatePicker instance in the page.
	 * It has to be set at the first call of CJuiDatePicker widget in the request.
	 */
	public $defaultOptions;

	/**
	 * @var boolean If true, shows the widget as an inline calendar and the input as a hidden field. Use the onSelect event to update the hidden field
	 */
	public $flat = false;

	/**
	* @var string if specified refer to a declared javascript function name invoked whenever onSelect fires,
		
		function declaration:
			function(dateSelected) { ... }
		
		as an example:
			onSelect=>'myJsFunctionName' , 
		or inline:		
			onSelect=>'js:function(dateSelected){ ...do something... }' 			
		
		- Enh #1238: CJuiDatePicker::onSelect event handler. (christiansalazar)
	*/
	public $onSelect = '';
	
	/**
	 * Run this widget.
	 * This method registers necessary javascript and renders the needed HTML code.
	 */
	public function run()
	{

		list($name,$id)=$this->resolveNameID();

		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;
		if(isset($this->htmlOptions['name']))
			$name=$this->htmlOptions['name'];

		if ($this->flat===false)
		{
			if($this->hasModel())
				echo CHtml::activeTextField($this->model,$this->attribute,$this->htmlOptions);
			else
				echo CHtml::textField($name,$this->value,$this->htmlOptions);
			
			if (!isset($this->options['onSelect']) && ($this->onSelect != ''))
				$this->options['onSelect']=	new CJavaScriptExpression($this->onSelect);
		}
		else
		{
			if($this->hasModel())
			{
				echo CHtml::activeHiddenField($this->model,$this->attribute,$this->htmlOptions);
				$attribute = $this->attribute;
				$this->options['defaultDate'] = $this->model->$attribute;
			}
			else
			{
				echo CHtml::hiddenField($name,$this->value,$this->htmlOptions);
				$this->options['defaultDate'] = $this->value;
			}

			if (!isset($this->options['onSelect'])){
				$hiddenFieldUpdater = "jQuery('#{$id}').val(selectedDate)";
				if($this->onSelect != ''){
					// yes it has a user defined js function, two cases here: 
					// inline or referenced_by_name js function.
					if((strpos($this->onSelect,"js:") === 0) || (strpos($this->onSelect,"function(") === 0)){
						$onSelect = ltrim($this->onSelect,"js:");
						//inline
						$jsExpr = 
							"function(selDate){ jQuery('#{$id}').val($(this).val());"
							."( {$onSelect} )(selDate) }";
						// please uncomment this line to see how this function is built
						//echo "[$jsExpr]"; 
						$this->options['onSelect']=new CJavaScriptExpression($jsExpr);
					}
					else{
						//reference by name
						$userDef = $this->onSelect."(selectedDate);";
						$this->options['onSelect']=new CJavaScriptExpression(
							"function( selectedDate ) { {$hiddenFieldUpdater};{$userDef} }"
						);
					}
				}else{
					// no user defined function (onSelect is blank)
					$this->options['onSelect']=new CJavaScriptExpression(
						"function( selectedDate ) { {$hiddenFieldUpdater};  }"
					);
				}
			}
					

			$id = $this->htmlOptions['id'] = $id.'_container';
			$this->htmlOptions['name'] = $name.'_container';

			echo CHtml::tag('div', $this->htmlOptions, '');
		}

		$options=CJavaScript::encode($this->options);
		$js = "jQuery('#{$id}').datepicker($options);";

		if ($this->language!='' && $this->language!='en')
		{
			$this->registerScriptFile($this->i18nScriptFile);
			$js = "jQuery('#{$id}').datepicker(jQuery.extend({showMonthAfterYear:false}, jQuery.datepicker.regional['{$this->language}'], {$options}));";
		}

		$cs = Yii::app()->getClientScript();

		if (isset($this->defaultOptions))
		{
			$this->registerScriptFile($this->i18nScriptFile);
			$cs->registerScript(__CLASS__, 	$this->defaultOptions!==null?'jQuery.datepicker.setDefaults('.CJavaScript::encode($this->defaultOptions).');':'');
		}
		$cs->registerScript(__CLASS__.'#'.$id, $js);

	}
}