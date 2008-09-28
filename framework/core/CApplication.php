<?php
/**
 * This file contains the base application class and related classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require_once(YII_PATH.'/core/interfaces.php');
require_once(YII_PATH.'/core/functions.php');

/**
 * CApplication is the base class for all application classes.
 *
 * An application serves as the global context that the user request
 * is being processed. It manages a set of application components that
 * provide specific functionalities to the whole application.
 *
 * The core application components provided by CApplication are the following:
 * <ul>
 * <li>{@link getErrorHandler errorHandler}: handles PHP errors and
 *   uncaught exceptions. This application component is dynamically loaded when needed.</li>
 * <li>{@link getSecurityManager securityManager}: provides security-related
 *   services, such as hashing, encryption. This application component is dynamically
 *   loaded when needed.</li>
 * <li>{@link getStatePersister statePersister}: provides global state
 *   persistence method. This application component is dynamically loaded when needed.</li>
 * <li>{@link getCache cache}: provides caching feature. This application component is
 *   disabled by default.</li>
 * <li>{@link getMessages messages}: provides the message source for translating
 *   application messages. This application component is dynamically loaded when needed.</li>
 * <li>{@link getCoreMessages coreMessages}: provides the message source for translating
 *   Yii framework messages. This application component is dynamically loaded when needed.</li>
 * </ul>
 *
 * CApplication will undergo the following lifecycles when processing a user request:
 * <ol>
 * <li>load application configuration;</li>
 * <li>set up class autoloader and error handling;</li>
 * <li>load static application components;</li>
 * <li>{@link onBeginRequest}: preprocess the user request;</li>
 * <li>{@link processRequest}: process the user request;</li>
 * <li>{@link onEndRequest}: postprocess the user request;</li>
 * </ol>
 *
 * Starting from lifecycle 3, if a PHP error or an uncaught exception occurs,
 * the application will switch to its error handling logic and jump to step 6 afterwards.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.core
 * @since 1.0
 */
abstract class CApplication extends CComponent
{
	/**
	 * @var string the application name. Defaults to 'My Application'.
	 */
	public $name='My Application';
	/**
	 * @var string the charset currently used for the application. Defaults to 'UTF-8'.
	 */
	public $charset='UTF-8';
	/**
	 * @var array the IDs of the application components that should be preloaded.
	 */
	public $preload=array();
	/**
	 * @var string the language that the application is written in. This mainly refers to
	 * the language that the messages and view files are in. Defaults to 'en_us' (US English).
	 */
	public $sourceLanguage='en_us';

	private $_id;
	private $_basePath;
	private $_runtimePath;
	private $_extensionPath;
	private $_globalState;
	private $_stateChanged;
	private $_params;
	private $_components=array();
	private $_componentConfig=array();
	private $_ended=false;
	private $_language;

	/**
	 * Processes the request.
	 * This is the place where the actual request processing work is done.
	 * Derived classes should override this method.
	 */
	abstract public function processRequest();

	/**
	 * Constructor.
	 * @param mixed application configuration.
	 * If a string, it is treated as the path of the file that contains the configuration;
	 * If an array or CConfiguration, it is the actual configuration information.
	 * Please make sure you specify the {@link getBasePath basePath} property in the configuration,
	 * which should point to the directory containing all application logic, template and data.
	 * If not, the directory will be defaulted to 'protected'.
	 */
	public function __construct($config=null)
	{
		Yii::setApplication($this);
		$this->registerCoreComponents();
		$this->configure($config);
		$this->init();
	}

	/**
	 * Initializes the application.
	 * This method is invoked right after the application is configured.
	 * The default implementation will initialize the error and exception
	 * handlers and load static components.
	 * If you override this method, make sure the parent implementation
	 * is called.
	 */
	protected function init()
	{
		$this->initSystemHandlers();
		$this->preloadComponents();
	}

	/**
	 * Getter magic method.
	 * This method is overridden to support accessing application components
	 * like reading application properties.
	 * @param string application component or property name
	 * @return mixed the named property value
	 */
	public function __get($name)
	{
		if($this->hasComponent($name))
			return $this->getComponent($name);
		else
			return parent::__get($name);
	}

