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
	 * Checks if inside a transaction
	 * 
	 * Checks if a transaction is currently active within the driver.
	 * This method always true if PHP below 5.4.0 to make it able to exec 'COMMIT TRANSACTION'
	 * @return boolean
	 */
	function inTransaction() 
	{
		if(version_compare(PHP_VERSION,'5.4.0','>='))
			return parent::inTransaction();
		else
			return true;
	}

	/**
	 * Begin a transaction
	 *
	 * Is is necessary to override pdo's method, as mssql pdo drivers
	 * does not support transaction for PHP below 5.4.0
	 *
	 * @return boolean
	 */
	#[ReturnTypeWillChange]
	public function beginTransaction ()
	{
		if(version_compare(PHP_VERSION,'5.4.0','>='))
			parent::beginTransaction();
		else
			$this->exec('BEGIN TRANSACTION');
		return true;
	}

	/**
	 * Commit a transaction
	 *
	 * Is is necessary to override pdo's method, as mssql pdo drivers
	 * does not support transaction for PHP below 5.4.0
	 *
	 * @return boolean
	 */
	#[ReturnTypeWillChange]
	public function commit ()
	{
		if(version_compare(PHP_VERSION,'5.4.0','>='))
			parent::commit();
		else
			$this->exec('COMMIT TRANSACTION');
		return true;
	}

	/**
	 * Rollback a transaction
	 *
	 * Is is necessary to override pdo's method, ac mssql pdo drivers
	 * does not support transaction for PHP below 5.4.0
	 *
	 * @return boolean
	 */
	#[ReturnTypeWillChange]
	public function rollBack ()
	{
		if(version_compare(PHP_VERSION,'5.4.0','>='))
			parent::rollBack();
		else
			$this->exec('ROLLBACK TRANSACTION');
		return true;
	}
}
