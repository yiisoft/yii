<?php
/**
 * CWidget class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWidget is the base class for widgets.
 *
 * A widget is a self-contained component that may generate presentation
 * based on model data.  It can be viewed as a micro-controller that embeds
 * into the controller-managed views.
 *
 * Compared with {@link CController controller}, a widget has neither actions nor filters.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.widgets
 * @since 1.0
 */
class CWidget extends CBaseController
{
	/**
	 * @var array view paths for different types of widgets
	 */
	private static $_viewPaths;
	/**
	 * @var integer the counter for generating implicit IDs.
	 */
	private static $_counter=0;
	/**
	 * @var string id of the widget.
	 */
	private $_id;
	/**
	 * @var CBaseController owner/creator of this widget. It could be either a widget or a controller.
	 */
	private $_owner;

	/**
	 * Constructor.
	 * @param CBaseController owner/creator of this widget. It could be either a widget or a controller.
	 * @param string ID of this widget. If not set, an ID will be generated automatically
	 */
	public function __construct($owner=null)
	{
		$this->_owner=$owner===null?Yii::app()->getController():$owner;
	}

	/**
	 * @return CBaseController owner/creator of this widget. It could be either a widget or a controller.
	 */
	public function getOwner()
	{
		return $this->_owner;
	}

	/**
	 * @param boolean whether to generate an ID if it is not set previously
	 * @return string id of the widget.
	 */
	public function getId($autoGenerate=true)
	{
		if($this->_id!==null)
			return $this->_id;
		else if($autoGenerate)
			return $this->_id='yw'.self::$_counter++;
	}

	/**
	 * @param string id of the widget.
	 */
	public function setId($value)
	{
		$this->_id=$value;
	}

	/**
	 * @return CController the controller that this widget belongs to.
	 */
	public function getController()
	{
		if($this->_owner instanceof CController)
			return $this->_owner;
		else
			return Yii::app()->getController();
	}

	/**
	 * Initializes the widget.
	 * This method is called by {@link CBaseController::createWidget}
	 * and {@link CBaseController::beginWidget} after the widget's
	 * properties have been initialized.
	 */
	public function init()
	{
	}

	/**
	 * Executes the widget.
	 * This method is called by {@link CBaseController::endWidget}.
	 */
	public function run()
	{
	}

	/**
	 * Returns the directory containing the view files for this widget.
	 * The default implementation returns the 'views' subdirectory of the directory containing the widget class file.
	 * @return string the directory containing the view files for this widget.
	 */
	public function getViewPath()
	{
		$className=get_class($this);
		if(isset(self::$_viewPaths[$className]))
			return self::$_viewPaths[$className];
		else
		{
			$class=new ReflectionClass(get_class($this));
			return self::$_viewPaths[$className]=dirname($class->getFileName()).DIRECTORY_SEPARATOR.'views';
		}
	}

	/**
	 * Looks for the view script file according to the view name.
	 * This method will look for the view under the widget's {@link getViewPath viewPath}.
	 * The view script file is named as "ViewName.php". A localized view file
	 * may be returned if internationalization is needed. See {@link CApplication::findLocalizedFile}
	 * for more details.
	 * @param string name of the view (without file extension)
	 * @return string the view file path. False if the view file does not exist
	 * @see CApplication::findLocalizedFile
	 */
	public function getViewFile($viewName)
	{
		$viewFile=$this->getViewPath().DIRECTORY_SEPARATOR.$viewName.'.php';
		return is_file($viewFile) ? Yii::app()->findLocalizedFile($viewFile) : false;
	}

	/**
	 * Renders a view.
	 *
	 * The named view refers to a PHP script (resolved via {@link getViewFile})
	 * that is included by this method. If $data is an associative array,
	 * it will be extracted as PHP variables and made available to the script.
	 *
	 * @param string name of the view to be rendered. See {@link getViewFile} for details
	 * about how the view script is resolved.
	 * @param array data to be extracted into PHP variables and made available to the view script
	 * @param boolean whether the rendering result should be returned instead of being displayed to end users
	 * @return string the rendering result. Null if the rendering result is not required.
	 * @throws CException if the view does not exist
	 * @see getViewFile
	 */
	public function render($view,$data=null,$return=false)
	{
		if(($viewFile=$this->getViewFile($view))!==false)
			return $this->renderFile($viewFile,$data,$return);
		else
			throw new CException(Yii::t('yii#{widget} cannot find the view "{view}".',
				array('{widget}'=>get_class($this), '{view}'=>$view)));
	}
}