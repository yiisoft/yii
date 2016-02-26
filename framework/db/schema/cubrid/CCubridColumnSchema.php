<?php
/**
 * CCubridColumnSchema class file.
 *
 * @author Esen Sagynov <kadismal@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCubridColumnSchema class describes the column meta data of a CUBRID table.
  *
 * @author Esen Sagynov <kadismal@gmail.com>
 * @package system.db.schema.cubrid
 * @since 1.1.16
 */
class CCubridColumnSchema extends CDbColumnSchema
{
	/**
	 * Extracts the PHP type from DB type.
	 * @param string $dbType DB type
	 */
	protected function extractType($dbType)
	{
		if(preg_match('/(FLO|REA|DOU|NUM|DEC)/',$dbType))
			$this->type='double';
		// The following "bool" and 'boolean" are for future compatibility.
		// As of CUBRID 9.0, they are not supported.
		elseif(strpos($dbType,'BOOL')!==false)
			$this->type='boolean';
		elseif(preg_match('/(INT|BIT|SMA|SHO|NUM)/',$dbType))
			$this->type='integer';
		else
			$this->type='string';
	}

	/**
	 * Extracts the default value for the column.
	 * The value is typecasted to correct PHP type.
	 * @param mixed $defaultValue the default value obtained from metadata
	 */
	protected function extractDefault($defaultValue)
	{
		if($this->dbType==='TIMESTAMP' && $defaultValue==='CURRENT_TIMESTAMP')
			$this->defaultValue=null;
		else
			parent::extractDefault($defaultValue);
	}
}
