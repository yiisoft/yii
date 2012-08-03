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
	 */
	public function __construct($code)
	{
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