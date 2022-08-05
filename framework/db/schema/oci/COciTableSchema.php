<?php
/**
 * COciTableSchema class file.
 *
 * @author Ricardo Grana <rickgrana@yahoo.com.br>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * COciTableSchema represents the metadata for an Oracle table.
 *
 * @author Ricardo Grana <rickgrana@yahoo.com.br>
 * @package system.db.schema.oci
 */
class COciTableSchema extends CDbTableSchema
{
	/**
	 * @var string name of the schema (database) that this table belongs to.
	 * Defaults to null, meaning no schema (or the current database).
	 */
	public $schemaName;
}