	/**
	 * Runs the application.
	 * This method loads static application components. Derived classes usually overrides this
	 * method to do more application-specific tasks.
	 * Remember to call the parent implementation so that static application components are loaded.
	 */
	public function run()
	{
		if(YII_ENABLE_CLEAN_SHUTDOWN)
			register_shutdown_function(array($this,'onEndRequest'),new CEvent($this));
		$this->onBeginRequest(new CEvent($this));
		$this->processRequest();
		if(!YII_ENABLE_CLEAN_SHUTDOWN)
			$this->onEndRequest(new CEvent($this));
	}

	/**
	 * Terminates the application.
	 * This method replaces PHP's exit() function by performing
	 * additional final tasks of the application before exiting.
	 * @param integer exit status (value 0 means normal exit while other values mean abnormal exit).
	 */
	public function terminate($status=0)
	{
		$this->onEndRequest(new CEvent($this));
		exit($status);
	}

	/**
	 * Raised right BEFORE the application processes the request.
	 * @param CEvent the event parameter
	 */
	public function onBeginRequest($event)
	{
		$this->raiseEvent('onBeginRequest',$event);
	}

	/**
	 * Raised right AFTER the application processes the request.
	 * @param CEvent the event parameter
	 */
	public function onEndRequest($event)
	{
		if(!$this->_ended)
		{
			$this->_ended=true;
			$this->raiseEvent('onEndRequest',$event);
		}
	}

	/**
	 * @return string a unique identifier for the application.
	 */
	public function getId()
	{
		if($this->_id!==null)
			return $this->_id;
		else
			return $this->_id=md5($this->getBasePath().$this->name);
	}

	/**
	 * @param string a unique identifier for the application.
	 */
	public function setId($id)
	{
		$this->_id=$id;
	}

	/**
	 * @return string the root directory of the application. Defaults to 'protected'.
	 */
	public function getBasePath()
	{
		return $this->_basePath;
	}

	/**
	 * Sets the root directory of the application.
	 * This method can only be invoked at the begin of the constructor.
	 * @param string the root directory of the application.
	 * @throws CException if the directory does not exist.
	 */
	public function setBasePath($path)
	{
		if(($this->_basePath=realpath($path))===false || !is_dir($this->_basePath))
			throw new CException(Yii::t('yii##Application base path "{path}" is not a valid directory.',
				array('{path}'=>$path)));
	}

	/**
	 * @return string the directory that stores runtime files. Defaults to 'protected/runtime'.
	 */
	public function getRuntimePath()
	{
		if($this->_runtimePath!==null)
			return $this->_runtimePath;
		else
		{
			$this->setRuntimePath($this->getBasePath().DIRECTORY_SEPARATOR.'runtime');
			return $this->_runtimePath;
		}
	}

	/**
	 * @param string the directory that stores runtime files.
	 * @throws CException if the directory does not exist or is not writable
	 */
	public function setRuntimePath($path)
	{
		if(($runtimePath=realpath($path))===false || !is_dir($runtimePath) || !is_writable($runtimePath))
			throw new CException(Yii::t('yii##Application runtime path "{path}" is not valid. Please make sure it is a directory writable by the Web server process.',
				array('{path}'=>$runtimePath)));
		$this->_runtimePath=$runtimePath;
	}

	/**
	 * Returns the root directory that holds all third-party extensions.
	 * Note, this property cannot be changed or overridden. It is always 'AppBasePath/extensions'.
	 * @return string the directory that contains all extensions.
	 */
	final public function getExtensionPath()
	{
		if($this->_extensionPath!==null)
			return $this->_extensionPath;
		else
			return $this->_extensionPath=$this->getBasePath().DIRECTORY_SEPARATOR.'extensions';
	}

	/**
	 * Sets the aliases that are used in the application.
	 * @param array list of aliases to be imported
	 */
	public function setImport($aliases)
	{
		foreach($aliases as $alias)
			Yii::import($alias);
	}

	/**
	 * @return string the language that the user is using and the application should be targeted to.
	 * Defaults to the {@link sourceLanguage source language}.
	 */
	public function getLanguage()
	{
		return $this->_language===null ? $this->sourceLanguage : $this->_language;
	}

	/**
	 * Specifies which language the application is targeted to.
	 *
	 * This is the language that the application displays to end users.
	 * If set null, it uses the {@link sourceLanguage source language}.
	 *
	 * Unless your application needs to support multiple languages, you should always
	 * set this language to null to maximize the application's performance.
	 * @param string the user language (e.g. 'en_US', 'zh_CN').
	 * If it is null, the {@link sourceLanguage} will be used.
	 */
	public function setLanguage($language)
	{
		$this->_language=$language;
	}

