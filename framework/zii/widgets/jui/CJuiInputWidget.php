<?php
/**
 * CJuiInputWidget class file.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('zii.widgets.jui.CJuiWidget');

/**
 * CJuiInputWidget is the base class for JUI widgets that can collect user input.
 *
 * @author Sebastian Thierer <sebathi@gmail.com>
 * @package zii.widgets.jui
 * @since 1.1
 */
abstract class CJuiInputWidget extends CJuiWidget
{
	/**
	 * @var CModel the data model associated with this widget.
	 */
	public $model;
	/**
	 * @var string the attribute associated with this widget.
	 * The name can contain square brackets (e.g. 'name[1]') which is used to collect tabular data input.
	 */
	public $attribute;
	/**
	 * @var string the input name. This must be set if {@link model} is not set.
	 */
	public $name;
	/**
	 * @var string the input value.
	 */
	public $value;

	/**
	 * @param string $nameProperty this class property name which holds element name to be used. This parameter
	 * is available since 1.1.13.
	 * @param string $attributeProperty this class property name which holds model attribute name to be used. This
	 * parameter is available since 1.1.13.
	 * @return array the name and the ID of the input.
	 * @throws CException in case model and attribute property or name property could not be resolved.
	 */
	protected function resolveNameID($nameProperty='name',$attributeProperty='attribute')
	{
		if($this->$nameProperty!==null)
			$name=$this->$nameProperty;
		elseif(isset($this->htmlOptions[$nameProperty]))
			$name=$this->htmlOptions[$nameProperty];
		elseif($this->hasModel())
			$name=CHtml::activeName($this->model,$this->$attributeProperty);
		else
			throw new CException(Yii::t('zii','{class} must specify "model" and "{attribute}" or "{name}" property values.',array('{class}'=>get_class($this),'{attribute}'=>$attributeProperty,'{name}'=>$nameProperty)));

		if(($id=$this->getId(false))===null)
		{
			if(isset($this->htmlOptions['id']))
				$id=$this->htmlOptions['id'];
			else
				$id=CHtml::getIdByName($name);
		}

		return array($name,$id);
	}

	/**
	 * @return boolean whether this widget is associated with a data model.
	 */
	protected function hasModel()
	{
		return $this->model instanceof CModel && $this->attribute!==null;
	}
}
