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
 * CForm represents a form object that contains form input specifications.
 *
 * The main purpose of introducing the abstraction of form objects is to enhance the
 * reusability of forms. In particular, we can divide a form in two parts: those
 * that specify each individual form inputs, and those that decorate the form inputs.
 * A CForm object represents the former part. It relies on the rendering process to
 * accomplish form input decoration. Reusability is mainly achieved in the rendering process.
 * That is, a rendering process can be reused to render different CForm objects.
 *
 * A form can be rendered in different ways. One can call the {@link render} method
 * to get a quick form rendering without writing any HTML code; one can also override
 * {@link render} to render the form in a different layout; and one can use an external
 * view template to render each form element explicitly. In these ways, the {@link render}
 * method can be applied to all kinds of forms and thus achieves maximum reusability;
 * while the external view template keeps maximum flexibility in rendering complex forms.
 *
 * Form input specifications are organized in terms of a form element hierarchy.
 * At the root of the hierarchy, it is the root CForm object. The root form object maintains
 * its children in two collections: {@link elements} and {@link buttons}.
 * The former contains non-button form elements ({@link CFormStringElement},
 * {@link CFormInputElement} and CForm); while the latter mainly contains
 * button elements ({@link CFormButtonElement}). When a CForm object is embedded in the
 * {@link elements} collection, it is called a sub-form which can have its own {@link elements}
 * and {@link buttons} collections and thus form the whole form hierarchy.
 *
 * Sub-forms are mainly used to handle multiple models. For example, in a user
 * registration form, we can have the root form to collect input for the user
 * table while a sub-form to collect input for the profile table. Sub-form is also
 * a good way to partition a lengthy form into shorter ones, even though all inputs
 * may belong to the same model.
 *
 * Form input specifications are given in terms of a configuration array which is
 * used to initialize the property values of a CForm object. The {@link elements} and
 * {@link buttons} properties need special attention as they are the main properties
 * to be configured. To configure {@link elements}, we should give it an array like
 * the following:
 * <pre>
 * 'elements'=>array(
 *     'username'=>array('type'=>'text', 'maxlength'=>80),
 *     'password'=>array('type'=>'password', 'maxlength'=>80),
 * )
 * </pre>
 * The above code specifies two input elements: 'username' and 'password'. Note the model
 * object must have exactly the same attributes 'username' and 'password'. Each element
 * has a type which specifies what kind of input should be used. The rest of the array elements
 * (e.g. 'maxlength') in an input specification are rendered as HTML element attributes
 * when the input field is rendered. The {@link buttons} property is configured similarly.
 *
 * For more details about configuring form elements, please refer to {@link CFormInputElement}
 * and {@link CFormButtonElement}.
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
	/**
	 * @var array attribute values for the form tag
	 */
	public $attributes=array('class'=>'form');

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
	 * Validates the models associated with this form.
	 * All models, including those associated with sub-forms, will perform
	 * the validation. You may use {@link CModel::getErrors()} to retrieve the validation
	 * error messages.
	 * @return boolean whether all models are valid
	 */
	public function validate()
	{
		$ret=true;
		if($this->_model!==null)
			$ret=$this->_model->validate() && $ret;
		foreach($this->getElements() as $element)
		{
			if($element instanceof self)
				$ret=$element->validate() && $ret;
		}
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
	 * by which the form is created and rendered.
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
			return "<div style=\"visibility:hidden\">\n".$output.'</div>';
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
			$output.="<fieldset>\n<legend>\n".$this->legend."</legend>\n";

		if($this->description!==null)
			$output.="<div class=\"description\">\n".$this->description."</div>\n";

		$output.=CHtml::errorSummary($this->getModel());

		foreach($this->getElements() as $element)
		{
			if($element->getVisible())
			{
				if($element instanceof self)
					$output.=$element->renderBody();
				else if($element instanceof CFormInputElement)
					$output.="<div class=\"row {$element->name}\">\n".$element."</div>\n";
				else
					$output.=$element;
			}
		}

		$buttons='';
		foreach($this->getButtons() as $button)
		{
			if($button->getVisible())
				$buttons.=$button."\n";
		}
		if($buttons!=='')
			$output.="<div class=\"row buttons\">".$buttons."</div>\n";

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