	/**
	 * Returns the localized version of a specified file.
	 * The searching is based on the specified language code. For a language code such as 'zh_cn',
	 * this method will check the existence of the following files in order:
	 * <li>path/to/zh_cn/fileName
	 * <li>path/to/zh/fileName
	 * The first existing file will be returned. If no localized file exists, the original file will be returned
	 * (even if it does not exist.)
	 * Note, the language codes used here should be in lower-case and the dashes
	 * be replaced with underscores (e.g. 'en_us' instead of 'en-US').
	 * @param string the original file
	 * @param string the language that the original file is in. If null, the application {@link language} is used.
	 * @param string the desired language that the file should be localized to. If null, the {@link getLanguage application language} will be used.
	 * @return string the matching localized file. The original file is returned if no localized version is found.
	 */
	public function findLocalizedFile($srcFile,$srcLanguage=null,$language=null)
	{
		static $files=array();

		if($srcLanguage===null)
			$srcLanguage=$this->sourceLanguage;
		if($language===null)
			$language=$this->getLanguage();
		if($language===$srcLanguage)
			return $srcFile;

		if(isset($files[$srcFile][$language]))
			return $files[$srcFile][$language];

		$basePath=dirname($srcFile).DIRECTORY_SEPARATOR;
		$fileName=basename($srcFile);
		$langs=explode('_',$language);

		$paths=array();
		$pos=-1;
		while(($pos=strpos($language,'_',$pos+1))!==false)
			$paths[]=$basePath.substr($language,0,$pos).DIRECTORY_SEPARATOR.$fileName;
		$paths[]=$basePath.$language;

		for($i=count($paths)-1;$i>=0;--$i)
		{
			if(is_file($paths[$i]))
				return $files[$srcFile][$language]=$paths[$i];
		}
		return $files[$srcFile][$language]=$srcFile;
	}

	/**
	 * @param string locale ID (e.g. en_US). If null, the {@link getLanguage application language ID} will be used.
	 * @return CLocale the locale instance
	 */
	public function getLocale($localeID=null)
	{
		return CLocale::getInstance($localeID===null?$this->getLanguage():$localeID);
	}

	/**
	 * @return CNumberFormatter the locale-dependent number formatter.
	 * The current {@link getLocale application locale} will be used.
	 */
	public function getNumberFormatter()
	{
		return $this->getLocale()->getNumberFormatter();
	}

	/**
	 * @return CDateFormatter the locale-dependent date formatter.
	 * The current {@link getLocale application locale} will be used.
	 */
	public function getDateFormatter()
	{
		return $this->getLocale()->getDateFormatter();
	}

	/**
	 * @return CDbConnection the database connection
	 */
	public function getDb()
	{
		return $this->getComponent('db');
	}

	/**
	 * @return CErrorHandler the error handler application component.
	 */
	public function getErrorHandler()
	{
		return $this->getComponent('errorHandler');
	}

	/**
	 * @return CSecurityManager the security manager application component.
	 */
	public function getSecurityManager()
	{
		return $this->getComponent('securityManager');
	}

	/**
	 * @return CStatePersister the state persister application component.
	 */
	public function getStatePersister()
	{
		return $this->getComponent('statePersister');
	}

	/**
	 * @return CCache the cache application component. Null if the component is not enabled.
	 */
	public function getCache()
	{
		return $this->getComponent('cache');
	}

	/**
	 * @return CPhpMessageSource the core message translations
	 */
	public function getCoreMessages()
	{
		return $this->getComponent('coreMessages');
	}

	/**
	 * @return CMessageSource the application message translations
	 */
	public function getMessages()
	{
		return $this->getComponent('messages');
	}

	/**
	 * @return CAttributeCollection the list of application parameters
	 */
	public function getParams()
	{
		if($this->_params!==null)
			return $this->_params;
		else
			return $this->_params=new CAttributeCollection;
	}

	/**
	 * @param mixed application parameters. This can be either an array or CAttributeCollection object.
	 */
	public function setParams($value)
	{
		if(is_array($value))
			$this->_params=new CAttributeCollection($value);
		else
			$this->_params=$value;
	}

