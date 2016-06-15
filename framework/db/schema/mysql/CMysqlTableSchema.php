<?php
/**
 * CMysqlTableSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yeeframework.com/
 * @copyright 2008-2013 Yee Software LLC
 * @license http://www.yeeframework.com/license/
 */

/**
 * CMysqlTableSchema represents the metadata for a MySQL table.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.db.schema.mysql
 * @since 1.0
 */
class CMysqlTableSchema extends CDbTableSchema
{
	/**
	 * @var string name of the schema (database) that this table belongs to.
	 * Defaults to null, meaning no schema (or the current database).
	 */
	public $schemaName;
}
