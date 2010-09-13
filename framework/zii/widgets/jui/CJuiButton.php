<?php
/**
 * CJuiButton class file.
 *
 * @author Sebastian Thierer <sebas@artfos.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiInputWidget');

/**
 * CJuiButton displays a button widget.
 *
 * CJuiButton encapsulates the {@link http://jqueryui.com/demos/button/ JUI Button}
 * plugin.
 *
 * To use this widget as a submit button, you may insert the following code in a view:
 * <pre>
 * $this->widget('zii.widgets.jui.CJuiButton', array(
 * 		'name'=>'submit',
 *     'options'=>array(
 *         'onclick'=>'js:function(){alert("Yes");}',
 *     ),
 * ));
 * </pre>
 *
 * To use this widget as a button, you may insert the following code in a view:
 * <pre>
 * $this->widget('zii.widgets.jui.CJuiButton',
 *		array(
 *			'name'=>'button',
 *			'value'=>'asd',
 *			'onclick'=>'js:function(){alert("clicked"); this.blur(); return false;}',
 * 		)
 * );
 * </pre>
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the JUI button plugin. Please refer to
 * the {@link http://jqueryui.com/demos/button/ JUI Button} documentation
 * for possible options (name-value pairs).
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @version $Id$
 * @package zii.widgets.jui
 * @since 1.1.3
 */
class CJuiButton extends CJuiInputWidget
{
	/**
	 * @var string The button type (possible types: submit, button, link, radio, checkbox).
	 * For radio and checkbox you could add
	 */
	public $buttonType = 'submit';

	/**
	 * @var string The url used when a buttonType "link" is selected.
	 */
	public $url = null;

	/**
	 * @var mixed The value of the current item. Used only for "submit", "button", "radio" and "checkbox"
	 */
	public $value;

	/**
	 * @var string The button text
	 */
	public $caption="";
	/**
	 * @var string The javascript function to be raised when this item is clicked (client event).
	 */
	public $onclick;

	public function run()
	{
		list($name, $id) = $this->resolveNameID();

		if (!isset($this->htmlOptions['name']))
			$this->htmlOptions['name'] = $name;
		if (!isset($this->htmlOptions['id']))
			$this->htmlOptions['id'] = $id;

		switch($this->buttonType)
		{
			case 'submit':
				echo CHtml::submitButton($this->caption, $this->htmlOptions);
				break;
			case 'button':
				echo CHtml::htmlButton($this->caption, $this->htmlOptions);
				break;
			case 'link':
				echo CHtml::link($this->caption, $this->url, $this->htmlOptions);
				break;
			case 'radio':
				if ($this->hasModel()){
					echo CHtml::activeRadioButton($this->model, $this->attribute, $this->htmlOptions);
					//echo CHtml::activeLabel($this->model, $this->attribute);
				}else{
					echo CHtml::radioButton($name, $this->value, $this->htmlOptions);
					//echo CHtml::label($this->label, $name);
				}
				break;
			case 'checkbox':
				if ($this->hasModel())
					echo CHtml::activeCheckbox($this->model, $this->attribute, $this->htmlOptions);
				else
					echo CHtml::checkbox($name, $this->value, $this->htmlOptions);
				break;
			default:
				throw new CException(Yii::t('zii','The button type "{type}" is not supported.',array('{type}'=>$this->buttonType)));
		}

		$cs = Yii::app()->getClientScript();
		$options=empty($this->options) ? '' : CJavaScript::encode($this->options);
		if (isset($this->onclick))
		{
			if(strpos($this->onclick,'js:')!==0)
				$this->onclick='js:'.$this->onclick;
			$click = CJavaScript::encode($this->onclick);
			$cs->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').button($options).click($click);");
		}
		else
			$cs->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').button($options);");
	}
}
