<?php
/**
 * CInlineFilter class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CInlineFilter represents a filter defined as a controller method.
 *
 * CInlineFilter executes the 'filterXYZ($action)' method defined
 * in the controller, where the name 'XYZ' can be retrieved from the {@link name} property.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web.filters
 * @since 1.0
 */
class CInlineFilter extends CFilter
{
	/**
	 * @var string name of the filter. It stands for 'XYZ' in the filter method name 'filterXYZ'.
	 */
	public $name;

	/**
	 * Creates an inline filter instance.
	 * The creation is based on a string describing the inline method name
	 * and action names that the filter shall or shall not apply to.
	 * @param CController the controller who hosts the filter methods
	 * @param string the filter name
	 * @return CInlineFilter the created instance, null if the spec indicates the filter does not apply to the action
	 * @throws CException if filter methods are not found in the controller
	 */
	public static function create($controller,$filterName)
	{
		$filter=new CInlineFilter;
		$filter->name=$filterName;
		return $filter;
	}

	/**
	 * Performs the filtering.
	 * This method calls the filter method defined in the controller class.
	 * @param CFilterChain the filter chain that the filter is on.
	 */
	public function filter($filterChain)
	{
		$method='filter'.$this->name;
		if(method_exists($filterChain->controller,$method))
			$filterChain->controller->$method($filterChain);
		else
			throw new CException(Yii::t('yii##Filter "{filter}" is invalid. Controller "{class}" does have the filter method "filter{filter}".',
				array('{filter}'=>$this->name, '{class}'=>get_class($filterChain->controller))));
	}
}