<?php
/**
 * CFormInputElement class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFormInputElement represents form input element.
 *
 * CFormInputElement can represent the following types of form input based on {@link type} property:
 * <ul>
 * <li>text: a normal text input generated using {@link CHtml::activeTextField}</li>
 * <li>hidden: a hidden input generated using {@link CHtml::activeHiddenField}</li>
 * <li>password: a password input generated using {@link CHtml::activePasswordField}</li>
 * <li>textarea: a text area generated using {@link CHtml::activeTextArea}</li>
 * <li>file: a file input generated using {@link CHtml::activeFileField}</li>
 * <li>radio: a radio button generated using {@link CHtml::activeRadioButton}</li>
 * <li>checkbox: a check box generated using {@link CHtml::activeCheckBox}</li>
 * <li>listbox: a list box generated using {@link CHtml::activeListBox}</li>
 * <li>dropdownlist: a drop-down list generated using {@link CHtml::activeDropDownList}</li>
 * <li>checkboxlist: a list of check boxes generated using {@link CHtml::activeCheckBoxList}</li>
 * <li>radiolist: a list of radio buttons generated using {@link CHtml::activeRadioButtonList}</li>
 * <li>url: an HTML5 url input generated using {@link CHtml::activeUrlField}</li>
 * <li>email: an HTML5 email input generated using {@link CHtml::activeEmailField}</li>
 * <li>number: an HTML5 number input generated using {@link CHtml::activeNumberField}</li>
 * <li>range: an HTML5 range input generated using {@link CHtml::activeRangeField}</li>
 * <li>date: an HTML5 date input generated using {@link CHtml::activeDateField}</li>
 * </ul>
 * The {@link type} property can also be a class name or a path alias to the class. In this case,
 * the input is generated using a widget of the specified class. Note, the widget must
 * have a property called "model" which expects a model object, and a property called "attribute"
 * which expects the name of a model attribute.
 *
 * Because CFormElement is an ancestor class of CFormInputElement, a value assigned to a non-existing property will be
 * stored in {@link attributes} which will be passed as HTML attribute values to the {@link CHtml} method
 * generating the input or initial values of the widget properties.
 *
 * @property boolean $required Whether this input is required.
 * @property string $label The label for this input. If the label is not manually set,
 * this method will call {@link CModel::getAttributeLabel} to determine the label.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.form
 * @since 1.1
 */
class CFormInputElement extends CFormElement
{
	/**
	 * @var array Core input types (alias=>CHtml method name)
	 */
	public static $coreTypes=array(
		'text'=>'activeTextField',
		'hidden'=>'activeHiddenField',
		'password'=>'activePasswordField',
		'textarea'=>'activeTextArea',
		'file'=>'activeFileField',
		'radio'=>'activeRadioButton',
		'checkbox'=>'activeCheckBox',
		'listbox'=>'activeListBox',
		'dropdownlist'=>'activeDropDownList',
		'checkboxlist'=>'activeCheckBoxList',
		'radiolist'=>'activeRadioButtonList',
		'url'=>'activeUrlField',
		'email'=>'activeEmailField',
		'number'=>'activeNumberField',
		'range'=>'activeRangeField',
		'date'=>'activeDateField'
	);

	/**
	 * @var string the type of this input. This can be a widget class name, a path alias of a widget class name,
	 * or an input type alias (text, hidden, password, textarea, file, radio, checkbox, listbox, dropdownlist, checkboxlist, or radiolist).
	 * If a widget class, it must extend from {@link CInputWidget} or (@link CJuiInputWidget).
	 */
	public $type;
	/**
	 * @var string name of this input
	 */
	public $name;
	/**
	 * @var string hint text of this input
	 */
	public $hint;
	/**
	 * @var array the options for this input when it is a list box, drop-down list, check box list, or radio button list.
	 * Please see {@link CHtml::listData} for details of generating this property value.
	 */
	public $items=array();
	/**
	 * @var array the options used when rendering the error part. This property will be passed
	 * to the {@link CActiveForm::error} method call as its $htmlOptions parameter.
	 * @see CActiveForm::error
	 * @since 1.1.1
	 */
	public $errorOptions=array();
	/**
	 * @var boolean whether to allow AJAX-based validation for this input. Note that in order to use
	 * AJAX-based validation, {@link CForm::activeForm} must be configured with 'enableAjaxValidation'=>true.
	 * This property allows turning on or off  AJAX-based validation for individual input fields.
	 * Defaults to true.
	 * @since 1.1.7
	 */
	public $enableAjaxValidation=true;
	/**
	 * @var boolean whether to allow client-side validation for this input. Note that in order to use
	 * client-side validation, {@link CForm::activeForm} must be configured with 'enableClientValidation'=>true.
	 * This property allows turning on or off  client-side validation for individual input fields.
	 * Defaults to true.
	 * @since 1.1.7
	 */
	public $enableClientValidation=true;
	/**
	 * @var string the layout used to render label, input, hint and error. They correspond to the placeholders
	 * "{label}", "{input}", "{hint}" and "{error}".
	 */
	public $layout="{label}\n{input}\n{hint}\n{error}";

	private $_label;
	private $_required;

