<?php
/**
 * CJavaScriptExpression class file.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CJavaScriptExpression represents a JavaScript expression that does not need escaping.
 * It can be passed to {@link CJavaScript::encode()} and the code will stay as is.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @package system.web.helpers
 * @since 1.1.11
 */
class CJavaScriptExpression
{
	/**
	 * @var string the javascript expression wrapped by this object
	 */
	public $code;

	/**
	 * @param string $code a javascript expression that is to be wrapped by this object
	 * @param boolean $keepBC if true removes "js:" from $code if it starts with it. Defaults to false.
	 * This parameter is used to keep BC (backward compatibility) with previous versions, when 
	 * javascript expressions where prefixed with "js:". This functionality was deprecated in 1.1.11. 
	 * in favor of this class for better security.
	 * @throws CException if argument is not a string
	 */
	public function __construct($code, $keepBC=false)
	{
		if(!is_string($code))
			throw new CException('Value passed to CJavaScriptExpression should be a string.');
		if($keepBC && strpos($code, 'js:')===0)
			$code=substr($code,3);
		$this->code=$code;
	}

	/**
	 * String magic method
	 * @return string the javascript expression wrapped by this object
	 */
	public function __toString()
	{
		return $this->code;
	}
}