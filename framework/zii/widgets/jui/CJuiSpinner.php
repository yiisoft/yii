<?php
/**
 * CJuiSpinner class file.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiInputWidget');

/**
 * CJuiSpinner displays a spinner widget.
 *
 * CJuiSpinner encapsulates the {@link http://jqueryui.com/spinner/ JUI Spinner} plugin.
 *
 * To use this widget, you may insert the following code in a view:
 * <pre>
 * $this->widget('zii.widgets.jui.CJuiSpinner',array(
 *     'model'=>$car, // widget would be used to select a selling car price
 *     'attribute'=>'price', // model attribute which stores price value
 *     'culture'=>'de-DE',
 *     'options'=>array(
 *         'numberFormat'=>'C', // currency, euros since culture is 'de-DE'
 *         'max'=>95000, // upper price limit is 95000 euros
 *         'min'=>1500, // lower price limit is 1500 euros
 *         'step'=>100, // change value when using arrow keys or clicking on buttons
 *         'page'=>500, // change value when using PgUp/PgDn keys
 *         'incremental'=>true, // increment speed would grow constantly
 *     ),
 * ));
 * </pre>
 *
 * By configuring the {@link CJuiWidget::$options} property, you may specify the options that need to be passed
 * to the JUI Spinner plugin. Please refer to the {@link http://api.jqueryui.com/spinner/ JUI Spinner} documentation
 * for possible options (name-value pairs).
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @package zii.widgets.jui
 * @since 1.1.14
 */
class CJuiSpinner extends CJuiInputWidget
{
	/**
	 * @var string the culture ID (e.g. 'fr-FR', 'de', 'sr-Cyrl-RS') for the language to be used by the spinner.
	 * If this property is not set, the currently set culture in Globalize is used, see
	 * {@link https://github.com/jquery/globalize/ Globalize docs} for available cultures. Only relevant if the
	 * 'numberFormat' options key is set. Implied options are stored in {@link CJuiWidget::$options} property.
	 */
	public $culture;
	/**
	 * @var string the Globalize culture script file URL (e.g. '/scripts/globalize.culture.de-custom.js').
	 * This property could be useful if there is need to replace or extend default Globalize culture file.
	 */
	public $cultureScriptFile;

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

		if($this->hasModel())
			echo CHtml::activeTextField($this->model,$this->attribute,$this->htmlOptions);
		else
			echo CHtml::textField($name,$this->value,$this->htmlOptions);

		$cs=Yii::app()->getClientScript();
		if($this->culture!==null && isset($this->options['numberFormat']))
		{
			$this->options['culture']=$this->culture;
			$cs->registerCoreScript('globalize');
			if($this->cultureScriptFile===null)
				$cs->registerScriptFile($cs->getCoreScriptUrl().'/globalize/cultures/globalize.culture.'.$this->culture.'.js');
			else
				$cs->registerScriptFile($this->cultureScriptFile);
		}

		$options=CJavaScript::encode($this->options);
		$cs->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').spinner($options);");
	}
}
