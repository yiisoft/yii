<?php
/**
 * CForm class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CForm represents a form object that contains form input specification.
 *
 *
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.form
 * @since 1.1
 */
class CForm extends CFormElement implements ArrayAccess
{
	/**
	 * @var string the legend for this form. If this is set, a fieldset may be rendered
	 * around the form body with the specified legend text. Defaults to null.
	 */
	public $legend;
	/**
	 * @var string the description of this form.
	 */
	public $description;
	/**
	 * @var string the submission method of this form. Defaults to 'post'.
	 * This property is ignored when this form is a sub-form.
	 */
	public $method='post';
	/**
	 * @var mixed the form action URL (see {@link CHtml::normalizeUrl} for details about this parameter.)
	 * Defaults to an empty string, meaning the current request URL.
	 * This property is ignored when this form is a sub-form.
	 */
	public $action='';
	/**
	 * @var string the name of the class for representing a form input element. Defaults to 'CFormInputElement'.
	 */
	public $inputElementClass='CFormInputElement';
	/**
	 * @var string the name of the class for representing a form button element. Defaults to 'CFormButtonElement'.
	 */
	public $buttonElementClass='CFormButtonElement';

	private $_model;
	private $_elements;
	private $_buttons;
	private $_hiddens=array();

	/**
	 * Constructor.
	 * If you override this method, make sure you do not modify the method
	 * signature, and also make sure you call the parent implementation.
	 * @param mixed the direct parent of this form. This could be either a {@link CBaseController}
	 * object (a controller or a widget), or a {@link CForm} object.
	 * If the former, it means the form is a top-level form; if the latter, it means this form is a sub-form.
	 * @param CModel the model object associated with this form. If it is null,
	 * the parent's model will be used instead.
	 * @param mixed the configuration for this form. It can be a configuration array
	 * or the name of a PHP script file that returns a configuration array.
	 * The configuration array consists of name-value pairs that are used to initialize
	 * the properties of this form.
	 */
	public function __construct($parent,$model=null,$config=null)
	{
		$this->_model=$model;
		parent::__construct($parent,$config);
		$this->init();
	}

	/**
	 * Initializes this form.
	 * This method is invoked at the end of the constructor.
	 * You may override this method to provide customized initialization (such as
	 * configuring the form object).
	 */
	protected function init()
	{
	}

	/**
	 * Returns a value indicating whether this form is submitted.
	 * @param boolean whether to call {@link loadData} if the form is submitted so that
	 * the submitted data can be populated to the associated models.
	 * @return boolean whether this form is submitted.
	 * @see loadData
	 */
	public function submitted($loadData=true)
	{
		$ret=$this->clicked($this->getUniqueId());
		if($ret && $loadData)
			$this->loadData();
		return $ret;
	}

	/**
	 * Loads the submitted data into the associated model(s) to the form.
	 * This method will go through all models associated with this form and its sub-forms
	 * and massively assign the submitted data to the models.
	 * @see submitted
	 */
	public function loadData()
	{
		if($this->_model!==null)
		{
			$class=get_class($this->_model);
			if($this->getRoot()->method==='get')
			{
				if(isset($_GET[$class]))
					$this->_model->setAttributes($_GET[$class]);
			}
			else if(isset($_POST[$class]))
				$this->_model->setAttributes($_POST[$class]);
		}
		foreach($this->getElements() as $element)
		{
			if($element instanceof self)
				$element->loadData();
		}
	}

	/**
	 * Returns a value indicating whether the specified button is clicked.
	 * @param string the button name
	 * @return boolean whether the button is clicked.
	 */
	public function clicked($name)
	{
		if($this->getRoot()->method==='get')
			return isset($_GET[$name]);
		else
			return isset($_POST[$name]);
	}

	/**
	 * @return CForm the top-level form object
	 */
	public function getRoot()
	{
		$root=$this;
		while($root->getParent() instanceof self)
			$root=$root->getParent();
		return $root;
	}

	/**
	 * @return CBaseController the owner of this form. This refers to either a controller or a widget
	 * in which the form is created.
	 */
	public function getOwner()
	{
		$owner=$this->getParent();
		while($owner instanceof self)
			$owner=$owner->getParent();
		return $owner;
	}

	/**
	 * @return CModel the model associated with this form. If this form does not have a model,
	 * it will look for a model in its ancestors.
	 */
	public function getModel()
	{
		$form=$this;
		while($form->_model===null && $form->getParent() instanceof self)
			$form=$form->getParent();
		return $form->_model;
	}

	/**
	 * @param CModel the model to be associated with this form
	 */
	public function setModel($model)
	{
		$this->_model=$model;
	}

	/**
	 * Returns the input elements of this form.
	 * This includes text strings, input elements and sub-forms.
	 * Note that the returned result is a {@link CFormElementCollection} object, which
	 * means you can use it like an array. For more details, see {@link CMap}.
	 * @return CFormElementCollection the form elements.
	 */
	public function getElements()
	{
		if($this->_elements===null)
			$this->_elements=new CFormElementCollection($this,false);
		return $this->_elements;
	}

	/**
	 * Configures the input elements of this form.
	 * The configuration must be an array of input configuration array indexed by input name.
	 * Each input configuration array consists of name-value pairs that are used to initialize
	 * a {@link CFormStringElement} object (when 'type' is 'string'), a {@link CFormElement} object
	 * (when 'type' is a string ending with 'Form'), or a {@link CFormInputElement} object in
	 * all other cases.
	 * @param array the button configurations
	 */
	public function setElements($elements)
	{
		$collection=$this->getElements();
		foreach($elements as $name=>$config)
			$collection->add($name,$config);
	}

