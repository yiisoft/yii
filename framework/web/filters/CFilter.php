<?php
/**
 * CFilter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFilter is the base class for all filters.
 *
 * A filter can be applied before and after an action is executed.
 * It can modify the context that the action is to run or decorate the result that the
 * action generates.
 *
 * Override {@link filter()} to specify the filtering logic that should be applied
 * around the action.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.filters
 * @since 1.0
 */
class CFilter extends CComponent implements IFilter
{
	/**
	 * Performs the filtering.
	 * The default implementation simply continues the action execution.
	 * Derived classes may want to override this method to change this behavior.
	 * Note, in order to continue the execution of action,
	 * you must call <code>$filterChain->run()</code> inside this method.
	 * @param CFilterChain the filter chain that the filter is on.
	 */
	public function filter($filterChain)
	{
		$filterChain->run();
	}
}