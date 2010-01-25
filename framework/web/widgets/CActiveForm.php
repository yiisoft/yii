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
 *
 *
 * CActiveForm renders the open and close form tags. In addition, it registers
 * necessary javascript code that can trigger AJAX validations when users change
 * the data in the relevant input fields.
 *
 * The goal of CActiveForm is to simplify the work of creating an HTML form that
 * can perform AJAX validation upon an input is changed by users. This may greatly
 * improve the user experience at entering data into a form. Because the validation
 * is done on the server side using the rules defined in the data model, no extra
 * javascript code needs to be written, and the validation result is consistent.
 * In case when the user turns off javascript in his browser, the traditional
 * validation via whole page submission still works.
 *
 * Using CActiveForm requires writing both the view code and the class code responding
 * to the AJAX validation requests.
 *
 * The following is a piece of sample view code:
 * <pre>
 * &lt;?php $form = $this->beginWidget('CActiveForm', array('id'=>'user-form')); ?&gt;
 *
 * &lt;?php echo $form-&gt;errorSummary($model); ?&gt;
 *
 * &lt;div class="row"&gt;
 *     &lt;?php echo CHtml::activeLabelEx($model,'firstName'); ?&gt;
 *     &lt;?php echo CHtml::activeTextField($model,'firstName'); ?&gt;
 *     &lt;?php echo $form-&gt;error($model,'firstName'); ?&gt;
 * &lt;/div&gt;
 * &lt;div class="row"&gt;
 *     &lt;?php echo CHtml::activeLabelEx($model,'lastName'); ?&gt;
 *     &lt;?php echo CHtml::activeTextField($model,'lastName'); ?&gt;
 *     &lt;?php echo $form-&gt;error($model,'lastName'); ?&gt;
 * &lt;/div&gt;
 *
 * &lt;?php $this->endWidget(); ?&gt;
 * </pre>
 * As we can see, the usage is very similar to {@link CHtml::error} and {@link CHtml::errorSummary}.
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
 * There are some limitations of CActiveForm. First, it does not validate with file upload fields.
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
	 * @var mixed the form action URL (see {@link normalizeUrl} for details about this parameter.)
	 * If not set, the current page URL is used.
	 */
	public $action='';
	/**
	 * @var string the form submission method. This should be either 'post' or 'get'.
	 * Defaults to 'post'.
	 */
	public $method='post';
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
	 * Defaults to 100 (0.1 second).</li>
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
	 * </ul>
	 *
	 * Some of the above options may be overridden in individual calls of {@link error()}.
	 * They include: validationDelay, validateOnChange, validateOnType, hideErrorMessage,
	 * inputContainer, errorCssClass, successCssClass, and validatingCssClass.
	 */
	public $clientOptions=array();
	/**
	 * @var boolean whether to enable data validation via AJAX. Defaults to false.
	 * When this property is set true, you should
	 */
	public $enableAjaxValidation=false;

	private $_attributes=array();
	private $_summary;

	/**
	 * Initializes the widget.
	 * This renders the form open tag.
	 */
	public function init()
	{
		$this->htmlOptions['id']=$this->id;
		echo CHtml::beginForm($this->action, $this->method, $this->htmlOptions);
	}

	/**
	 * Runs the widget.
	 * This registers the necessary javascript code and renders the form close tag.
	 */
	public function run()
	{
		echo CHtml::endForm();
		if(!$this->enableAjaxValidation || empty($this->_attributes))
			return;
		$options=$this->clientOptions;
		if(isset($this->clientOptions['validationUrl']) && is_array($this->clientOptions['validationUrl']))
			$options['validationUrl']=CHtml::normalizeUrl($this->clientOptions['validationUrl']);
		$options['attributes']=array_values($this->_attributes);
		if($this->_summary!==null)
			$options['summaryID']=$this->_summary;
		$options=CJavaScript::encode($options);
		Yii::app()->clientScript->registerCoreScript('yiiactiveform');
		$id=$this->id;
		Yii::app()->clientScript->registerScript(__CLASS__.'#'.$id,"\$('#$id').yiiactiveform($options);");
	}

	/**
	 * Displays the first validation error for a model attribute.
	 * This is similar to {@link CHtml::error} except that it registers the model attribute
	 * so that if its value is changed by users, an AJAX validation may be triggered.
	 * @param CModel the data model
	 * @param string the attribute name
	 * @param array additional HTML attributes to be rendered in the container div tag.
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
	 * </ul>
	 * These options override the corresponding options as declared in {@link options} for this
	 * particular model attribute. For more details about these options, please refer to {@link options}.
	 * @return string the validation result (error display or success message).
	 * @see CHtml::error
	 */
	public function error($model,$attribute,$htmlOptions=array())
	{
		$inputID=isset($htmlOptions['inputID']) ? $htmlOptions['inputID'] : CHtml::activeId($model,$attribute);
		if(!isset($htmlOptions['id']))
			$htmlOptions['id']=$inputID.'_em_';

		$option=array('inputID'=>$inputID, 'errorID'=>$htmlOptions['id']);

		$optionNames=array(
			'validationDelay',
			'validateOnChange',
			'validateOnType',
			'hideErrorMessage',
			'inputContainer',
			'errorCssClass',
			'successCssClass',
			'validatingCssClass',
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
	 * @param mixed the models whose input errors are to be displayed. This can be either
	 * a single model or an array of models.
	 * @param string a piece of HTML code that appears in front of the errors
	 * @param string a piece of HTML code that appears at the end of the errors
	 * @param array additional HTML attributes to be rendered in the container div tag.
	 * @return string the error summary. Empty if no errors are found.
	 * @see CHtml::errorSummary
	 */
	public function errorSummary($models,$header=null,$footer=null,$htmlOptions=array())
	{
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
	 * Validates one or several models and returns the results in JSON format.
	 * This is a helper method that simplies the way of writing AJAX validation code.
	 * @param mixed a single model instance or an array of models.
	 * @param boolean whether to load the data from $_POST array in this method.
	 * If this is true, the model will be populated from <code>$_POST[ModelClass]</code>.
	 * @return string the JSON representation of the validation error messages.
	 */
	public static function validate($models, $loadInput=true)
	{
		$result=array();
		if(!is_array($models))
			$models=array($models);
		foreach($models as $model)
		{
			if($loadInput && isset($_POST[get_class($model)]))
				$model->attributes=$_POST[get_class($model)];
			$model->validate();
			foreach($model->getErrors() as $attribute=>$errors)
				$result[CHtml::activeId($model,$attribute)]=$errors;
		}
		return CJavaScript::encode($result);
	}
}