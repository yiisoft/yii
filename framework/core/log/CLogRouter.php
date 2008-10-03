<?php
/**
 * CLogRouter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CLogRouter manages log routes that record log messages in different media.
 *
 * For example, a file log route {@link CFileLogRoute} records log messages
 * in log files. An email log route {@link CEmailLogRoute} sends log messages
 * to specific email addresses. See {@link CLogRoute} for more details about
 * different log routes.
 *
 * Log routes may be configured in application configuration like following:
 * <pre>
 * array(
 *     'preload'=>array('log'), // preload log component when app starts
 *     'components'=>array(
 *         'log'=>array(
 *             'class'=>'CLogRouter',
 *             'routes'=>array(
 *                 array(
 *                     'class'=>'CFileLogRoute',
 *                     'level'=>'trace, info',
 *                     'category'=>'system.*',
 *                 ),
 *                 array(
 *                     'class'=>'CEmailLogRoute',
 *                     'level'=>'error, warning',
 *                     'email'=>'admin@example.com',
 *                 ),
 *             ),
 *         ),
 *     ),
 * )
 * </pre>
 *
 * You can specify multiple routes with different filtering conditions and different
 * targets, even if the routes are of the same type.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.core.log
 * @since 1.0
 */
class CLogRouter extends CApplicationComponent
{
	private $_routes=array();
	private $_routeConfig=array();

	/**
	 * Initializes this application component.
	 * This method is required by the IApplicationComponent interface.
	 */
	public function init()
	{
		parent::init();
		foreach($this->_routeConfig as $config)
		{
			$class=$config->remove('class');
			$route=Yii::createComponent($class);
			$config->applyTo($route);
			$route->init();
			$this->_routes[]=$route;
		}
		Yii::app()->attachEventHandler('onEndRequest',array($this,'collectLogs'));
	}

	/**
	 * @return array the currently initialized routes
	 */
	public function getRoutes()
	{
		return $this->_routes;
	}

	/**
	 * @param array list of route configurations. Each element is an array configuring a route.
	 * A 'class' key is required to specify the class of the route.
	 */
	public function setRoutes($config)
	{
		$routes=$this->getRoutes();
		foreach($config as $c)
		{
			if(isset($c['class']))
				$this->_routeConfig[]=new CConfiguration($c);
			else
				throw new CException(Yii::t('yii#Log route configuration must have a "class" value.'));
		}
	}

	/**
	 * Collects log messages from a logger.
	 * This method is an event handler to application's onEndRequest event.
	 * @param mixed event parameter
	 */
	public function collectLogs($param)
	{
		$logger=Yii::getLogger();
		foreach($this->getRoutes() as $route)
			$route->collectLogs($logger);
	}
}