	/**
	 * Returns a global value.
	 *
	 * A global value is one that is persistent across users sessions and requests.
	 * @param string the name of the value to be returned
	 * @param mixed the default value. If the named global value is not found, this will be returned instead.
	 * @return mixed the named global value
	 * @see setGlobalState
	 */
	public function getGlobalState($key,$defaultValue=null)
	{
		if($this->_globalState===null)
			$this->loadGlobalState();
		if(isset($this->_globalState[$key]))
			return $this->_globalState[$key];
		else
			return $defaultValue;
	}

	/**
	 * Sets a global value.
	 *
	 * A global value is one that is persistent across users sessions and requests.
	 * Make sure that the value is serializable and unserializable.
	 * @param string the name of the value to be saved
	 * @param mixed the global value to be saved. It must be serializable.
	 * @param mixed the default value. If the named global value is the same as this value, it will be cleared from the current storage.
	 * @see getGlobalState
	 */
	public function setGlobalState($key,$value,$defaultValue=null)
	{
		if($this->_globalState===null)
			$this->loadGlobalState();
		$this->_stateChanged=true;
		if($value===$defaultValue)
			unset($this->_globalState[$key]);
		else
			$this->_globalState[$key]=$value;
	}

	/**
	 * Clears a global value.
	 *
	 * The value cleared will no longer be available in this request and the following requests.
	 * @param string the name of the value to be cleared
	 */
	public function clearGlobalState($key)
	{
		if($this->_globalState===null)
			$this->loadGlobalState();
		if(isset($this->_globals[$key]))
		{
			$this->_stateChanged=true;
			unset($this->_globals[$key]);
		}
	}

	/**
	 * Loads the global state data from persistent storage.
	 * @see getStatePersister
	 * @throws CException if the state persister is not available
	 */
	protected function loadGlobalState()
	{
		$persister=$this->getStatePersister();
		if(($this->_globalState=$persister->load())===null)
			$this->_globalState=array();
		$this->_stateChanged=false;
		$this->attachEventHandler('onEndRequest',array($this,'saveGlobalState'));
	}

	/**
	 * Saves the global state data into persistent storage.
	 * @see getStatePersister
	 * @throws CException if the state persister is not available
	 */
	protected function saveGlobalState()
	{
		if($this->_stateChanged)
		{
			$persister=$this->getStatePersister();
			$this->_stateChanged=false;
			$persister->save($this->_globalState);
		}
	}

	/**
	 * Handles uncaught PHP exceptions.
	 *
	 * This method is implemented as a PHP exception handler. It requires
	 * that constant YII_ENABLE_EXCEPTION_HANDLER be defined true.
	 *
	 * This method will first raise an {@link onException} event.
	 * If the exception is not handled by any event handler, it will call
	 * {@link getErrorHandler errorHandler} to process the exception.
	 *
	 * The application will be terminated by this method.
	 *
	 * @param Exception exception that is not caught
	 */
	public function handleException($exception)
	{
		// disable error capturing to avoid recursive errors
		restore_error_handler();
		restore_exception_handler();

		$category='exception.'.get_class($exception);
		if($exception instanceof CHttpException)
			$category.='.'.$exception->statusCode;
		$message=(string)$exception;
		if(isset($_SERVER['REQUEST_URI']))
			$message.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
		Yii::log($message,CLogger::LEVEL_ERROR,$category);

		$event=new CExceptionEvent($this,$exception);
		$this->onException($event);
		if(!$event->handled)
		{
			// try an error handler
			if(($handler=$this->getErrorHandler())!==null)
				$handler->handle($event);
			else
				$this->displayException($exception);
		}
		$this->terminate(1);
	}

	/**
	 * Handles PHP execution errors such as warnings, notices.
	 *
	 * This method is implemented as a PHP error handler. It requires
	 * that constant YII_ENABLE_ERROR_HANDLER be defined true.
	 *
	 * This method will first raise an {@link onError} event.
	 * If the error is not handled by any event handler, it will call
	 * {@link getErrorHandler errorHandler} to process the error.
	 *
	 * The application will be terminated by this method.
	 *
	 * @param integer the level of the error raised
	 * @param string the error message
	 * @param string the filename that the error was raised in
	 * @param integer the line number the error was raised at
	 */
	public function handleError($code,$message,$file,$line)
	{
		if($code & error_reporting())
		{
			// disable error capturing to avoid recursive errors
			restore_error_handler();
			restore_exception_handler();

			$log="$message ($file:$line)";
			if(isset($_SERVER['REQUEST_URI']))
				$log.=' REQUEST_URI='.$_SERVER['REQUEST_URI'];
			Yii::log($log,CLogger::LEVEL_ERROR,'php');

			$event=new CErrorEvent($this,$code,$message,$file,$line);
			$this->onError($event);
			if(!$event->handled)
			{
				// try an error handler
				if(($handler=$this->getErrorHandler())!==null)
					$handler->handle($event);
				else
					$this->displayError($code,$message,$file,$line);
			}
			$this->terminate(1);
		}
	}

