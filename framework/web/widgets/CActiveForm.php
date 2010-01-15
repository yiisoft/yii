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
 * CActiveForm represents an HTML form that can perform data validation via AJAX.
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
 *     &lt;?php echo CHtml::activeFileField($model,'lastName'); ?&gt;
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
	 * <li>validateOnSubmit: boolean, whether to perform AJAX validation when the form is being submitted.
	 * If there are any validation errors, the form submission will be stopped.
	 * Defaults to false.</li>
	 * <li>validateOnChange: boolean, whether to trigger an AJAX validation
	 * each time when an input's value is changed.	You may want to turn this off
	 * if it causes too much performance impact, because each AJAX validation request
	 * will submit the data of the whole form. Defaults to true.</li>
	 * <li>errorLabelCssClass: string, the CSS class assigned to the labels whose associated input fields
	 * have AJAX validation errors. If this is set false, no CSS class will be added to the error labels.
	 * Defaults to 'error'.</li>
	 * <li>successLabelCssClass: string, the CSS class assigned to the labels whose associated input fields
	 * pass AJAX validations. If this is set false, no CSS class will be added to the success labels.
	 * Defaults to 'success'.</li>
	 * <li>errorInputCssClass: string, the CSS class assigned to the input fields that have
	 * AJAX validation errors. If this is set false, no CSS class will be added to the error inputs.
	 * Defaults to 'error'.</li>
	 * <li>successInputCssClass: string, the CSS class assigned to the input fields that have
	 * passed AJAX validations. If this is set false, no CSS class will be added to the success inputs.
	 * Defaults to 'success'.</li>
	 * <li>errorMessageCssClass: string, the CSS class assigned to the error messages returned
	 * by AJAX validations. If this is set false, no CSS class will be added to the error messages.
	 * Defaults to 'errorMessage'.</li>
	 * <li>successMessageCssClass: string, the CSS class assigned to the success messages when
	 * the corresponding inputs pass AJAX validations. If this is set false, no CSS class will
	 * be added to the success inputs. Defaults to 'successMessage'.</li>
	 * <li>successMessage: string, the message to be displayed when an input passes AJAX validation.
	 * If false, no success message will be displayed. Defaults to false.</li>
	 * </ul>
	 *
	 * Some of the above options may be overridden in individual calls of {@link error()}.
	 * They include: validateOnChange, errorLabelCssClass, successLabelCssClass,
	 * errorInputCssClass, successInputCssClass, errorMessageCssClass, successMessageCssClass and successMessage.
	 */
	public $options=array();

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
		if(empty($this->_attributes))
			return;
		$options=$this->options;
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
	 * <li>validateOnChange</li>
	 * <li>errorLabelCssClass</li>
	 * <li>successLabelCssClass</li>
	 * <li>errorInputCssClass</li>
	 * <li>successInputCssClass</li>
	 * <li>errorMessageCssClass</li>
	 * <li>successMessageCssClass</li>
	 * <li>successMessage</li>
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
			'validateOnChange',
			'errorLabelCssClass',
			'successLabelCssClass',
			'errorInputCssClass',
			'successInputCssClass',
			'errorMessageCssClass',
			'successMessageCssClass',
			'successMessage',
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
			$option['validated']=true;

		if(!isset($htmlOptions['class']) && (isset($option['errorMessageCssClass']) || isset($this->options['errorMessageCssClass'])))
		{
			$class=isset($option['errorMessageCssClass']) ? $option['errorMessageCssClass'] : $this->options['errorMessageCssClass'];
			if($class!==false)
				$htmlOptions['class']=$option['errorMessageCssClass'];
		}
		$html=CHtml::error($model,$attribute,$htmlOptions);
		if($html==='')
		{
			$htmlOptions['style']=isset($htmlOptions['style']) ? rtrim($htmlOptions['style'],';').';display:none' : 'display:none';
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