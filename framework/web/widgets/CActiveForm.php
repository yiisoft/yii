<?php
/**
 * CActiveForm class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CActiveForm provides a set of methods that can facilitate creating a form associated with some data models.
 *
 * CActiveForm implements a set of wrapper methods that call the corresponding
 * 'active' methods in {@link CHtml}. For example, the {@link textField} method
 * is a wrapper of {@link CHtml::activeTextField}.
 *
 * The 'beginWidget' and 'endWidget' call of CActiveForm widget will render
 * the open and close form tags. Anything in between are rendered as form content
 * (such as input fields, labels). We can call the wrapper methods of CActiveForm
 * to generate these form contents. For example, calling {@link CActiveForm::textField},
 * which is a wrapper of {@link CHtml::activeTextField}, would generate an input field
 * for a specified model attribute.
 *
 * Besides the wrapper methods, CActiveForm also implements an important feature
 * known as AJAX validation. This feature may be turned on setting {@link enableAjaxValidation}
 * to be true. When the user enters some value in an input field, the AJAX validation
 * feature would trigger an AJAX request to the server to call for validating the model
 * with the current user inputs. If there are any validation errors, the corresponding
 * error messages will show up next to the input fields immediately.
 *
 * The AJAX validation feature may greatly improve the user experience at entering
 * data into a form. Because the validation is done on the server side using the rules
 * defined in the data model, no extra javascript code needs to be written.
 * More importantly, and the validation result is consistent with the server-side validation.
 * And in case when the user turns off javascript in his browser, it automatically
 * falls back to traditional validation via whole page submission.
 *
 * To use CActiveForm with AJAX validation, one needs to write both the view code
 * and the controller action code.
 *
 * The following is a piece of sample view code:
 * <pre>
 * <?php $form = $this->beginWidget('CActiveForm', array(
 *     'id'=>'user-form',
 *     'enableAjaxValidation'=>true,
 *     'focus'=>array($model,firstName),
 * )); ?>
 *
 * <?php echo $form->errorSummary($model); ?>
 *
 * <div class="row">
 *     <?php echo $form->labelEx($model,'firstName'); ?>
 *     <?php echo $form->textField($model,'firstName'); ?>
 *     <?php echo $form->error($model,'firstName'); ?>
 * </div>
 * <div class="row">
 *     <?php echo $form->labelEx($model,'lastName'); ?>
 *     <?php echo $form->textField($model,'lastName'); ?>
 *     <?php echo $form->error($model,'lastName'); ?>
 * </div>
 *
 * <?php $this->endWidget(); ?>
 * </pre>
 *
 * To respond to the AJAX validation requests, we need the following class code:
 * <pre>
 * public function actionCreate()
 * {
 *     $model=new User;
 *     $this->performAjaxValidation($model);
 *     if(isset($_POST['User']))
 *     {
 *         $model->attributes=$_POST['User'];
 *         if($model->save())
 *             $this->redirect('index');
 *     }
 *     $this->render('create',array('model'=>$model));
 * }
 *
 * protected function performAjaxValidation($model)
 * {
 *     if(isset($_POST['ajax']) && $_POST['ajax']==='user-form')
 *     {
 *         echo CActiveForm::validate($model);
 *         Yii::app()->end();
 *     }
 * }
 * </pre>
 * The method <code>performAjaxValidation</code> is the main extra code we add to our
 * traditional model creation action code. In this method, we check if the request
 * is submitted via AJAX by the 'user-form'. If so, we validate the model and return
 * the validation results. We may call the same method in model update action.
 *
 * On the client side, an input field may be in one of the four states: initial (not validated),
 * validating, error and success. To differentiate these states, CActiveForm automatically
 * assigns different CSS classes for the last three states to the HTML element containing the input field.
 * By default, these CSS classes are named as 'validating', 'error' and 'success', respectively.
 * They may be changed by configuring the {@link options} property or specifying in the {@link error} method.
 *
 * Sometimes, we may want to limit the AJAX validation to certain model attributes only.
 * This can be achieved by setting the model with a scenario that is specific for AJAX validation.
 * Then only list those attributes that need AJAX validation in the scenario in {@link CModel::rules()} declaration.
 *
 * There are some limitations of CActiveForm regarding to its AJAX validation support.
 * First, it does not validate with file upload fields.
 * Second, it should not be used to perform validations that may cause server-side state change.
 * For example, it is not suitable to perform CAPTCHA validation done by {@link CCaptchAction}
 * because each validation request will increase the number of tests by one. Third, it is not designed
 * to work with tabular data input for the moment.
 *
 * Because CActiveForm relies on submitting the whole form in AJAX mode to perform the validation,
 * if the form has a lot of data to submit, the performance may not be good. In this case,
 * you should design your own lightweight AJAX validation.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.widgets
 * @since 1.1.1
 */