	/**
	 * Raised when an uncaught PHP exception occurs.
	 *
	 * An event handler can set the {@link CExceptionEvent::handled handled}
	 * property of the event parameter to be true to indicate no further error
	 * handling is needed. Otherwise, the {@link getErrorHandler errorHandler}
	 * application component will continue processing the error.
	 *
	 * @param CExceptionEvent event parameter
	 */
	public function onException($event)
	{
		$this->raiseEvent('onException',$event);
	}

	/**
	 * Raised when a PHP execution error occurs.
	 *
	 * An event handler can set the {@link CErrorEvent::handled handled}
	 * property of the event parameter to be true to indicate no further error
	 * handling is needed. Otherwise, the {@link getErrorHandler errorHandler}
	 * application component will continue processing the error.
	 *
	 * @param CErrorEvent event parameter
	 */
	public function onError($event)
	{
		$this->raiseEvent('onError',$event);
	}

	/**
	 * Displays the captured PHP error.
	 * This method displays the error in HTML when there is
	 * no active error handler.
	 * @param integer error code
	 * @param string error message
	 * @param string error file
	 * @param string error line
	 */
	public function displayError($code,$message,$file,$line)
	{
		if(YII_DEBUG)
		{
			echo "<h1>PHP Error [$code]</h1>\n";
			echo "<p>$message ($file:$line)</p>\n";
			echo '<pre>';
			debug_print_backtrace();
			echo '</pre>';
		}
		else
		{
			echo "<h1>PHP Error [$code]</h1>\n";
			echo "<p>$message</p>\n";
		}
	}

	/**
	 * Displays the uncaught PHP exception.
	 * This method displays the exception in HTML when there is
	 * no active error handler.
	 * @param Exception the uncaught exception
	 */
	public function displayException($exception)
	{
		if(YII_DEBUG)
		{
			echo '<h1>'.get_class($exception)."</h1>\n";
			echo '<p>'.$exception->getMessage().' ('.$exception->getFile().':'.$exception->getLine().')</p>';
			echo '<pre>'.$exception->getTraceAsString().'</pre>';
		}
		else
		{
			echo '<h1>'.get_class($exception)."</h1>\n";
			echo '<p>'.$exception->getMessage().'</p>';
		}
	}

	/**
	 * @param string application component ID
	 * @return boolean whether the named application component exists (including both loaded and disabled.)
	 */
	public function hasComponent($id)
	{
		return isset($this->_components[$id]) || isset($this->_componentConfig[$id]);
	}

	/**
	 * Retrieves the named application component.
	 * @param string application component ID (case-sensitive)
	 * @return IApplicationComponent the application component instance, null if the application component is disabled or does not exist.
	 * @see hasComponent
	 */
	public function getComponent($id)
	{
		if(isset($this->_components[$id]))
			return $this->_components[$id];
		else if(isset($this->_componentConfig[$id]))
		{
			$config=$this->_componentConfig[$id];
			unset($this->_componentConfig[$id]);
			if(!isset($config['enabled']) || $config['enabled'])
			{
				Yii::trace("Loading \"$id\" application component",'system.core.CApplication');
				unset($config['enabled']);
				$component=CConfiguration::createObject($config);
				$component->init();
				return $this->_components[$id]=$component;
			}
		}
		return null;
	}

	/**
	 * Puts a component under the management of the application.
	 * The component will be initialized (by calling its {@link CApplicationComponent::init() init()}
	 * method if it has not done so.
	 * @param string component ID
	 * @param IApplicationComponent the component
	 */
	public function setComponent($id,$component)
	{
		$this->_components[$id]=$component;
		if(!$component->getIsInitialized())
			$component->init();
	}