	/**
	 * Gets the value indicating whether this input is required.
	 * If this property is not set explicitly, it will be determined by calling
	 * {@link CModel::isAttributeRequired} for the associated model and attribute of this input.
	 * @param $index mixed
	 * @return boolean whether this input is required.
	 */
	public function getRequired($index=null)
	{
		if($this->_required!==null)
			return $this->_required;
		else
		{
			$model=$this->getParent()->getModel();

			if($index===null)
				return $model->isAttributeRequired($this->name);
			else
				return $model[$index]->isAttributeRequired($this->name);
		}
	}

	/**
	 * @param boolean $value whether this input is required.
	 */
	public function setRequired($value)
	{
		$this->_required=$value;
	}

	/**
	 * @param $index mixed
	 * @return string the label for this input. If the label is not manually set,
	 * this method will call {@link CModel::getAttributeLabel} to determine the label.
	 */
	public function getLabel($index=null)
	{
		if($this->_label!==null)
			return $this->_label;
		else
		{
			$model=$this->getParent()->getModel();

			if($index===null)
				return $model->getAttributeLabel($this->name);
			else
				return $model[$index]->getAttributeLabel($this->name);
		}
	}

	/**
	 * @param string $value the label for this input
	 */
	public function setLabel($value)
	{
		$this->_label=$value;
	}

	/**
	 * Renders everything for this input.
	 * The default implementation simply returns the result of {@link renderLabel}, {@link renderInput},
	 * {@link renderHint}. When {@link CForm::showErrorSummary} is false, {@link renderError} is also called
	 * to show error messages after individual input fields.
	 * @param $index mixed
	 * @return string the complete rendering result for this input, including label, input field, hint, and error.
	 */
	public function render($index=null)
	{
		if($this->type==='hidden')
			return $this->renderInput($index);

		$output=array(
			'{label}'=>$this->renderLabel($index),
			'{input}'=>$this->renderInput($index),
			'{hint}'=>$this->renderHint(),
			'{error}'=>$this->getParent()->showErrorSummary ? '' : $this->renderError($index),
		);

		return strtr($this->layout,$output);
	}

	/**
	 * Renders the label for this input.
	 * The default implementation returns the result of {@link CHtml activeLabelEx}.
	 * @param $index mixed
	 * @return string the rendering result
	 */
	public function renderLabel($index=null)
	{
		$options = array(
			'label'=>$this->getLabel($index),
			'required'=>$this->getRequired($index)
		);

		if(!empty($this->attributes['id']))
        {
            $options['for'] = $this->attributes['id'];
        }

		$model=$this->getParent()->getModel();

		if($index===null)
			return CHtml::activeLabel($model, $this->name, $options);
		else
			return CHtml::activeLabel($model[$index], "[{$index}]{$this->name}", $options);
	}

	/**
	 * Renders the input field.
	 * The default implementation returns the result of the appropriate CHtml method or the widget.
	 * @param $index mixed
	 * @return string the rendering result
	 */
	public function renderInput($index=null) //TODO: check
	{
		$model=$this->getParent()->getModel();

		if(isset(self::$coreTypes[$this->type]))
		{
			$method=self::$coreTypes[$this->type];
			if(strpos($method,'List')!==false)
			{
				if($index===null)
					return CHtml::$method($model, $this->name, $this->items, $this->attributes);
				else
					return CHtml::$method($model[$index], "[{$index}]{$this->name}", $this->items, $this->attributes);
			}
			else
			{
				if($index===null)
					return CHtml::$method($model, $this->name, $this->attributes);
				else
					return CHtml::$method($model[$index], "[{$index}]{$this->name}", $this->attributes);
			}
		}
		else
		{
			$attributes=$this->attributes;

			if($index===null)
			{
				$attributes['model']=$model;
				$attributes['attribute']=$this->name;
			}
			else
			{
				$attributes['model']=$model[$index];
				$attributes['attribute']="[{$index}]{$this->name}";
			}

			ob_start();
			$this->getParent()->getOwner()->widget($this->type, $attributes);
			return ob_get_clean();
		}
	}

	/**
	 * Renders the error display of this input.
	 * The default implementation returns the result of {@link CHtml::error}
	 * @param $index mixed
	 * @return string the rendering result
	 */
	public function renderError($index=null)
	{
		$parent=$this->getParent();
		$model=$parent->getModel();

		if($index===null)
			return $parent->getActiveFormWidget()->error($model, $this->name, $this->errorOptions, $this->enableAjaxValidation, $this->enableClientValidation);
		else
			return $parent->getActiveFormWidget()->error($model[$index], "[{$index}]{$this->name}", $this->errorOptions, $this->enableAjaxValidation, $this->enableClientValidation);
	}

	/**
	 * Renders the hint text for this input.
	 * The default implementation returns the {@link hint} property enclosed in a paragraph HTML tag.
	 * @return string the rendering result.
	 */
	public function renderHint()
	{
		return $this->hint===null ? '' : '<div class="hint">'.$this->hint.'</div>';
	}

	/**
	 * Evaluates the visibility of this element.
	 * This method will check if the attribute associated with this input is safe for
	 * the current model scenario.
	 * @params $index mixed
	 * @return boolean whether this element is visible.
	 */
	protected function evaluateVisible($index=null)
	{
		$model=$this->getParent()->getModel();

		if($index===null)
			return $model->isAttributeSafe($this->name);
		else
			return $model[$index]->isAttributeSafe($this->name);
	}
}