class CActiveForm extends CWidget
{
	/**
	 * @var mixed the form action URL (see {@link CHtml::normalizeUrl} for details about this parameter).
	 * If not set, the current page URL is used.
	 */
	public $action='';
	/**
	 * @var string the form submission method. This should be either 'post' or 'get'.
	 * Defaults to 'post'.
	 */
	public $method='post';
	/**
	 * @var boolean whether to generate a stateful form (See {@link CHtml::statefulForm}). Defaults to false.
	 */
	public $stateful=false;
	/**
	 * @var string the CSS class name for error messages. Defaults to 'errorMessage'.
	 * Individual {@link error} call may override this value by specifying the 'class' HTML option.
	 */
	public $errorMessageCssClass='errorMessage';
	/**
	 * @var array additional HTML attributes that should be rendered for the form tag.
	 */
	public $htmlOptions=array();
	/**
	 * @var array the options to be passed to the javascript validation plugin.
	 * The following options are supported:
	 * <ul>
	 * <li>ajaxVar: string, the name of the parameter indicating the request is an AJAX request.
	 * When the AJAX validation is triggered, a parameter named as this property will be sent
	 * together with the other form data to the server. The parameter value is the form ID.
	 * The server side can then detect who triggers the AJAX validation and react accordingly.
	 * Defaults to 'ajax'.</li>
	 * <li>validationUrl: string, the URL that performs the AJAX validations.
	 * If not set, it will take the value of {@link action}.</li>
	 * <li>validationDelay: integer, the number of milliseconds that an AJAX validation should be
	 * delayed after an input is changed. A value 0 means the validation will be triggered immediately
	 * when an input is changed. A value greater than 0 means changing several inputs may only
	 * trigger a single validation if they happen fast enough, which may help reduce the server load.
	 * Defaults to 200 (0.2 second).</li>
	 * <li>validateOnSubmit: boolean, whether to perform AJAX validation when the form is being submitted.
	 * If there are any validation errors, the form submission will be stopped.
	 * Defaults to false.</li>
	 * <li>validateOnChange: boolean, whether to trigger an AJAX validation
	 * each time when an input's value is changed.	You may want to turn this off
	 * if it causes too much performance impact, because each AJAX validation request
	 * will submit the data of the whole form. Defaults to true.</li>
	 * <li>validateOnType: boolean, whether to trigger an AJAX validation each time when the user
	 * presses a key. When setting this property to be true, you should tune up the 'validationDelay'
	 * option to avoid triggering too many AJAX validations. Defaults to false.</li>
	 * <li>hideErrorMessage: boolean, whether to hide the error message even if there is an error.
	 * Defaults to false, which means the error message will show up whenever the input has an error.</li>
	 * <li>inputContainer: string, the jQuery selector for the HTML element containing the input field.
	 * During the validation process, CActiveForm will set different CSS class for the container element
	 * to indicate the state change. If not set, it means the closest 'div' element that contains the input field.</li>
	 * <li>errorCssClass: string, the CSS class to be assigned to the container whose associated input
	 * has AJAX validation error. Defaults to 'error'.</li>
	 * <li>successCssClass: string, the CSS class to be assigned to the container whose associated input
	 * passes AJAX validation without any error. Defaults to 'success'.</li>
	 * <li>validatingCssClass: string, the CSS class to be assigned to the container whose associated input
	 * is currently being validated via AJAX. Defaults to 'validating'.</li>
	 * <li>errorMessageCssClass: string, the CSS class assigned to the error messages returned
	 * by AJAX validations. Defaults to 'errorMessage'.</li>
	 * <li>beforeValidate: function, the function that will be invoked before performing ajax-based validation
	 * triggered by form submission action (available only when validateOnSubmit is set true).
	 * The expected function signature should be <code>beforeValidate(form) {...}</code>, where 'form' is
	 * the jquery representation of the form object. If the return value of this function is NOT true, the validation
	 * will be cancelled.
	 *
	 * Note that because this option refers to a js function, you should prefix the value with 'js:' to prevent it
	 * from being encoded as a string. This option has been available since version 1.1.3.</li>
	 * <li>afterValidate: function, the function that will be invoked after performing ajax-based validation
	 * triggered by form submission action (available only when validateOnSubmit is set true).
	 * The expected function signature should be <code>afterValidate(form, data, hasError) {...}</code>, where 'form' is
	 * the jquery representation of the form object; 'data' is the JSON response from the server-side validation; 'hasError'
	 * is a boolean value indicating whether there is any validation error. If the return value of this function is NOT true,
	 * the normal form submission will be cancelled.
	 *
	 * Note that because this option refers to a js function, you should prefix the value with 'js:' to prevent it
	 * from being encoded as a string. This option has been available since version 1.1.3.</li>
	 * <li>beforeValidateAttribute: function, the function that will be invoked before performing ajax-based validation
	 * triggered by a single attribute input change. The expected function signature should be
	 * <code>beforeValidateAttribute(form, attribute) {...}</code>, where 'form' is the jquery representation of the form object
	 * and 'attribute' refers to the js options for the triggering attribute (see {@link error}).
	 * If the return value of this function is NOT true, the validation will be cancelled.
	 *
	 * Note that because this option refers to a js function, you should prefix the value with 'js:' to prevent it
	 * from being encoded as a string. This option has been available since version 1.1.3.</li>
	 * <li>afterValidateAttribute: function, the function that will be invoked after performing ajax-based validation
	 * triggered by a single attribute input change. The expected function signature should be
	 * <code>beforeValidateAttribute(form, attribute, data, hasError) {...}</code>, where 'form' is the jquery
	 * representation of the form object; 'attribute' refers to the js options for the triggering attribute (see {@link error});
	 * 'data' is the JSON response from the server-side validation; 'hasError' is a boolean value indicating whether
	 * there is any validation error.
	 *
	 * Note that because this option refers to a js function, you should prefix the value with 'js:' to prevent it
	 * from being encoded as a string. This option has been available since version 1.1.3.</li>
	 * </ul>
	 *
	 * Some of the above options may be overridden in individual calls of {@link error()}.
	 * They include: validationDelay, validateOnChange, validateOnType, hideErrorMessage,
	 * inputContainer, errorCssClass, successCssClass, validatingCssClass, beforeValidateAttribute, afterValidateAttribute.
	 */
	public $clientOptions=array();
	/**
	 * @var boolean whether to enable data validation via AJAX. Defaults to false.
	 * When this property is set true, you should respond to the AJAX validation request on the server side as shown below:
	 * <pre>
	 * public function actionCreate()
	 * {
	 *     $model=new User;
	 *     if(isset($_POST['ajax']) && $_POST['ajax']==='user-form')
	 *     {
	 *         echo CActiveForm::validate($model);
	 *         Yii::app()->end();
	 *     }
	 *     ......
	 * }
	 * </pre>
 	 */
	public $enableAjaxValidation=false;

