<?php
/**
 * CFilterWidget class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFilterWidget is the base class for widgets that can also be used as filters.
 *
 * Derived classes may need to override the following methods:
 * <ul>
 * <li>{@link CWidget::init()} : called when this is object is used as a widget and needs initialization.</li>
 * <li>{@link CWidget::run()} : called when this is object is used as a widget.</li>
 * <li>{@link filter()} : the filtering method called when this object is used as an action filter.</li>
 * </ul>
 *
 * CFilterWidget provides all properties and methods of {@link CWidget} and {@link CFilter}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.widgets
 * @since 1.0
 */
class CFilterWidget extends CWidget implements IFilter
{
	/**
	 * @var boolean whether to stop the action execution when this widget is used as a filter.
	 * This property should be changed only in {@link CWidget::init} method.
	 * Defaults to false, meaning the action should be executed.
	 */
	public $stopAction=false;
	/**
	 * Performs the filtering.
	 * The default implementation simply calls {@link init()},
	 * {@link CFilterChain::run()} and {@link run()} in order
	 * Derived classes may want to override this method to change this behavior.
	 * @param CFilterChain the filter chain that the filter is on.
	 */
	public function filter($filterChain)
	{
		$this->init();
		if(!$this->stopAction)
		{
			$filterChain->run();
			$this->run();
		}
	}
}