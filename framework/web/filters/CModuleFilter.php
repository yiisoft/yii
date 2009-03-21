<?php
/**
 * CModuleFilter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CModuleFilter represents a filter that invokes the {@link CWebModule::filterControllerAction filterControllerAction} method.
 * This filter is mainly internally used so that a module gets a chance
 * to filter controller actions.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.filters
 * @since 1.0.4
 */
class CModuleFilter extends CFilter
{
	/**
	 * Performs the filtering.
	 * This method calls the {@link module}'s {@link CWebModule::filterControllerAction filterControllerAction} method.
	 * @param CFilterChain the filter chain that the filter is on.
	 */
	public function filter($filterChain)
	{
		if(($module=$filterChain->controller->getModule())!==null)
			$module->filterControllerAction($filterChain);
		else
			$filterChain->run();
	}
}