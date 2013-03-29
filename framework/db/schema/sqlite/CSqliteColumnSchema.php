<?php
/**
 * CSqliteColumnSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CSqliteColumnSchema class describes the column meta data of a SQLite table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.db.schema.sqlite
 * @since 1.0
 */
class CSqliteColumnSchema extends CDbColumnSchema
{
	/**
	 * Extracts the default value for the column.
	 * The value is typecasted to correct PHP type.
	 * @param mixed $defaultValue the default value obtained from metadata
	 */
	protected function extractDefault($defaultValue)
	{
		$this->defaultValue=$this->typecast(strcasecmp($defaultValue,'null') ? $defaultValue : null);

		if($this->type==='string' && $this->defaultValue!==null) // PHP 5.2.6 adds single quotes while 5.2.0 doesn't
			$this->defaultValue=trim($this->defaultValue,"'\"");
	}

	/**
	 * Converts the input value to the type that this column is of.
	 * @param mixed $value input value
	 * @return mixed converted value
	 */
	public function typecast($value)
	{
		$supposed_value = parent::typecast($value);

		if ( $this->type == 'integer' && PHP_INT_MAX < $value ) {
			return (float)$value;
		}

		return $supposed_value;
	}
}
