<?php
/**
 * CDateValidator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDateValidator verifies if the attribute represents a date, time or datetime.
 *
 * By setting the {@link format} property, one can specify what format the date value
 * must be in. If the given date value doesn't follow the format, the attribute is considered as invalid.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CDateValidator.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.validators
 * @since 1.1.7
 */
class CDateValidator extends CValidator
{
	/**
	 * @var mixed the format pattern that the date value should follow.
	 * This can be either a string or an array representing multiple formats.
	 * Defaults to 'MM/dd/yyyy'. Please see {@link CDateTimeParser} for details
	 * about how to specify a date format.
	 */
	public $format='MM/dd/yyyy';
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;
	/**
	 * @var string the name of the attribute to receive the parsing result.
	 * When this property is not null and the validation is successful, the named attribute will
	 * receive the parsing result.
	 */
	public $timestampAttribute;

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

		$valid=false;

		// reason of array checking is explained here: https://github.com/yiisoft/yii/issues/1955
		if(!is_array($value))
		{
			$formats=is_string($this->format) ? array($this->format) : $this->format;
			foreach($formats as $format)
			{
				$timestamp=CDateTimeParser::parse($value,$format,array('month'=>1,'day'=>1,'hour'=>0,'minute'=>0,'second'=>0));
				if($timestamp!==false)
				{
					$valid=true;
					if($this->timestampAttribute!==null)
						$object->{$this->timestampAttribute}=$timestamp;
					break;
				}
			}
		}

		if(!$valid)
		{
			$message=$this->message!==null?$this->message : Yii::t('yii','The format of {attribute} is invalid.');
			$this->addError($object,$attribute,$message);
		}
	}
}

