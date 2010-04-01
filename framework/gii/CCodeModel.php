<?php
/**
 * CCodeModel class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCodeModel is the base class for model classes that are used to generate code.
 *
 * Each code generator should have at least one code model class that extends from this class.
 * The purpose of a code model is to represent user-supplied parameters and use them to
 * generate customized code.
 *
 * Derived classes should implement the {@link prepare} method whose main task is to
 * fill up the {@link files} property based on the user parameters.
 *
 * The {@link files} property should be filled with a set of {@link CCodeFile} instances,
 * each representing a single code file to be generated.
 *
 * CCodeModel implements the feature of "sticky attributes". A sticky attribute is an attribute
 * that can remember its last valid value, even if the user closes his browser window
 * and reopen it. To declare an attribute is sticky, simply list it in a validation rule with
 * the validator name being "sticky".
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.gii
 * @since 1.1.2
 */
abstract class CCodeModel extends CFormModel
{
	const STATUS_NEW=1;
	const STATUS_PREVIEW=2;
	const STATUS_SUCCESS=3;
	const STATUS_ERROR=4;

	/**
	 * @var array user confirmations on whether to overwrite existing code files with the newly generated ones.
	 * The value of this property is internally managed by this class and {@link CCodeGenerator}.
	 */
	public $answers;
	/**
	 * @var string the name of the code template that the user has selected.
	 * The value of this property is internally managed by this class and {@link CCodeGenerator}.
	 */
	public $template;
	/**
	 * @var array a list of available code templates (name=>directory).
	 * The value of this property is populated by {@link CCodeGenerator}.
	 */
	public $templates=array();  // name => path
	/**
	 * @var array a list of {@link CCodeFile} objects that represent the code files to be generated.
	 * The {@link prepare()} method is responsible to populate this property.
	 */
	public $files=array();
	/**
	 * @var integer the status of this model. T
	 * The value of this property is internally managed by {@link CCodeGenerator}.
	 */
	public $status=self::STATUS_NEW;

	private $_stickyAttributes=array();

	/**
	 * Prepares the code files to be generated.
	 * This is the main method that child classes should implement. It should contain the logic
	 * that populates the {@link files} property with a list of code files to be generated.
	 */
	abstract public function prepare();

	/**
	 * Initializes the model by loading the sticky attributes.
	 */
	public function init()
	{
		$this->loadStickyAttributes();
	}

	/**
	 * Declares the model validation rules.
	 * Child classes must override this method in the following format:
	 * <pre>
	 * return array_merge(parent::rules(), array(
	 *     ...rules for the child class...
	 * ));
	 * </pre>
	 * @return array validation rules
	 */
	public function rules()
	{
		return array(
			array('template', 'required'),
			array('template', 'in', 'range'=>array_keys($this->templates)),
			array('template', 'sticky'),
		);
	}

	/**
	 * Declares the model attribute labels.
	 * Child classes must override this method in the following format:
	 * <pre>
	 * return array_merge(parent::attributeLabels(), array(
	 *     ...labels for the child class attributes...
	 * ));
	 * </pre>
	 * @return array the attribute labels
	 */
	public function attributeLabels()
	{
		return array(
			'template'=>'Code Template',
		);
	}

	/**
	 * Saves the generated code into files.
	 */
	public function save()
	{
		$result=true;
		foreach($this->files as $file)
		{
			if($this->confirmed($file))
				$result=$file->save() && $result;
		}
		return $result;
	}

	/**
	 * @return string the directory that contains the template files.
	 * @throw CException if {@link templates} is empty or template selection is invalid
	 */
	public function getTemplatePath()
	{
		if(isset($this->templates[$this->template]))
			return $this->templates[$this->template];
		else if(empty($this->templates))
			throw new CException('No templates are available.');
		else
			throw new CException('Invalid template selection.');

	}

	/**
	 * @param CCodeFile whether the code file should be saved
	 */
	public function confirmed($file)
	{
		return $this->answers===null && $file->operation===CCodeFile::OP_NEW
			|| is_array($this->answers) && isset($this->answers[md5($file->path)]);
	}

	/**
	 * Generates the code using the specified code template file.
	 * This method is manly used in {@link generate} to generate code.
	 * @param string the code template file path
	 * @param array a set of parameters to be extracted and made available in the code template
	 * @return string the generated code
	 */
	public function render($templateFile,$_params_=null)
	{
		if(!is_file($templateFile))
			throw new CException("The template file '$templateFile' does not exist.");

		if(is_array($_params_))
			extract($_params_,EXTR_PREFIX_SAME,'params');
		else
			$params=$_params_;
		ob_start();
		ob_implicit_flush(false);
		require($templateFile);
		return ob_get_clean();
	}

	/**
	 * @return string the code generation result log.
	 */
	public function renderResults()
	{
		$output='Generating code using template "'.$this->templatePath."\"...\n";
		foreach($this->files as $file)
		{
			if($file->error!==null)
				$output.="<span class=\"error\">{$file->relativePath}<br/>{$file->error}</span>\n";
			else if($file->operation===CCodeFile::OP_NEW && $this->confirmed($file))
				$output.=' generated '.$file->relativePath."\n";
			else if($file->operation===CCodeFile::OP_OVERWRITE && $this->confirmed($file))
				$output.=' overwrote '.$file->relativePath."\n";
			else
				$output.='   skipped '.$file->relativePath."\n";
		}
		$output.="done!\n";
		return $output;
	}

	/**
	 * The "sticky" validator.
	 * This validator does not really validate the attributes.
	 * It actually saves the attribute value in a file to make it sticky.
	 * @param string the attribute to be validated
	 * @param array the validation parameters
	 */
	public function sticky($attribute,$params)
	{
		if(!$this->hasErrors())
		{
			$this->_stickyAttributes[$attribute]=$this->$attribute;
			$this->saveStickyAttributes();
		}
	}

	/**
	 * Loads sticky attributes from a file and populates them into the model.
	 */
	public function loadStickyAttributes()
	{
		$this->_stickyAttributes=array();
		$path=Yii::app()->runtimePath.'/gii/'.get_class($this).'.php';
		if(is_file($path))
		{
			$result=@include($path);
			if(is_array($result))
			{
				$this->_stickyAttributes=$result;
				foreach($this->_stickyAttributes as $name=>$value)
				{
					if(property_exists($this,$name) || $this->canSetProperty($name))
						$this->$name=$value;
				}
			}
		}
	}

	/**
	 * Saves sticky attributes into a file.
	 */
	public function saveStickyAttributes()
	{
		$path=Yii::app()->runtimePath.'/gii/'.get_class($this).'.php';
		@mkdir(dirname($path),0755,true);
		file_put_contents($path,"<?php\nreturn ".var_export($this->_stickyAttributes,true).";\n");
	}
}