	/**
	 * @var mixed form element to get initial input focus on page load.
	 *
	 * Defaults to null meaning no input field has a focus.
	 * If set as array, first element should be model and second element should be the attribute.
	 * If set as string any jQuery selector can be used
	 *
	 * Example - set input focus on page load to:
	 * <ul>
	 * <li>'focus'=>array($model,'username') - $model->username input filed</li>
	 * <li>'focus'=>'#'.CHtml::activeId($model,'username') - $model->username input field</li>
	 * <li>'focus'=>'#LoginForm_username' - input field with ID LoginForm_username</li>
	 * <li>'focus'=>'input[type="text"]:first' - first input element of type text</li>
	 * <li>'focus'=>'input:visible:enabled:first' - first visible and enabled input element</li>
	 * <li>'focus'=>'input:text[value=""]:first' - first empty input</li>
	 * </ul>
	 *
	 * @since 1.1.4
	 */
	public $focus;

	private $_attributes=array();
	private $_summary;

	/**
	 * Initializes the widget.
	 * This renders the form open tag.
	 */
	public function init()
	{
		$this->htmlOptions['id']=$this->id;
		if($this->stateful)
			echo CHtml::statefulForm($this->action, $this->method, $this->htmlOptions);
		else
			echo CHtml::beginForm($this->action, $this->method, $this->htmlOptions);
	}

