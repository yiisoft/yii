<?php
/**
 * CDbExpression class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbExpression represents a DB expression that does not need escaping.
 * CDbExpression is mainly used in {@link CActiveRecord} as attribute values.
 * When inserting or updating a {@link CActiveRecord}, attribute values of
 * type CDbExpression will be directly put into the corresponding SQL statement
 * without escaping. A typical usage is that an attribute is set with 'NOW()'
 * expression so that saving the record would fill the corresponding column
 * with the current DB server timestamp.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.schema
 * @since 1.0.2
 */
class CDbExpression extends CComponent
{
	/**
	 * @var string the DB expression
	 */
	public $expression;

	/**
	 * Constructor.
	 * @param string the DB expression
	 */
	public function __construct($expression)
	{
		$this->expression=$expression;
	}

	/**
	 * String magic method
	 * @return string the DB expression
	 */
	public function __toString()
	{
		return $this->expression;
	}
}