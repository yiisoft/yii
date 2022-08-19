<?php
/**
 * CMssqlSqlsrvPdoAdapter class file.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * This is an extension of default PDO class for MSSQL SQLSRV driver only.
 * It provides workaround of the improperly implemented functionalities of PDO SQLSRV driver.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @package system.db.schema.mssql
 * @since 1.1.13
 */
class CMssqlSqlsrvPdoAdapter extends PDO
{
	/**
	 * Returns last inserted ID value.
	 * Before version 5.0, the SQLSRV driver supports PDO::lastInsertId() with one peculiarity: when $sequence's 
	 * value is null or empty string it returns empty string. But when parameter is not specified at all it's working as 
	 * expected and returns actual last inserted ID (like other PDO drivers).
	 * Version 5.0 of the Microsoft PHP Drivers for SQL Server changes the behaviour of PDO::lastInsertID to be 
	 * consistent with the behaviour outlined in the PDO documentation. It returns the ID of the 
	 * last inserted sequence or row.
	 *
	 * @param string|null $sequence the sequence/table name. Defaults to null.
	 * @return integer last inserted ID value.
	 */
	public function lastInsertId($sequence=null)
	{
		$parts = explode('.', phpversion('pdo_sqlsrv'));
		$sqlsrvVer = phpversion('pdo_sqlsrv') ? intval(array_shift($parts)) : 0;

		if(!$sequence || $sqlsrvVer >= 5)
			return parent::lastInsertId();
		return parent::lastInsertId($sequence);
	}
}
