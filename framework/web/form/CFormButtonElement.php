<?php
/**
 * CFormButtonElement class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFormButtonElement represents a form button element.
 *
 * CFormButtonElement can represent the following types of button based on {@link type} property:
 * <ul>
 * <li>submit: a submit button generated using {@link CHtml::submitButton}</li>
 * <li>button: a normal button generated using {@link CHtml::button}</li>
 * <li>image: an image button generated using {@link CHtml::imageButton}</li>
 * <li>reset: a reset button generated using {@link CHtml::resetButton}</li>
 * <li>link: a link button generated using {@link CHtml::linkButton}</li>
 * </ul>
 * The {@link type} property can also be a class name or a path alias to the class. In this case,
 * the button is generated using a widget of the specified class. Note, the widget must
 * have a property called "name".
 *
 * Because CFormElement is an ancestor class of CFormButtonElement, a value assigned to a non-existing property will be
 * stored in {@link attributes} which will be passed as HTML attribute values to the {@link CHtml} method
 * generating the button or initial values of the widget properties.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.form
 * @since 1.1
 */
class CFormButtonElement extends CFormElement
{
	/**
	 * @var array Core button types (alias=>CHtml method name)
	 */
	public static $coreTypes=array(
		'submit'=>'submitButton',
		'button'=>'button',
		'image'=>'imageButton',
		'reset'=>'resetButton',
		'link'=>'linkButton',
	);

	/**
	 * @var string the type of this button. This can be a class name, a path alias of a class name,
	 * or a button type alias (submit, button, image, reset, or link).
	 */
	public $type;
	/**
	 * @var string name of this button
	 */
	public $name;
	/**
	 * @var string the label of this button. This property is ignored when a widget is used to generate the button.
	 */
	public $label;

	private $_on;

	/**
	 * Evaluates the visibility of this element.
	 * This method will check the {@link on} property to see if
	 * the model is in a scenario that should have this string displayed.
	 * @return boolean whether this element is visible.
	 */
	protected function evaluateVisible()
	{
		return empty($this->_on) || in_array($this->getParent()->getModel()->getScenario(),$this->_on);
	}

	/**
	 * Returns a value indicating under which scenarios this button is visible.
	 * If the value is empty, it means the button is visible under all scenarios.
	 * Otherwise, only when the model is in the scenario whose name can be found in
	 * this value, will the button be visible. See {@link CModel::scenario} for more
	 * information about model scenarios.
	 * @return string scenario names separated by commas. Defaults to null.
	 */
	public function getOn()
	{
		return $this->_on;
	}

	/**
	 * @param string scenario names separated by commas.
	 */
	public function setOn($value)
	{
		$this->_on=preg_split('/[\s,]+/',$value,-1,PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Returns this button.
	 * @return string the rendering result
	 */
	public function render()
	{
		$attributes=$this->attributes;
		if(isset(self::$coreTypes[$this->type]))
		{
			$method=self::$coreTypes[$this->type];
			if($method==='linkButton')
			{
				if(!isset($attributes['params'][$this->name]))
					$attributes['params'][$this->name]=1;
				return CHtml::linkButton($this->label,$attributes);
			}
			$attributes['name']=$this->name;
			if($method==='imageButton')
				return CHtml::imageButton(isset($attributes['src']) ? $attributes['src'] : '',$attributes);
			else
				return CHtml::$method($this->label,$attributes);
		}
		else
		{
			$attributes['name']=$this->name;
			ob_start();
			$this->getParent()->getOwner()->widget($this->type, $attributes);
			return ob_get_clean();
		}
	}
}
