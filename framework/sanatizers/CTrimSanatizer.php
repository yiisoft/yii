<?php

/**
 * CTrimSanatizer class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * This sanatizer is used to trim an attribute using a given characterlist.
 * 
 * Sanatizes an attribute using the mode given in {@see CTrimSanatizer::$mode}.
 * By default trim, ltrim and rtrim are supported, but mode can be used to use
 * any callable value as trim function. Addditionally you may override {@see CTrimSanatizer::trim()}
 * to use your own function instead of trim, ltrim or rtrim.
 * Any callable value passed to {@see CTrimSanatizer::$mode} has to have the following signature:
 * <pre>
 * string trim(CModel $object, string $attribute)
 * </pre>
 * Your callback has to return the trimmed value;
 * You may access the attributes model using
 * 
 * The value passed to {@see CTrimSanatizer::$charlist} will be used as second parameter
 * of phps native 'trim' and third parameter of your callback.
 * @author Suralc <thesurwaveing@googlemail.com>
 * @package system.sanatizers
 * @since 1.1.13
 */
class CTrimSanatizer extends CSanatizer
{
	/**
	 * @var string List of trimmable characters. 
	 * Defaults to null meaning:
	 * <pre> 
	 *   " " (ASCII 32 (0x20)), whitespace.
	 *   "\t" (ASCII 9 (0x09)), a tab.
	 *   "\n" (ASCII 10 (0x0A)), a new line (line feed).
	 *   "\r" (ASCII 13 (0x0D)), a carriage return.
	 *   "\0" (ASCII 0 (0x00)), the NUL-byte.
	 *   "\x0B" (ASCII 11 (0x0B)), a vertical tab.
	 * </pre>
	 */
	public $charlist=null;
	/**
	 * @var string|callable 
	 */
	public $mode='trim';
	public $length;
	private $_supportedModes=array('trim','ltrim','rtrim');
	/**
	 * 
	 * @param type $object
	 * @param type $attribute
	 * @return boolean
	 */
	protected function sanatizeAttribute($object, $attribute)
	{
		$value=(string)$object->$attribute;
		if(in_array(strtolower($this->mode),$this->_supportedModes))
			$object->$attribute=$this->cut($this->trim($value,$this->mode,$this->charlist),$this->length);
		elseif(is_callable($this->mode))
		{
			$trimmedValue=call_user_func_array($this->mode, array($object, $attribute));
			if($trimmedValue !== false)
				$object->$attribute=$this->cut($trimmedValue,$this->length);
			else
				return false;
		}
	}
	/**
	 * 
	 * @param type $value
	 * @param type $mode
	 * @param type $charlist
	 * @return type
	 * @throws CException
	 */
	protected function trim($value, $mode='trim', $charlist=null)
	{
		switch(strtolower($mode))
		{
			case 'trim':
				if($this->charlist===null)
					return trim($value);
				else
					return trim($value,$this->charlist);
				break;
			case 'rtrim':
				if($this->charlist===null)
					return rtrim($value);
				else
					return rtrim($value,$this->charlist);
				break;
			case 'ltrim':
				if($this->charlist===null)
					return ltrim($value);
				else
					return ltrim($value,$this->charlist);
				break;
			default:
				throw new CException(Yii::t('yii', 'Mode "{mode}" no supported in {class}', 
						array('{mode}' => (string)$mode, '{class}'=>  get_class($this))));
		}
	}
	/**
	 * 
	 * @param type $value
	 * @param type $length
	 * @return type
	 */
	protected function cut($value, $length=null)
	{
		if($length===null)
			return $value;
		else
			return substr ($value, 0, (int)$length);
	}
}