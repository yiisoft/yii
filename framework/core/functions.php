<?php
/**
 * This file contains some shortcut global functions.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

if(!function_exists('t'))
{
	/**
	 * Translates a message to the {@link CApplication::getLanguage application language}.
	 * This is a shortcut to {@link Yii::t}.
	 * @param string the original message
	 * @param array parameters to be applied to the message
	 * @return string the translated message
	 * @see Yii::t
	 */
	function t($message,$params=array())
	{
		return Yii::t($message,$params);
	}
}

if(!function_exists('app'))
{
	/**
	 * Returns the current application instance.
	 * This is a shortcut to {@link Yii::app}.
	 * @return CApplication the application instance
	 */
	function app()
	{
		return Yii::app();
	}
}

if(!function_exists('h'))
{
	/**
	 * Encodes HTML special characters into corresponding entites.
	 * This is a shortcut method that is equivalent to {@link CHtml::encode()}.
	 * @param string the HTML code to be encoded.
	 * @return string the encoded HTML code
	 */
	function h($str)
	{
		return htmlspecialchars($str,ENT_QUOTES,Yii::app()->charset);
	}
}