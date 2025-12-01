<?php
/**
 * CMssqlPdo class file
 *
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * This is an extension of default PDO class for mssql driver only
 * It provides some missing functionalities of pdo driver
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @package system.db.schema.mssql
 */
class CMssqlPdoAdapter extends PDO
{
	/**
	 * Get the last inserted id value
	 * MSSQL doesn't support sequence, so, argument is ignored
	 *
	 * @param string|null $sequence sequence name. Defaults to null
	 * @return integer last inserted id
	 */
	#[ReturnTypeWillChange]
	public function lastInsertId ($sequence=NULL)
	{
		return $this->query('SELECT CAST(COALESCE(SCOPE_IDENTITY(), @@IDENTITY) AS bigint)')->fetchColumn();
	}

	/**
	 * Begin a transaction
	 *
	 * Is is necessary to override pdo's method, as mssql pdo drivers
	 * does not support transaction
	 *
	 * @return boolean
	 */
	#[ReturnTypeWillChange]
	public function beginTransaction ()
	{
		$this->exec('BEGIN TRANSACTION');
		return true;
	}

	/**
	 * Commit a transaction
	 *
	 * Is is necessary to override pdo's method, as mssql pdo drivers
	 * does not support transaction
	 *
	 * @return boolean
	 */
	#[ReturnTypeWillChange]
	public function commit ()
	{
		$this->exec('COMMIT TRANSACTION');
		return true;
	}

	/**
	 * Rollback a transaction
	 *
	 * Is is necessary to override pdo's method, ac mssql pdo drivers
	 * does not support transaction
	 *
	 * @return boolean
	 */
	#[ReturnTypeWillChange]
	public function rollBack ()
	{
		$this->exec('ROLLBACK TRANSACTION');
		return true;
	}
}
