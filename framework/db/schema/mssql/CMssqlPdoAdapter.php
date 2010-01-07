<?php
/**
 * CMssqlPdo class file
 *
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * This is an extension of default PDO class for mssql driver only
 * It provides some missing functionalities of pdo driver
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @version $Id$
 * @package system.db.schema.mssql
 * @since 1.0.4
 */
class CMssqlPdoAdapter extends PDO
{
	/**
	 * Get the last inserted id value
	 * MSSQL doesn't support sequence, so, argument is ignored
	 *
	 * @param string sequence name. Defaults to null
	 * @return int last inserted id
	 */
	public function lastInsertId ($sequence=NULL)
	{
		return $this->query('SELECT SCOPE_IDENTITY()')->fetchColumn();
	}

	/**
	 * Begin a transaction
	 *
	 * Is is necessary to override pdo's method, as mssql pdo drivers
	 * does not support transaction
	 *
	 * @return boolean
	 */
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
	public function rollBack ()
	{
		$this->exec('ROLLBACK TRANSACTION');
		return true;
	}
}
