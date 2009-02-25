<?php
/**
 * CWebModule class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CWebModule represents an application module.
 *
 * An application module may be considered as a self-contained sub-application
 * that has its own controllers, models and views and can be reused in a different
 * project as a whole. Controllers inside a module must be accessed with routes
 * that are prefixed with the module ID.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0.3
 */
class CWebModule extends CComponent
{
	/**
	 * @var string the ID of the default controller for this module. Defaults to 'default'.
	 */
	public $defaultController='default';
	/**
	 * @var array the IDs of the module components that should be preloaded.
	 */
	public $preload=array();
	/**
	 * @var mixed the layout that is shared by the controllers inside this module.
	 * If a controller has explicitly declared its own {@link CController::layout layout},
	 * this property will be ignored.
	 * If this is null (default), the application's layout or the parent module's layout (if available)
	 * will be used. If this is false, then no layout will be used.
	 */
	public $layout;
	/**
	 * @var array mapping from controller ID to controller configurations.
	 * Pleaser refer to {@link CWebApplication::controllerMap} for more details.
	 */
	public $controllerMap=array();
	/**
	 * @var array the behaviors that should be attached to the module.
	 * The behaviors will be attached to the application when {@link init} is called.
	 * Please refer to {@link CModel::behaviors} on how to specify the value of this property.
	 */
	public $behaviors=array();

	private $_id;
	private $_parentModule;
	private $_params;
	private $_basePath;
	private $_moduleConfig=array();
	private $_modules=array();
	private $_components=array();
	private $_componentConfig=array();

	/**
	 * Constructor.
	 * @param string the ID of this module
	 * @param CWebModule the parent module (if any)
	 */
	public function __construct($id, $parent=null)
	{
		$this->_id=$id;
		$this->_parentModule=$parent;
	}

	/**
	 * Getter magic method.
	 * This method is overridden to support accessing module components
	 * like reading module properties.
	 * @param string module component or property name
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
	 * Checks if a property value is null.
	 * This method overrides the parent implementation by checking
	 * if the named module component is loaded.
	 * @param string the property name or the event name
	 * @return boolean whether the property value is null
	 * @since 1.0.1
	 */
	public function __isset($name)
	{
		if($this->hasComponent($name))
			return $this->getComponent($name)!==null;
		else
			return parent::__isset($name);
	}

	/**
	 * Initializes this module.
	 * This method is invoked automatically when the module is initially created.
	 * You may override this method to customize the module or the application.
	 * Make sure you call the parent implementation so that the module gets configured.
	 * @param mixed the configuration array or a PHP script returning the configuration array.
	 * The configuration will be applied to this module.
	 */
	public function init($config)
	{
		$this->configure($config);
		$this->attachBehaviors($this->behaviors);
		$this->preloadComponents();
	}

	/**
	 * @return string the module ID.
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * @return CWebModule the parent module. Null if this module does not have parent.
	 */
	public function getParentModule()
	{
		return $this->_parentModule;
	}

	/**
	 * Returns the name of this module.
	 * The default implementation simply returns {@link id}.
	 * You may override this method to customize the name of this module.
	 * @return string the name of this module.
	 */
	public function getName()
	{
		return basename($this->_id);
	}

	/**
	 * Returns the description of this module.
	 * The default implementation returns an empty string.
	 * You may override this method to customize the description of this module.
	 * @return string the description of this module.
	 */
	public function getDescription()
	{
		return '';
	}

	/**
	 * Returns the version of this module.
	 * The default implementation returns '1.0'.
	 * You may override this method to customize the version of this module.
	 * @return string the version of this module.
	 */
	public function getVersion()
	{
		return '1.0';
	}

	/**
	 * @return CAttributeCollection the list of module parameters
	 */
	public function getParams()
	{
		if($this->_params!==null)
			return $this->_params;
		else
			return $this->_params=new CAttributeCollection;
	}

	/**
	 * @param array module parameters. This should be in name-value pairs.
	 */
	public function setParams($value)
	{
		$params=$this->getParams();
		foreach($value as $k=>$v)
			$params->add($k,$v);
	}

	/**
	 * @return string the root directory of the module. Defaults to the directory containing the module class.
	 */
	public function getBasePath()
	{
		if($this->_basePath===null)
		{
			$class=new ReflectionClass(get_class($this));
			$this->_basePath=dirname($class->getFileName());
		}
		return $this->_basePath;
	}

