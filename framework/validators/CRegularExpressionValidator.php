<?php
/**
 * CRegularExpressionValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CRegularExpressionValidator validates that the attribute value matches to the specified {@link pattern regular expression}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.validators
 * @since 1.0
 */
class CRegularExpressionValidator extends CValidator
{
	/**
	 * @var string the regular expression to be matched with
	 */
	public $pattern;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;

	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object,$attribute)
	{
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		if($this->pattern===null)
			throw new CException(Yii::t('yii','The "pattern" property must be specified with a valid regular expression.'));
		if(!preg_match($this->pattern,$value))
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} is invalid.');
			$this->addError($object,$attribute,$message);
		}
	}
}

