<?php
/**
 * GiiModule class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

Yii::import('system.gii.components.Controller');
Yii::import('system.gii.CCodeGenerator');
Yii::import('system.gii.CCodeModel');
Yii::import('system.gii.CCodeFile');

/**
 * GiiModule is a module that provides Web-based code generation capabilities.
 *
 * To use GiiModule, you must include it as a module in the application configuration like the following:
 * <pre>
 * return array(
 *     ......
 *     'modules'=>array(
 *         'gii'=>array(
 *             'class'=>'system.gii.GiiModule',
 *             'password'=>***choose a password***
 *         ),
 *     ),
 * )
 * </pre>
 *
 * Because GiiModule generates new code files on the server, you should only use it on your own
 * development machine. To prevent other people from using this module, it is required that
 * you should specify the username and password in the configuration. Later when you access
 * the module via browser, you will be prompted to enter these credential information.
 *
 * By default, GiiModule can only be accessed by localhost. You may configure its {@link ipFilters}
 * property if you want to make it accessible on other machines.
 *
 * With the above configuration, you will be able to access GiiModule in your browser using
 * the following URL:
 *
 * http://localhost/path/to/index.php?r=gii
 *
 * If your application is using path-format URLs with some customized URL rules, you may need to add
 * the following URLs in your application configuration in order to access GiiModule:
 * <pre>
 * 'components'=>array(
 *     'urlManager'=>array(
 *         'urlFormat'=>'path',
 *         'rules'=>array(
 *             'gii'=>'gii',
 *             'gii/&lt;controller:\w+>'=>'gii/&lt;controller>',
 *             'gii/&lt;controller:\w+>/&lt;action:\w+>'=>'gii/&lt;controller>/&lt;action>',
 *             ...other rules...
 *         ),
 *     )
 * )
 * </pre>
 *
 * You can then access GiiModule via:
 *
 * http://localhost/path/to/index.php/gii
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.gii
 * @since 1.1.2
 */
class GiiModule extends CWebModule
{
	/**
	 * @var string the password that can be used to access GiiModule.
	 * If this property is set false, then GiiModule can be accessed without any prompt for password.
	 */
	public $password;
	/**
	 * @var array the IP filters that specify which IP addresses are allowed to access GiiModule.
	 * Each array element represents a single filter. A filter can be either an IP address
	 * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
	 * The default value is array('127.0.0.1'), which means GiiModule can only be accessed
	 * on the localhost.
	 */
	public $ipFilters=array('127.0.0.1');
	/**
	 * @var array a list of directories that may contain code generators.
	 */
	public $generatorPaths=array();

	private $_assetsUrl;

	public function init()
	{
		parent::init();
		Yii::app()->setComponents(array(
			'errorHandler'=>array(
				'errorAction'=>'gii/default/error',
			),
			'user'=>array(
				'class'=>'CWebUser',
				'stateKeyPrefix'=>'gii',
				'loginUrl'=>array('gii/default/login'),
			),
		));
		$this->generatorPaths[]=Yii::getPathOfAlias('gii.generators');
		$this->controllerMap=$this->findGenerators();
	}

	public function getAssetsUrl()
	{
		if($this->_assetsUrl===null)
			$this->_assetsUrl=Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('gii.assets'),false,-1,YII_DEBUG);
		return $this->_assetsUrl;
	}

	public function setAssetsUrl($value)
	{
		$this->_assetsUrl=$value;
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			$route=$controller->uniqueId.'/'.$action->id;
			if(!$this->allowIp(Yii::app()->request->userHostAddress) && $route!=='gii/default/error')
				throw new CHttpException(403,"You are not allowed to access this page.");

			$publicPages=array(
				'gii/default/login',
				'gii/default/error',
			);
			$route=$controller->uniqueId.'/'.$action->id;
			if($this->password!==false && Yii::app()->user->isGuest && !in_array($route,$publicPages))
				Yii::app()->user->loginRequired();
			else
				return true;
		}
		else
			return false;
	}

	protected function allowIp($ip)
	{
		if(empty($this->ipFilters))
			return true;
		foreach($this->ipFilters as $filter)
		{
			if($filter==='*' || $filter===$ip || (($pos=strpos($filter,'*'))!==false && !strncmp($ip,$filter,$pos)))
				return true;
		}
		return false;
	}

	protected function findGenerators()
	{
		$generators=array();
		$n=count($this->generatorPaths);
		for($i=$n-1;$i>=0;--$i)
		{
			$path=$this->generatorPaths[$i];
			Yii::setPathOfAlias('gii'.$i,$path);
			$names=scandir($path);
			foreach($names as $name)
			{
				if($name[0]!=='.' && is_dir($path.'/'.$name))
				{
					$className=ucfirst($name).'Generator';
					if(is_file("$path/$name/$className.php"))
					{
						$generators[$name]=array(
							'class'=>"gii{$i}.$name.$className",
						);
					}
					else if(isset($generators[$name]) && is_dir("$path/$name/templates"))
					{
						$generators[$name]['templatePath']="$path/$name/templates";
					}
				}
			}
		}
		return $generators;
	}
}