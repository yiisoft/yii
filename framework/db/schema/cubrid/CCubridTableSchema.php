<?php
/**
 * CCubridTableSchema class file.
 *
 * @author Esen Sagynov <kadismal@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
/**
 * CCubridTableSchema represents the metadata for a CUBRID table.
 *
 * @author Esen Sagynov <kadismal@gmail.com>
 * @version $Id: CCubridTableSchema.php
 * @package system.db.schema.cubrid
 * @since 1.1.8
 */
class CCubridTableSchema extends CDbTableSchema
{
	/**
	 * @var string name of the schema (database) that this table belongs to.
	 * Defaults to null, meaning no schema (or the current database).
	 */
	public $schemaName;
}
