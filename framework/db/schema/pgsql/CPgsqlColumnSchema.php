<?php
/**
 * CPgsqlColumnSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CPgsqlColumnSchema class describes the column meta data of a PostgreSQL table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.db.schema.pgsql
 * @since 1.0
 */
class CPgsqlColumnSchema extends CDbColumnSchema
{
	/**
	 * Extracts the PHP type from DB type.
	 * @param string $dbType DB type
	 */
	protected function extractType($dbType)
	{
		if(strpos($dbType,'[')!==false || strpos($dbType,'char')!==false || strpos($dbType,'text')!==false)
			$this->type='string';
		elseif(strpos($dbType,'bool')!==false)
			$this->type='boolean';
		elseif(preg_match('/(real|float|double)/',$dbType))
			$this->type='double';
		elseif(preg_match('/(integer|oid|serial|smallint)/',$dbType))
			$this->type='integer';
		else
			$this->type='string';
	}

	/**
	 * Extracts size, precision and scale information from column's DB type.
	 * @param string $dbType the column's DB type
	 */
	protected function extractLimit($dbType)
	{
		if(strpos($dbType,'('))
		{
			if (preg_match('/^time.*\((.*)\)/',$dbType,$matches))
			{
				$this->precision=(int)$matches[1];
			}
			elseif (preg_match('/\((.*)\)/',$dbType,$matches))
			{
				$values=explode(',',$matches[1]);
				$this->size=$this->precision=(int)$values[0];
				if(isset($values[1]))
					$this->scale=(int)$values[1];
			}
		}
	}

	/**
	 * Extracts the default value for the column.
	 * The value is typecasted to correct PHP type.
	 * @param mixed $defaultValue the default value obtained from metadata
	 */
	protected function extractDefault($defaultValue)
	{
		if($defaultValue==='true')
			$this->defaultValue=true;
		elseif($defaultValue==='false')
			$this->defaultValue=false;
		elseif(strpos($defaultValue,'nextval')===0)
			$this->defaultValue=null;
		elseif(preg_match('/^\'(.*)\'::/',$defaultValue,$matches))
			$this->defaultValue=$this->typecast(str_replace("''","'",$matches[1]));
		elseif(preg_match('/^(-?\d+(\.\d*)?)(::.*)?$/',$defaultValue,$matches))
			$this->defaultValue=$this->typecast($matches[1]);
		// else is null
	}
}
