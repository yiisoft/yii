<?php
/**
 * CJuiSliderInput class file.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiInputWidget');

/**
 * CJuiSliderInput displays a slider. It can be used in forms and post its value.
 *
 * CJuiSlider encapsulates the {@link http://jqueryui.com/demos/slider/ JUI
 * slider} plugin.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('zii.widgets.jui.CJuiSliderInput', array(
 *     'name'=>'rate',
 *     'value'=>37,
 *     // additional javascript options for the slider plugin
 *     'options'=>array(
 *         'min'=>10,
 *         'max'=>50,
 *     ),
 *     'htmlOptions'=>array(
 *         'style'=>'height:20px;'
 *     ),
 * ));
 * </pre>
 *
 * The widget can also be used in range mode which uses 2 sliders to set a range.
 * In this mode, {@link attribute} and {@link maxAttribute} will define the attribute
 * names for the minimum and maximum range values, respectively. For example:
 *
 * <pre>
 * $this->widget('zii.widgets.jui.CJuiSliderInput', array(
 *     'model'=>$model,
 *     'attribute'=>'timeMin',
 *     'maxAttribute'=>'timeMax',
 *     // additional javascript options for the slider plugin
 *     'options'=>array(
 *         'range'=>true,
 *         'min'=>0,
 *         'max'=>24,
 *     ),
 * ));
 * </pre>
 *
 * If you need to use the slider event, please change the event value for 'stop' or 'change'.
 *
 * By configuring the {@link options} property, you may specify the options
 * that need to be passed to the JUI slider plugin. Please refer to
 * the {@link http://jqueryui.com/demos/slider/ JUI slider} documentation
 * for possible options (name-value pairs).
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @package zii.widgets.jui
 * @since 1.1
 */
class CJuiSliderInput extends CJuiInputWidget
{
	/**
	 * @var string the name of the container element that contains the slider. Defaults to 'div'.
	 */
	public $tagName='div';
	/**
	 * @var integer determines the value of the slider, if there's only one handle. If there is more than one handle,
	 * determines the value of the first handle (min value).
	 */
	public $value;
	/**
	 * @var string the name of the event where the input will be attached to the slider. It
	 * can be 'slide', 'stop' or 'change'. If you want to use 'slide' event change $event property to 'change'.
	 */
	public $event='slide';
	/**
	 * @var string name of attribute for max value if slider is used in ranged mode.
	 */
	public $maxAttribute;
	/**
	 * @var string the input name to be used for max value attribute when using slider in ranged mode. This must be
	 * set if {@link model} is not set.
	 * @since 1.1.13
	 */
	public $maxName;
	/**
	 * @var integer determines the max value of the slider, if there's two handles (ranged mode). Ignored if there's
	 * only one handle.
	 * @since 1.1.13
	 */
	public $maxValue;
	/**
	 * @var string the suffix to be appended to the ID of the max value input element when slider used in ranged mode.
	 * @since 1.1.13
	 */
	public $idEndSuffix='_end';

	/**
	 * Run this widget.
	 * This method registers necessary javascript and renders the needed HTML code.
	 */
	public function run()
	{
		$isRange=isset($this->options['range']) && $this->options['range'];

		list($name,$id)=$this->resolveNameID();
		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;

		if($this->hasModel())
		{
			$attribute=$this->attribute;
			if ($isRange)
			{
				$options=$this->htmlOptions;
				echo CHtml::activeHiddenField($this->model,$this->attribute,$options);
				$options['id']=$options['id'].$this->idEndSuffix;
				echo CHtml::activeHiddenField($this->model,$this->maxAttribute,$options);

				$maxAttribute=$this->maxAttribute;
				$this->options['value']=$this->model->$attribute;
				$this->options['maxValue']=$this->model->$maxAttribute;
				$this->options['values']=array($this->model->$attribute,$this->model->$maxAttribute);
			}
			else
			{
				echo CHtml::activeHiddenField($this->model,$this->attribute,$this->htmlOptions);
				$this->options['value']=$this->model->$attribute;
			}
		}
		else
		{
			if ($isRange)
			{
				list($maxName,$maxId)=$this->resolveNameID('maxName','maxAttribute');

				$options=$this->htmlOptions;
				echo CHtml::hiddenField($name,$this->value,$options);
				$options['id']=$options['id'].$this->idEndSuffix;
				echo CHtml::hiddenField($maxName,$this->maxValue,$options);

				$this->options['value']=$this->value;
				$this->options['maxValue']=$this->maxValue;
				$this->options['values']=array($this->value,$this->maxValue);
			}
			else
			{
				echo CHtml::hiddenField($name,$this->value,$this->htmlOptions);
				if($this->value!==null)
					$this->options['value']=$this->value;
			}
		}

		$idHidden=$this->htmlOptions['id'];
		$this->htmlOptions['id']=$idHidden.'_slider';
		echo CHtml::tag($this->tagName,$this->htmlOptions,'');

		$this->options[$this->event]=$isRange ?
			new CJavaScriptExpression("function(e,ui){ v=ui.values; jQuery('#{$idHidden}').val(v[0]); jQuery('#{$idHidden}{$this->idEndSuffix}').val(v[1]); }"):
			new CJavaScriptExpression('function(event, ui) { jQuery(\'#'. $idHidden .'\').val(ui.value); }');

		$options=empty($this->options) ? '' : CJavaScript::encode($this->options);

		$js = "jQuery('#{$id}_slider').slider($options);\n";
		Yii::app()->getClientScript()->registerScript('CJuiSliderInput#'.$id, $js);
	}

}
