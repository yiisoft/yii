<?php
/**
 * CTheme class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CTheme represents an application theme.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CTheme extends CComponent
{
	private $_name;
	private $_basePath;
	private $_baseUrl;

	/**
	 * Constructor.
	 * @param string name of the theme
	 * @param string base theme path
	 * @param string base theme URL
	 */
	public function __construct($name,$basePath,$baseUrl)
	{
		$this->_name=$name;
		$this->_baseUrl=$baseUrl;
		$this->_basePath=$basePath;
	}

	/**
	 * @return string theme name
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * @return string the relative URL to the theme folder (without ending slash)
	 */
	public function getBaseUrl()
	{
		return $this->_baseUrl;
	}

	/**
	 * @return string the file path to the theme folder
	 */
	public function getBasePath()
	{
		return $this->_basePath;
	}

	/**
	 * @return string the path for controller views. Defaults to 'ThemeRoot/views'.
	 */
	public function getViewPath()
	{
		return $this->_basePath.DIRECTORY_SEPARATOR.'views';
	}

	/**
	 * @return string the path for layouts. Defaults to 'ThemeRoot/views/layouts'.
	 */
	public function getLayoutPath()
	{
		return $this->getViewPath().DIRECTORY_SEPARATOR.'layouts';
	}

	/**
	 * @return string the path for system views. Defaults to 'ThemeRoot/views/system'.
	 */
	public function getSystemViewPath()
	{
		return $this->getViewPath().DIRECTORY_SEPARATOR.'system';
	}

	/**
	 * Finds the view file for the specified controller's view.
	 * @param CController the controller
	 * @param string the view name
	 * @return string the view file path. False if the file does not exist.
	 */
	public function getViewFile($controller,$viewName)
	{
		if($viewName[0]==='/')
			$viewFile=$this->getViewPath().$viewName.'.php';
		else
			$viewFile=$this->getViewPath().DIRECTORY_SEPARATOR.$controller->getId().DIRECTORY_SEPARATOR.$viewName.'.php';
		return is_file($viewFile) ? Yii::app()->findLocalizedFile($viewFile) : false;
	}

	/**
	 * Finds the layout file for the specified controller's layout.
	 * @param CController the controller
	 * @param string the layout name
	 * @return string the layout file path. False if the file does not exist.
	 */
	public function getLayoutFile($controller,$layoutName)
	{
		if($layoutName[0]==='/')
			$layoutFile=$this->getViewPath().$layoutName.'.php';
		else
			$layoutFile=$this->getLayoutPath().DIRECTORY_SEPARATOR.$layoutName.'.php';
		return is_file($layoutFile) ? Yii::app()->findLocalizedFile($layoutFile) : false;
	}
}
