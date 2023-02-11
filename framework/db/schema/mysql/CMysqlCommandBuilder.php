<?php
/**
 * CMysqlCommandBuilder class file.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * CMysqlCommandBuilder provides basic methods to create query commands for tables.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @package system.db.schema.mysql
 * @since 1.1.13
 */
class CMysqlCommandBuilder extends CDbCommandBuilder
{
	/**
	 * Alters the SQL to apply JOIN clause.
	 * This method handles the mysql specific syntax where JOIN has to come before SET in UPDATE statement
	 * and for DELETE where JOIN has to be after FROM part.
	 * @param string $sql the SQL statement to be altered
	 * @param string $join the JOIN clause (starting with join type, such as INNER JOIN)
	 * @return string the altered SQL statement
	 */
	public function applyJoin($sql,$join)
	{
		if($join=='')
			return $sql;

		if(strpos($sql,'UPDATE')===0 && ($pos=strpos($sql,'SET'))!==false)
			return substr($sql,0,$pos).$join.' '.substr($sql,$pos);
		elseif(strpos($sql,'DELETE FROM ')===0)
		{
			$tableName=substr($sql,12);
			return "DELETE {$tableName} FROM {$tableName} ".$join;
		}
		else
			return $sql.' '.$join;
	}

	/**
	 * Alters the SQL to apply LIMIT and OFFSET.
	 * @param string $sql SQL query string without LIMIT and OFFSET.
	 * @param integer $limit maximum number of rows, -1 to ignore limit.
	 * @param integer $offset row offset, -1 to ignore offset.
	 * @return string SQL with LIMIT and OFFSET
	 */
	public function applyLimit($sql,$limit,$offset)
	{
		// Ugly, but this is how MySQL recommends doing it: https://dev.mysql.com/doc/refman/5.0/en/select.html
		if($limit<=0 && $offset>0)
			$limit=PHP_INT_MAX;
		return parent::applyLimit($sql,$limit,$offset);
	}
}
