<?php
/**
 * CMssqlColumnSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMssqlColumnSchema class describes the column meta data of a MSSQL table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @version $Id$
 * @package system.db.schema.mssql
 * @since 1.0.4
 */
class CMssqlColumnSchema extends CDbColumnSchema
{
	/**
	 * Extracts the PHP type from DB type.
	 * @param string DB type
	 */
	protected function extractType($dbType)
	{
		if(strpos($dbType,'bigint')!==false || strpos($dbType,'float')!==false || strpos($dbType,'real')!==false)
			$this->type='double';
		else if(strpos($dbType,'int')!==false || strpos($dbType,'smallint')!==false || strpos($dbType,'tinyint'))
			$this->type='integer';
		else if(strpos($dbType,'bit')!==false)
			$this->type='boolean';
		else
			$this->type='string';
	}

	protected function extractDefault($defaultValue)
	{
		if($this->dbType==='timestamp' )
			$this->defaultValue=null;
		else
			parent::extractDefault(str_replace(array('(',')',"'"), '', $defaultValue));
	}

	/**
	 * Extracts size, precision and scale information from column's DB type.
	 * We do nothing here, since sizes and precisions have been computed before.
	 * @param string the column's DB type
	 */
	protected function extractLimit($dbType)
	{
	}
}
