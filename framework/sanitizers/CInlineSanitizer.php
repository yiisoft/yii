<?php
/**
 * CInlineSanitizer class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CInlineSanitizer represents a sanitizer which is defined as a method in the object being sanitized.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Suralc <thesurwaveing@googlemail.com>
 * @version $Id$
 * @package system.sanitizers
 * @since 1.1.13
 */
class CInlineSanitizer extends CSanitizer
{
	/**
	 * @var string the name of the sanitization method defined in the model class
	 */
	public $method;
	/**
	 * @var array additional parameters that are passed to the sanitiazion method
	 */
	public $params;
		
	/**
	 * Sanitizes the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being sanitized
	 * @param string $attribute the attribute being sanitized
	 */
	protected function sanitizeAttribute($object,$attribute)
	{
		$method=$this->method;
		$object->$method($attribute,$this->params);
	}
}
