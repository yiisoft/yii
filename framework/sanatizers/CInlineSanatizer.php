<?php
/**
 * CInlineSanatizer class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CInlineSanatizer represents a sanatizer which is defined as a method in the object being sanatized.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Suralc <thesurwaveing@googlemail.com>
 * @version $Id$
 * @package system.sanatizers
 * @since 1.1.13
 */
class CInlineSanatizer extends CSanatizer
{
	/**
	 * @var string the name of the sanatization method defined in the model class
	 */
	public $method;
	/**
	 * @var array additional parameters that are passed to the sanatiazion method
	 */
	public $params;
		
	/**
	 * Sanatizes the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being sanatized
	 * @param string $attribute the attribute being sanatized
	 */
	protected function sanatizeAttribute($object,$attribute)
	{
		$method=$this->method;
		$object->$method($attribute,$this->params);
	}
}