	/**
	 * @return string the directory that contains the controller classes. Defaults to the "controllers" sub-directory of {@link basePath}.
	 */
	public function getControllerPath()
	{
		return $this->getBasePath().DIRECTORY_SEPARATOR.'controllers';
	}

	/**
	 * @return string the directory that contains the modules. Defaults to the "modules" sub-directory of {@link basePath}.
	 */
	public function getModulePath()
	{
		return $this->getBasePath().DIRECTORY_SEPARATOR.'modules';
	}

	/**
	 * @return string the root directory of view files. Defaults to the "views" sub-directory of {@link basePath}.
	 */
	public function getViewPath()
	{
		return $this->getBasePath().DIRECTORY_SEPARATOR.'views';
	}

	/**
	 * @return string the root directory of layout files. Defaults to the "layouts" sub-directory of {@link viewPath}.
	 */
	public function getLayoutPath()
	{
		return $this->getViewPath().DIRECTORY_SEPARATOR.'layouts';
	}

	/**
	 * Retrieves the named application module.
	 * @param string application module ID (case-sensitive)
	 * @return CWebModule the application module instance, null if the application module is disabled or does not exist.
	 */
	public function getModule($id)
	{
		if(isset($this->_modules[$id]))
			return $this->_modules[$id];
		else if(isset($this->_moduleConfig[$id]))
		{
			$config=$this->_moduleConfig[$id];
			unset($this->_moduleConfig[$id]);
			if(($module=Yii::app()->createModule($id,$config,$this))!==null)
				return $this->_modules[$id]=$module;
		}
	}

	/**
	 * @return array the currently loaded application modules (indexed by their IDs)
	 */
	public function getModules()
	{
		return $this->_modules;
	}

	/**
	 * Configures the modules belonging to this module.
	 *
	 * Call this method to declare sub-modules and configure them with their initial property values.
	 * The parameter should be an array of module configurations. Each array element represents a single module,
	 * which can be either a string representing the module ID or an ID-config pair representing
	 * a module with the specified ID and the initial property values.
	 *
	 * For example, the following array declares two modules:
	 * <pre>
	 * array(
	 *     'admin',
	 *     'payment'=>array(
	 *         'server'=>'paymentserver.com',
	 *     ),
	 * )
	 * </pre>
	 *
	 * By default, the module class is determined using the expression <code>ucfirst($moduleID).'Module'</code>.
	 * And the class file is located under <code>modules/$moduleID</code>.
	 * You may override this default by explicitly specifying the 'class' option in the configuration.
	 *
	 * You may also enable or disable a module by specifying the 'enabled' option in the configuration.
	 *
	 * @param array application module configuration.
	 */
	public function setModules($modules)
	{
		foreach($modules as $id=>$module)
		{
			if(is_int($id))
			{
				$id=$module;
				$module=array();
			}
			if(!isset($module['class']))
				$module['classFile']=$this->getModulePath().DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.ucfirst($id).'Module.php';

			if(isset($this->_moduleConfig[$id]))
				$this->_moduleConfig[$id]=CMap::mergeArray($this->_moduleConfig[$id],$module);
			else
				$this->_moduleConfig[$id]=$module;
		}
	}

	/**
	 * Loads static module components.
	 */
	protected function preloadComponents()
	{
		foreach($this->preload as $id)
			$this->getComponent($id);
	}

	/**
	 * @param string module component ID
	 * @return boolean whether the named module component exists (including both loaded and disabled.)
	 */
	public function hasComponent($id)
	{
		return isset($this->_components[$id]) || isset($this->_componentConfig[$id]);
	}

	/**
	 * Retrieves the named module component.
	 * @param string application component ID (case-sensitive)
	 * @return IApplicationComponent the module component instance, null if the module component is disabled or does not exist.
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
				Yii::trace("Loading \"$id\" module component",'system.web.CWebModule');
				unset($config['enabled']);
				$component=Yii::createComponent($config);
				$component->init();
				return $this->_components[$id]=$component;
			}
		}
	}

	/**
	 * Puts a component under the management of the module.
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
	 * @return array the currently loaded components (indexed by their IDs)
	 */
	public function getComponents()
	{
		return $this->_components;
	}

	/**
	 * Sets the module components.
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
	 * @param array module components(id=>component configuration or instances)
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

	/**
	 * Configures the module with the specified configuration.
	 * @param mixed the configuration array or a PHP script returning the configuration array.
	 */
	public function configure($config)
	{
		if(is_string($config))
			$config=require($config);
		if(is_array($config))
		{
			foreach($config as $key=>$value)
				$this->$key=$value;
		}
	}
}
