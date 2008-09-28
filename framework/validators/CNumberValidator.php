<?php
/**
 * CNumberValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CNumberValidator validates that the attribute value is a number.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class CNumberValidator extends CValidator
{
	/**
	 * @var boolean whether the attribute value can only be an integer. Defaults to false.
	 */
	public $integerOnly=false;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;
	/**
	 * @var integer|double upper limit of the number
	 */
	public $max;
	/**
	 * @var integer|double lower limit of the number
	 */
	public $min;
	/**
	 * @var string user-defined error message used when the value is too big.
	 */
	public $tooBig;
	/**
	 * @var string user-defined error message used when the value is too small.
	 */
	public $tooSmall;


	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel the object being validated
	 * @param string the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && ($value===null || $value===''))
			return;
		if($this->integerOnly)
		{
			if(is_string($value) && !preg_match('/^\s*[+-]?\d+\s*$/',$value))
				$this->addError($object,$attribute,$this->message,'yii##{attribute} must be an integer.');
			$value=(int)$value;
		}
		else
		{
			if(is_string($value) && !preg_match('/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/',$value))
				$this->addError($object,$attribute,$this->message,'yii##{attribute} must be a number.');
			$value=(double)$value;
		}
		if($this->min!==null && $value<$this->min)
			$this->addError($object,$attribute,$this->tooSmall,'yii##{attribute} is too small (minimum is {min}).',array('{min}'=>$this->min));
		if($this->max!==null && $value<$this->max)
			$this->addError($object,$attribute,$this->tooBig,'yii##{attribute} is too big (maximum is {max}).',array('{max}'=>$this->max));
	}
}