	/**
	 * Runs the widget.
	 * This registers the necessary javascript code and renders the form close tag.
	 */
	public function run()
	{
		if(is_array($this->focus))
			$this->focus="#".CHtml::activeId($this->focus[0],$this->focus[1]);

		echo CHtml::endForm();
		if(!$this->enableAjaxValidation || empty($this->_attributes))
		{
			Yii::app()->clientScript->registerScript('CActiveForm#focus',"
				if(!window.location.hash)
					$('".$this->focus."').focus();
			");
			return;
		}

		$options=$this->clientOptions;
		if(isset($this->clientOptions['validationUrl']) && is_array($this->clientOptions['validationUrl']))
			$options['validationUrl']=CHtml::normalizeUrl($this->clientOptions['validationUrl']);

		$options['attributes']=array_values($this->_attributes);

		if($this->_summary!==null)
			$options['summaryID']=$this->_summary;

		if($this->focus!==null)
				$options['focus']=$this->focus;

		$options=CJavaScript::encode($options);
		Yii::app()->clientScript->registerCoreScript('yiiactiveform');
		$id=$this->id;
		Yii::app()->clientScript->registerScript(__CLASS__.'#'.$id,"\$('#$id').yiiactiveform($options);");
	}

	/**
	 * Displays the first validation error for a model attribute.
	 * This is similar to {@link CHtml::error} except that it registers the model attribute
	 * so that if its value is changed by users, an AJAX validation may be triggered.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute name
	 * @param array $htmlOptions additional HTML attributes to be rendered in the container div tag.
	 * Besides all those options available in {@link CHtml::error}, the following options are recognized in addition:
	 * <ul>
	 * <li>validationDelay</li>
	 * <li>validateOnChange</li>
	 * <li>validateOnType</li>
	 * <li>hideErrorMessage</li>
	 * <li>inputContainer</li>
	 * <li>errorCssClass</li>
	 * <li>successCssClass</li>
	 * <li>validatingCssClass</li>
	 * <li>beforeValidateAttribute</li>
	 * <li>afterValidateAttribute</li>
	 * </ul>
	 * These options override the corresponding options as declared in {@link options} for this
	 * particular model attribute. For more details about these options, please refer to {@link clientOptions}.
	 * Note that these options are only used when {@link enableAjaxValidation} is set true.
	 * @param boolean $enableAjaxValidation whether to enable AJAX validation for the specified attribute.
	 * Note that in order to enable AJAX validation, both {@link enableAjaxValidation} and this parameter
	 * must be true.
	 * @return string the validation result (error display or success message).
	 * @see CHtml::error
	 */
	public function error($model,$attribute,$htmlOptions=array(),$enableAjaxValidation=true)
	{
		if(!$this->enableAjaxValidation || !$enableAjaxValidation)
			return CHtml::error($model,$attribute,$htmlOptions);

		$inputID=isset($htmlOptions['inputID']) ? $htmlOptions['inputID'] : CHtml::activeId($model,$attribute);
		unset($htmlOptions['inputID']);
		if(!isset($htmlOptions['id']))
			$htmlOptions['id']=$inputID.'_em_';

		$option=array(
			'inputID'=>$inputID,
			'errorID'=>$htmlOptions['id'],
			'model'=>get_class($model),
			'name'=>$attribute,
		);

		$optionNames=array(
			'validationDelay',
			'validateOnChange',
			'validateOnType',
			'hideErrorMessage',
			'inputContainer',
			'errorCssClass',
			'successCssClass',
			'validatingCssClass',
			'beforeValidateAttribute',
			'afterValidateAttribute',
		);
		foreach($optionNames as $name)
		{
			if(isset($htmlOptions[$name]))
			{
				$option[$name]=$htmlOptions[$name];
				unset($htmlOptions[$name]);
			}
		}
		if($model instanceof CActiveRecord && !$model->isNewRecord)
			$option['status']=1;

		if(!isset($htmlOptions['class']))
			$htmlOptions['class']=$this->errorMessageCssClass;
		$html=CHtml::error($model,$attribute,$htmlOptions);
		if($html==='')
		{
			if(isset($htmlOptions['style']))
				$htmlOptions['style']=rtrim($htmlOptions['style'],';').';display:none';
			else
				$htmlOptions['style']='display:none';
			$html=CHtml::tag('div',$htmlOptions,'');
		}

		$this->_attributes[$inputID]=$option;
		return $html;
	}

	/**
	 * Displays a summary of validation errors for one or several models.
	 * This method is very similar to {@link CHtml::errorSummary} except that it also works
	 * when AJAX validation is performed.
	 * @param mixed $models the models whose input errors are to be displayed. This can be either
	 * a single model or an array of models.
	 * @param string $header a piece of HTML code that appears in front of the errors
	 * @param string $footer a piece of HTML code that appears at the end of the errors
	 * @param array $htmlOptions additional HTML attributes to be rendered in the container div tag.
	 * @return string the error summary. Empty if no errors are found.
	 * @see CHtml::errorSummary
	 */
	public function errorSummary($models,$header=null,$footer=null,$htmlOptions=array())
	{
		if(!$this->enableAjaxValidation)
			return CHtml::errorSummary($models,$header,$footer,$htmlOptions);

		if(!isset($htmlOptions['id']))
			$htmlOptions['id']=$this->id.'_es_';
		$html=CHtml::errorSummary($models,$header,$footer,$htmlOptions);
		if($html==='')
		{
			if($header===null)
				$header='<p>'.Yii::t('yii','Please fix the following input errors:').'</p>';
			if(!isset($htmlOptions['class']))
				$htmlOptions['class']=CHtml::$errorSummaryCss;
			$htmlOptions['style']=isset($htmlOptions['style']) ? rtrim($htmlOptions['style'],';').';display:none' : 'display:none';
			$html=CHtml::tag('div',$htmlOptions,$header."\n<ul><li>dummy</li></ul>".$footer);
		}

		$this->_summary=$htmlOptions['id'];
		return $html;
	}

	/**
	 * Renders an HTML label for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeLabel}.
	 * Please check {@link CHtml::activeLabel} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated label tag
	 */
	public function label($model,$attribute,$htmlOptions=array())
	{
		return CHtml::activeLabel($model,$attribute,$htmlOptions);
	}

	/**
	 * Renders an HTML label for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeLabelEx}.
	 * Please check {@link CHtml::activeLabelEx} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated label tag
	 */
	public function labelEx($model,$attribute,$htmlOptions=array())
	{
		return CHtml::activeLabelEx($model,$attribute,$htmlOptions);
	}

	/**
	 * Renders a text field for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeTextField}.
	 * Please check {@link CHtml::activeTextField} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated input field
	 */
	public function textField($model,$attribute,$htmlOptions=array())
	{
		return CHtml::activeTextField($model,$attribute,$htmlOptions);
	}

	/**
	 * Renders a hidden field for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeHiddenField}.
	 * Please check {@link CHtml::activeHiddenField} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated input field
	 */
	public function hiddenField($model,$attribute,$htmlOptions=array())
	{
		return CHtml::activeHiddenField($model,$attribute,$htmlOptions);
	}

	/**
	 * Renders a password field for a model attribute.
	 * This method is a wrapper of {@link CHtml::activePasswordField}.
	 * Please check {@link CHtml::activePasswordField} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated input field
	 */
	public function passwordField($model,$attribute,$htmlOptions=array())
	{
		return CHtml::activePasswordField($model,$attribute,$htmlOptions);
	}

	/**
	 * Renders a text area for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeTextArea}.
	 * Please check {@link CHtml::activeTextArea} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated text area
	 */
	public function textArea($model,$attribute,$htmlOptions=array())
	{
		return CHtml::activeTextArea($model,$attribute,$htmlOptions);
	}

	/**
	 * Renders a file field for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeFileField}.
	 * Please check {@link CHtml::activeFileField} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes
	 * @return string the generated input field
	 */
	public function fileField($model,$attribute,$htmlOptions=array())
	{
		return CHtml::activeFileField($model,$attribute,$htmlOptions);
	}

	/**
	 * Renders a radio button for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeRadioButton}.
	 * Please check {@link CHtml::activeRadioButton} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated radio button
	 */
	public function radioButton($model,$attribute,$htmlOptions=array())
	{
		return CHtml::activeRadioButton($model,$attribute,$htmlOptions);
	}

	/**
	 * Renders a checkbox for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeCheckBox}.
	 * Please check {@link CHtml::activeCheckBox} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated check box
	 */
	public function checkBox($model,$attribute,$htmlOptions=array())
	{
		return CHtml::activeCheckBox($model,$attribute,$htmlOptions);
	}

	/**
	 * Renders a dropdown list for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeDropDownList}.
	 * Please check {@link CHtml::activeDropDownList} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data data for generating the list options (value=>display)
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated drop down list
	 */
	public function dropDownList($model,$attribute,$data,$htmlOptions=array())
	{
		return CHtml::activeDropDownList($model,$attribute,$data,$htmlOptions);
	}

	/**
	 * Renders a list box for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeListBox}.
	 * Please check {@link CHtml::activeListBox} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data data for generating the list options (value=>display)
	 * @param array $htmlOptions additional HTML attributes.
	 * @return string the generated list box
	 */
	public function listBox($model,$attribute,$data,$htmlOptions=array())
	{
		return CHtml::activeListBox($model,$attribute,$data,$htmlOptions);
	}

	/**
	 * Renders a checkbox list for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeCheckBoxList}.
	 * Please check {@link CHtml::activeCheckBoxList} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data value-label pairs used to generate the check box list.
	 * @param array $htmlOptions addtional HTML options.
	 * @return string the generated check box list
	 */
	public function checkBoxList($model,$attribute,$data,$htmlOptions=array())
	{
		return CHtml::activeCheckBoxList($model,$attribute,$data,$htmlOptions);
	}

	/**
	 * Renders a radio button list for a model attribute.
	 * This method is a wrapper of {@link CHtml::activeRadioButtonList}.
	 * Please check {@link CHtml::activeRadioButtonList} for detailed information
	 * about the parameters for this method.
	 * @param CModel $model the data model
	 * @param string $attribute the attribute
	 * @param array $data value-label pairs used to generate the radio button list.
	 * @param array $htmlOptions addtional HTML options.
	 * @return string the generated radio button list
	 */
	public function radioButtonList($model,$attribute,$data,$htmlOptions=array())
	{
		return CHtml::activeRadioButtonList($model,$attribute,$data,$htmlOptions);
	}

	/**
	 * Validates one or several models and returns the results in JSON format.
	 * This is a helper method that simplies the way of writing AJAX validation code.
	 * @param mixed $models a single model instance or an array of models.
	 * @param array $attributes list of attributes that should be validated. Defaults to null,
	 * meaning any attribute listed in the applicable validation rules of the models should be
	 * validated. If this parameter is given as a list of attributes, only
	 * the listed attributes will be validated.
	 * @param boolean $loadInput whether to load the data from $_POST array in this method.
	 * If this is true, the model will be populated from <code>$_POST[ModelClass]</code>.
	 * @return string the JSON representation of the validation error messages.
	 */
	public static function validate($models, $attributes=null, $loadInput=true)
	{
		$result=array();
		if(!is_array($models))
			$models=array($models);
		foreach($models as $model)
		{
			if($loadInput && isset($_POST[get_class($model)]))
				$model->attributes=$_POST[get_class($model)];
			$model->validate($attributes);
			foreach($model->getErrors() as $attribute=>$errors)
				$result[CHtml::activeId($model,$attribute)]=$errors;
		}
		return function_exists('json_encode') ? json_encode($result) : CJSON::encode($result);
	}
}