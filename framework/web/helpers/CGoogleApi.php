<?php
/**
 * CGoogleApi class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CGoogleApi provides helper methods to easily access {@link http://code.google.com/apis/ajax/ Google AJAX APIs}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.helpers
 * @since 1.0.3
 */
class CGoogleApi
{
	const BOOTSTRAP_URL='http://www.google.com/jsapi';

	/**
	 * Renders the jsapi script file.
	 * @param string the API key. Null if you do not have a key.
	 * @return string the script tag that loads Google jsapi.
	 */
	public static function init($apiKey=null)
	{
		if($apiKey===null)
			return CHtml::scriptFile(self::BOOTSTRAP_URL);
		else
			return CHtml::scriptFile(self::BOOTSTRAP_URL.'?key='.$apiKey);
	}

	/**
	 * Loads the specified API module.
	 * Note that you should call {@link init} first.
	 * @param string the module name
	 * @param string the module version
	 * @param array additional js options that are to be passed to the load() function.
	 * @return string the js code for loading the module. You can use {@link CHtml::script()}
	 * to enclose it in a script tag.
	 */
	public static function load($name,$version='1',$options=array())
	{
		if(empty($options))
			return "google.load(\"{$name}\",\"{$version}\");";
		else
			return "google.load(\"{$name}\",\"{$version}\",".CJavaScript::encode($options).");";
	}

	/**
	 * Registers the specified API module.
	 * This is similar to {@link load} except that it registers the loading code
	 * with {@link CClientScript} instead of returning it.
	 * This method also registers the jsapi script needed by the loading call.
	 * @param string the module name
	 * @param string the module version
	 * @param array additional js options that are to be passed to the load() function.
	 * @param string the API key. Null if you do not have a key.
	 */
	public static function register($name,$version='1',$options=array(),$apiKey=null)
	{
		$cs=Yii::app()->getClientScript();
		$url=$apiKey===null?self::BOOTSTRAP_URL:self::BOOTSTRAP_URL.'?key='.$apiKey;
		$cs->registerScriptFile($url);

		$js=self::load($name,$version,$options);
		$cs->registerScript($name,$js,CClientScript::POS_HEAD);
	}
}