	/**
	 * Returns the button elements of this form.
	 * Note that the returned result is a {@link CFormElementCollection} object, which
	 * means you can use it like an array. For more details, see {@link CMap}.
	 * @return CFormElementCollection the form elements.
	 */
	public function getButtons()
	{
		if($this->_buttons===null)
			$this->_buttons=new CFormElementCollection($this,true);
		return $this->_buttons;
	}

	/**
	 * Configures the buttons of this form.
	 * The configuration must be an array of button configuration array indexed by button name.
	 * Each button configuration array consists of name-value pairs that are used to initialize
	 * a {@link CFormButtonElement} object.
	 * @param array the button configurations
	 */
	public function setButtons($buttons)
	{
		$collection=$this->getButtons();
		foreach($buttons as $name=>$config)
			$collection->add($name,$config);
	}

	/**
	 * Returns the hidden elements of this form.
	 * The hidden elements are obtained from {@link elements} collection.
	 * Only the currently "renderable" elements are returned.
	 * @return array the "renderable" hidden elements of this form.
	 */
	public function getHiddenElements()
	{
		$model=$this->getModel();
		$elements=array();
		foreach($this->_hiddens as $name=>$element)
		{
			if($model->isAttributeSafe($name))
				$elements[$name]=$element;
		}
		return $elements;
	}

	/**
	 * Renders the form.
	 * If this is a sub-form, only the {@link renderBody} will be rendered.
	 * Otherwise, the whole form will be rendered, including the open and close tag.
	 * @return string the rendering result
	 */
	public function render()
	{
		if($this->getParent() instanceof self) // is a sub-form
			return $this->renderBody();
		else
			return $this->renderBegin() . $this->renderBody() . $this->renderEnd();
	}

	/**
	 * Renders the open tag of the form.
	 * The default implementation will render the open tag.
	 * @return string the rendering result
	 */
	public function renderBegin()
	{
		return CHtml::beginForm($this->action,$this->method,$this->attributes);
	}

	/**
	 * Renders the close tag of the form.
	 * @return string the rendering result
	 */
	public function renderEnd()
	{
		return CHtml::endForm();
	}

	/**
	 * Renders the hidden fields that this form has.
	 */
	public function renderHiddenFields()
	{
		$output=$this->getParent() instanceof self ? '' : CHtml::hiddenField($this->getUniqueID(),1)."\n";
		$model=$this->getModel();
		foreach($this->getHiddenElements() as $element)
			$output.=CHtml::activeHiddenField($model,$element->name,$element->attributes)."\n";
		if($output!=='')
			return "<div style=\"visibility:none\">\n".$output.'</div>';
		else
			return '';
	}

	/**
	 * Renders the body content of this form.
	 * The form tag will not be rendered. Please call {@link renderBegin} and {@link renderEnd}
	 * to render the open and close tags of the form.
	 * You may override this method to customize the rendering of the form.
	 * @return string the rendering result
	 */
	public function renderBody()
	{
		$output=$this->renderHiddenFields();

		if($this->legend!==null)
			$output.="\n<fieldset>\n<legend>\n".$this->legend."</legend>\n";
		$output.=CHtml::errorSummary($this->getModel());
		$output.="<ul>\n";
		foreach($this->getElements() as $element)
		{
			if($element->getVisible())
			{
				$output.="<li>";
				$output.=$element;
				$output.="</li>\n";
			}
		}
		foreach($this->getButtons() as $button)
		{
			if($button->getVisible())
			{
				$output.="<li>";
				$output.=$button;
				$output.="</li>\n";
			}
		}
		$output.="</ul>\n";
		if($this->legend!==null)
			$output.="</fieldset>\n";
		return $output;
	}

	/**
	 * This method is called after an element is added to the element collection.
	 * @param string the name of the element
	 * @param CFormElement the element that is added
	 * @param boolean whether the element is added to the {@link buttons} collection
	 */
	public function addedElement($name,$element,$forButtons)
	{
		if($element instanceof CFormInputElement && $element->type==='hidden')
			$this->_hiddens[$name]=$element;
	}

	/**
	 * This method is called after an element is removed from the element collection.
	 * @param string the name of the element
	 * @param CFormElement the element that is removed
	 * @param boolean whether the element is removed from the {@link buttons} collection
	 */
	public function removedElement($name,$element,$forButtons)
	{
		if($element instanceof CFormInputElement && $element->type==='hidden')
			unset($this->_hiddens[$name]);
	}

	/**
	 * Evaluates the visibility of this form.
	 * This method will check the visibility of the {@link elements}.
	 * If any one of them is visible, the form is considered as visible. Otherwise, it is invisible.
	 * @return boolean whether this form is visible.
	 */
	protected function evaluateVisible()
	{
		foreach($this->getElements() as $element)
			if($element->getVisible())
				return true;
		return false;
	}

	/**
	 * Returns a unique ID that identifies this form in the current page.
	 * @return string the unique ID identifying this form
	 */
	protected function getUniqueId()
	{
		if(isset($this->attributes['id']))
			return 'yform_'.$this->attributes['id'];
		else
			return 'yform_'.sprintf('%x',crc32(serialize(array_keys($this->getElements()->toArray()))));
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $this->getElements()->contains($offset);
	}

	/**
	 * Returns the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return $this->getElements()->itemAt($offset);
	}

	/**
	 * Sets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer the offset to set element
	 * @param mixed the element value
	 */
	public function offsetSet($offset,$item)
	{
		$this->getElements()->add($offset,$item);
	}

	/**
	 * Unsets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		$this->getElements()->remove($offset);
	}
}
