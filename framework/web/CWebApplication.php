<?php
/**
 * CWebApplication class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


/**
 * CWebApplication extends CApplication by providing functionalities specific to Web requests.
 *
 * CWebApplication manages the controllers in MVC pattern, and provides the following additional
 * core application components:
 * <ul>
 * <li>{@link getUrlManager urlManager}: provides URL parsing and constructing functionality;</li>
 * <li>{@link getRequest request}: encapsulates the Web request information;</li>
 * <li>{@link getSession session}: provides the session-related functionalities;</li>
 * <li>{@link getAssetManager assetManager}: manages the publishing of private asset files.</li>
 * </ul>
 *
 * User requests are resolved as controller-action pairs and additional parameters.
 * CWebApplication creates the requested controller instance and let it to handle
 * the actual user request. If the user does not specify controller ID, it will
 * assume {@link defaultController} is requested (which defaults to 'site').
 *
 * Controller class files must reside under the directory {@link getControllerPath controllerPath}
 * (defaults to 'protected/controllers'). The file name is the same as the controller
 * name and the class name is the controller ID appended with 'Controller'.
 * For example, the controller 'article' is defined by the class 'ArticleController'
 * which is in the file 'protected/controller/article.php'.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CWebApplication extends CApplication
{
	/**
	 * @return string the ID of the default controller. Defaults to 'site'.
	 */
	public $defaultController='site';
	/**
	 * @var string the application-wide layout. Defaults to 'main' (relative to {@link getLayoutPath layoutPath}).
	 */
	public $layout='main';
	/**
	 * @var array mapping from controller ID to controller configurations.
	 * Each name-value pair specifies the configuration for a single controller.
	 * A controller configuration can be either a string or an array.
	 * If the former, the string should be the class name or
	 * {@link YiiBase::getPathOfAlias class path alias} of the controller.
	 * If the latter, the array must contain a 'class' element which specifies
	 * the controller's class name or {@link YiiBase::getPathOfAlias class path alias}.
	 * The rest name-value pairs in the array are used to initialize
	 * the corresponding controller properties. For example,
	 * <pre>
	 * array(
	 *   'post'=>array(
	 *      'class'=>'path.to.PostController',
	 *      'pageTitle'=>'something new',
	 *   ),
	 *   'user'=>'path.to.UserController',,
	 * )
	 * </pre>
	 *
	 * Note, when processing an incoming request, the controller map will first be
	 * checked to see if the request can be handled by one of the controllers in the map.
	 * If not, a controller will be searched for under the {@link getControllerPath default controller path}.
	 */
	public $controllerMap=array();
	/**
	 * @var array the configuration specifying a controller which should handle
	 * all user requests. This is mainly used when the application is in maintenance mode
	 * and we should use a controller to handle all incoming requests.
	 * The configuration specifies the controller route (the first element)
	 * and GET parameters (the rest name-value pairs). For example,
	 * <pre>
	 * array(
	 *     'offline/notice',
	 *     'param1'=>'value1',
	 *     'param2'=>'value2',
	 * )
	 * </pre>
	 * Defaults to null, meaning catch-all is not effective.
	 */
	public $catchAll;

	private $_controllerPath;
	private $_viewPath;
	private $_systemViewPath;
	private $_layoutPath;
	private $_controller;
	private $_homeUrl;
	private $_theme;


	/**
	 * Processes the current request.
	 * It first resolves the request into controller and action,
	 * and then creates the controller to perform the action.
	 */
	public function processRequest()
	{
		if(is_array($this->catchAll) && isset($this->catchAll[0]))
		{
			$segs=explode('/',$this->catchAll[0]);
			$controllerID=$segs[0];
			$actionID=isset($segs[1])?$segs[1]:'';
			foreach(array_splice($this->catchAll,1) as $name=>$value)
				$_GET[$name]=$value;
		}
		else
			list($controllerID,$actionID)=$this->resolveRequest();
		$this->runController($controllerID,$actionID);
	}

	/**
	 * Resolves the current request into controller and action.
	 * @return array controller ID and action ID.
	 */
	protected function resolveRequest()
	{
		$route=$this->getUrlManager()->parseUrl($this->getRequest());
		if(($pos=strrpos($route,'/'))!==false)
			return array(substr($route,0,$pos),(string)substr($route,$pos+1));
		else
			return array($route,'');
	}

	/**
	 * Creates the controller and performs the specified action.
	 * @param string controller ID
	 * @param string action ID
	 * @throws CHttpException if the controller could not be created.
	 */
	public function runController($controllerID,$actionID)
	{
		if(($controller=$this->createController($controllerID))!==null)
		{
			$oldController=$this->_controller;
			$this->_controller=$controller;
			$controller->run($actionID);
			$this->_controller=$oldController;
		}
		else
			throw new CHttpException(404,Yii::t('yii#The requested controller "{controller}" does not exist.',
				array('{controller}'=>$controllerID)));
	}

	/**
	 * Registers the core application components.
	 * This method overrides the parent implementation by registering additional core components.
	 * @see setComponents
	 */
	protected function registerCoreComponents()
	{
		parent::registerCoreComponents();

		$components=array(
			'urlManager'=>array(
				'class'=>'CUrlManager',
			),
			'request'=>array(
				'class'=>'CHttpRequest',
			),
			'session'=>array(
				'class'=>'CHttpSession',
			),
			'assetManager'=>array(
				'class'=>'CAssetManager',
			),
			'user'=>array(
				'class'=>'CWebUser',
			),
			'themeManager'=>array(
				'class'=>'CThemeManager',
			),
		);

		$this->setComponents($components);
	}

	/**
	 * @return CHttpRequest the request component
	 */
	public function getRequest()
	{
		return $this->getComponent('request');
	}

	/**
	 * @return CUrlManager the URL manager component
	 */
	public function getUrlManager()
	{
		return $this->getComponent('urlManager');
	}

	/**
	 * @return CAssetManager the asset manager component
	 */
	public function getAssetManager()
	{
		return $this->getComponent('assetManager');
	}

	/**
	 * @return CHttpSession the session component
	 */
	public function getSession()
	{
		return $this->getComponent('session');
	}

	/**
	 * @return CWebUser the user session information
	 */
	public function getUser()
	{
		return $this->getComponent('user');
	}

	/**
	 * @return CThemeManager the theme manager.
	 */
	public function getThemeManager()
	{
		return $this->getComponent('themeManager');
	}

	/**
	 * @return CTheme the theme used currently. Null if no theme is being used.
	 */
	public function getTheme()
	{
		if(is_string($this->_theme))
			$this->_theme=$this->getThemeManager()->getTheme($this->_theme);
		return $this->_theme;
	}

	/**
	 * @param string the theme name
	 */
	public function setTheme($value)
	{
		$this->_theme=$value;
	}

	/**
	 * Creates a relative URL based on the given controller and action information.
	 * @param string the URL route. This should be in the format of 'ControllerID/ActionID'.
	 * @param array additional GET parameters (name=>value). Both the name and value will be URL-encoded.
	 * @param string the token separating name-value pairs in the URL.
	 * @return string the constructed URL
	 */
	public function createUrl($route,$params=array(),$ampersand='&')
	{
		return $this->getUrlManager()->createUrl($route,$params,$ampersand);
	}

	/**
	 * Returns the relative URL for the application.
	 * This is a shortcut method to {@link CHttpRequest::getBaseUrl()}.
	 * @return string the relative URL for the application
	 * @see CHttpRequest::getBaseUrl()
	 */
	public function getBaseUrl()
	{
		return $this->getRequest()->getBaseUrl();
	}

	/**
	 * @return string the homepage URL
	 */
	public function getHomeUrl()
	{
		if($this->_homeUrl===null)
		{
			if($this->getUrlManager()->showScriptName)
				return $this->getRequest()->getScriptUrl();
			else
				return $this->getRequest()->getBaseUrl().'/';
		}
		else
			return $this->_homeUrl;
	}

	/**
	 * @param string the homepage URL
	 */
	public function setHomeUrl($value)
	{
		$this->_homeUrl=$value;
	}

	/**
	 * Creates a controller instance based on its ID.
	 * The controller class will be ucfirst(id).'Controller'.
	 * @param string ID of the controller
	 * @return CController the controller instance, null if the controller class does not exist or is invalid.
	 */
	public function createController($id)
	{
		if($id==='')
			$id=$this->defaultController;
		if(preg_match('/^\w+$/',$id))
		{
			if(isset($this->controllerMap[$id]))
				return CConfiguration::createObject($this->controllerMap[$id],$id);
			else
				return $this->createControllerIn($this->getControllerPath(),ucfirst($id).'Controller',$id);
		}
	}

	/**
	 * Creates a controller instance whose class file is under the specified directory.
	 * @param string the directory containing the controller class file
	 * @param string name of the controller class
	 * @param string ID of the controller
	 * @return CController the controller instance, null if the controller class does not exist or is invalid.
	 */
	protected function createControllerIn($directory,$className,$id)
	{
		$filePath=$directory.DIRECTORY_SEPARATOR.$className.'.php';
		if(is_file($filePath))
		{
			require_once($filePath);
			if(class_exists($className,false) && is_subclass_of($className,'CController'))
				return new $className($id);
		}
	}

	/**
	 * @return CController the currently active controller
	 */
	public function getController()
	{
		return $this->_controller;
	}

	/**
	 * @return string the directory that contains the controller classes. Defaults to 'protected/controllers'.
	 */
	public function getControllerPath()
	{
		if($this->_controllerPath!==null)
			return $this->_controllerPath;
		else
			return $this->_controllerPath=$this->getBasePath().DIRECTORY_SEPARATOR.'controllers';
	}

	/**
	 * @param string the directory that contains the controller classes.
	 * @throws CException if the directory is invalid
	 */
	public function setControllerPath($value)
	{
		if(($this->_controllerPath=realpath($value))===false || !is_dir($this->_controllerPath))
			throw new CException(Yii::t('yii#The controller path "{path}" is not a valid directory.',
				array('{path}'=>$value)));
	}

	/**
	 * @return string the root directory of view files. Defaults to 'protected/views'.
	 */
	public function getViewPath()
	{
		if($this->_viewPath!==null)
			return $this->_viewPath;
		else
			return $this->_viewPath=$this->getBasePath().DIRECTORY_SEPARATOR.'views';
	}

	/**
	 * @param string the root directory of view files.
	 * @throws CException if the directory does not exist.
	 */
	public function setViewPath($path)
	{
		if(($this->_viewPath=realpath($path))===false || !is_dir($this->_viewPath))
			throw new CException(Yii::t('yii#The view path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * @return string the root directory of system view files. Defaults to 'protected/views/system'.
	 */
	public function getSystemViewPath()
	{
		if($this->_systemViewPath!==null)
			return $this->_systemViewPath;
		else
			return $this->_systemViewPath=$this->getViewPath().DIRECTORY_SEPARATOR.'system';
	}

	/**
	 * @param string the root directory of system view files.
	 * @throws CException if the directory does not exist.
	 */
	public function setSystemViewPath($path)
	{
		if(($this->_systemViewPath=realpath($path))===false || !is_dir($this->_systemViewPath))
			throw new CException(Yii::t('yii#The system view path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * @return string the root directory of layout files. Defaults to 'protected/views/layouts'.
	 */
	public function getLayoutPath()
	{
		if($this->_layoutPath!==null)
			return $this->_layoutPath;
		else
			return $this->_layoutPath=$this->getViewPath().DIRECTORY_SEPARATOR.'layouts';
	}

	/**
	 * @param string the root directory of layout files.
	 * @throws CException if the directory does not exist.
	 */
	public function setLayoutPath($path)
	{
		if(($this->_layoutPath=realpath($path))===false || !is_dir($this->_layoutPath))
			throw new CException(Yii::t('yii#The layout path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}
}
