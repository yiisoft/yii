<?php

/**
 * CTrimSanitizer class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * This sanitizer is used to trim an attribute using a given characterlist.
 * 
 * Sanatizes an attribute using the mode given in {@see CTrimSanatizer::$mode}.
 * By default trim, ltrim and rtrim are supported, but mode can be used to use
 * any callable value as trim function. Additionally you may override {@see CTrimSanatizer::trim()}
 * to use your own function instead of trim, ltrim or rtrim.
 * Any callable value passed to {@see CTrimSanatizer::$mode} has to support the following signature:
 * <pre>
 * string trim(CModel $object, string $attribute, CTrimSanitizer $sanitizer)
 * </pre>
 * Your callback has to return the trimmed value.
 * 
 * The value passed to {@see CTrimSanatizer::$charlist} will be used as second parameter
 * of phps native 'trim'.
 * 
 * Configuration of this sanitizer is aviable through the thirs parameter of your callback.
 * 
 * @author Suralc <thesurwaveing@googlemail.com>
 * @package system.sanitizers
 * @since 1.1.13
 */
class CTrimSanitizer extends CSanitizer
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
	/**
	 * @var int Maximum length of the trimmed string. Defaults to 'null' meaning infinite length.
	 */
	public $length=null;
	/**
	 * @var array List of supported trimming methods.
	 */
	private $supportedModes=array('trim','ltrim','rtrim');
	
	/**
	 * Trims an attribute according to the configuration rules presented in the model.
	 * @param CModel $object The model the attribute belongs to
	 * @param string $attribute Name of the attribute.
	 * @return boolean
	 */
	protected function sanitizeAttribute($object, $attribute)
	{
		$value=(string)$object->$attribute;
		if(is_string($this->mode)&&in_array(strtolower($this->mode),$this->supportedModes))
			$object->$attribute=$this->cut($this->trim($value,$this->mode,$this->charlist),$this->length);
		elseif(is_callable($this->mode))
		{
			$trimmedValue=call_user_func_array($this->mode, array($object, $attribute, $this));
			if($trimmedValue !== false)
				$object->$attribute=$this->cut($trimmedValue,$this->length);
			else
				return false;
		}
		else
			throw new CException(Yii::t('yii','{class}::$mode should be a string or a valid callback',
								array('{class}'=>get_class($this))));
	}
	/**
	 * Trims a string according to the given paramaters
	 * @param string $value The string to be trimmed
	 * @param string $mode The mode the string should be trimmed, defaults to 'trim'. Possible values:
	 * <ul>
	 * <li>trim</li>
	 * <li>rtrim</li>
	 * <li>ltrim</li>
	 * </ul>
	 * @param string $charlist The characters to be trimmed. 
	 * Defaults to 'null' using phps default charlist.
	 * @return string The trimmed string
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
				throw new CException(Yii::t('yii', 'Mode "{mode}" is not supported in {class}', 
						array('{mode}'=>(string)$mode,'{class}'=>get_class($this))));
		}
	}
	/**
	 * Cuts a string to a given length
	 * @param string $value The value to be cut.
	 * @param null|integer $length The max length of the given string.
	 * @return string The cut string.
	 */
	protected function cut($value,$length=null,$offset=0)
	{
		if($length===null)
			return $value;
		else
			return substr($value,$offset,(int)$length);
	}
}