	/**
	 * Configures the application with the specified configuration.
	 * @param mixed application configuration.
	 * If a string, it is treated as the path of the file that contains the configuration;
	 * If an array or CConfiguration, it is the actual configuration information.
	 * Please make sure you specify the {@link getBasePath basePath} property in the configuration,
	 * which should point to the root directory containing all application logic, template and data.
	 */
	protected function configure($config)
	{
		$config=new CConfiguration($config);
		if(($basePath=$config->remove('basePath'))===null)
			$basePath='protected';
		$this->setBasePath($basePath);
		Yii::setPathOfAlias('application',$this->getBasePath());
		$config->applyTo($this);
	}

	/**
	 * Initializes the class autoloader and error handlers.
	 */
	protected function initSystemHandlers()
	{
		if(YII_ENABLE_EXCEPTION_HANDLER)
			set_exception_handler(array($this,'handleException'));
		if(YII_ENABLE_ERROR_HANDLER)
			set_error_handler(array($this,'handleError'),error_reporting());
	}

	/**
	 * Registers the core application components.
	 * @see setComponents
	 */
	protected function registerCoreComponents()
	{
		$components=array(
			'coreMessages'=>array(
				'class'=>'CPhpMessageSource',
				'language'=>'en_us',
				'basePath'=>YII_PATH.DIRECTORY_SEPARATOR.'messages',
			),
			'db'=>array(
				'class'=>'CDbConnection',
			),
			'messages'=>array(
				'class'=>'CPhpMessageSource',
			),
			'errorHandler'=>array(
				'class'=>'CErrorHandler',
			),
			'securityManager'=>array(
				'class'=>'CSecurityManager',
			),
			'statePersister'=>array(
				'class'=>'CStatePersister',
			),
		);

		$this->setComponents($components);
	}

	/**
	 * Loads static application components.
	 */
	protected function preloadComponents()
	{
		foreach($this->preload as $id)
			$this->getComponent($id);
	}

	/**
	 * @return array the currently loaded components (indexed by their IDs)
	 */
	public function getComponents()
	{
		return $this->_components;
	}

	/**
	 * Sets the application components.
	 *
	 * When a configuration is used to specify a component, it should consist of
	 * the component's initial property values (name-value pairs). Additionally,
	 * a component can be enabled (default) or disabled by specifying the 'enabled' value
	 * in the configuration.
	 *
	 * If a configuration is specified with an ID that is the same as an existing
	 * component or configuration, the existing one will be replaced silently.
	 *
	 * The following is the configuration for two components:
	 * <pre>
	 * array(
	 *     'db'=>array(
	 *         'class'=>'CDbConnection',
	 *         'connectionString'=>'sqlite:path/to/file.db',
	 *     ),
	 *     'cache'=>array(
	 *         'class'=>'CDbCache',
	 *         'connectionID'=>'db',
	 *         'enabled'=>!YII_DEBUG,  // enable caching in non-debug mode
	 *     ),
	 * )
	 * </pre>
	 *
	 * @param array application components(id=>component configuration or instances)
	 */
	public function setComponents($components)
	{
		foreach($components as $id=>$component)
		{
			if($component instanceof IApplicationComponent)
				$this->setComponent($id,$component);
			else if(isset($this->_componentConfig[$id]))
				$this->_componentConfig[$id]=CMap::mergeArray($this->_componentConfig[$id],$component);
			else
				$this->_componentConfig[$id]=$component;
		}
	}
}


/**
 * CExceptionEvent represents the parameter for the {@link CApplication::onException onException} event.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.core
 * @since 1.0
 */
class CExceptionEvent extends CEvent
{
	/**
	 * @var CException the exception that this event is about.
	 */
	public $exception;

	/**
	 * Constructor.
	 * @param mixed sender of the event
	 * @param CException the exception
	 */
	public function __construct($sender,$exception)
	{
		$this->exception=$exception;
		parent::__construct($sender);
	}
}


/**
 * CErrorEvent represents the parameter for the {@link CApplication::onError onError} event.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.core
 * @since 1.0
 */
class CErrorEvent extends CEvent
{
	/**
	 * @var string error code
	 */
	public $code;
	/**
	 * @var string error message
	 */
	public $message;
	/**
	 * @var string error message
	 */
	public $file;
	/**
	 * @var string error file
	 */
	public $line;

	/**
	 * Constructor.
	 * @param mixed sender of the event
	 * @param string error code
	 * @param string error message
	 * @param string error file
	 * @param integer error line
	 */
	public function __construct($sender,$code,$message,$file,$line)
	{
		$this->code=$code;
		$this->message=$message;
		$this->file=$file;
		$this->line=$line;
		parent::__construct($sender);
	}
}
