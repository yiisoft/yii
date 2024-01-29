<?php
/**
 * CPgsqlCommandBuilder class file.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @link https://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * CPgsqlCommandBuilder provides basic methods to create query commands for tables.
 *
 * @author Timur Ruziev <resurtm@gmail.com>
 * @package system.db.schema.pgsql
 * @since 1.1.14
 */
class CPgsqlCommandBuilder extends CDbCommandBuilder
{
	/**
	 * Returns default value of the integer/serial primary key. Default value means that the next
	 * autoincrement/sequence value would be used.
	 * @return string default value of the integer/serial primary key.
	 * @since 1.1.14
	 */
	protected function getIntegerPrimaryKeyDefaultValue()
	{
		return 'DEFAULT';
	}

    /**
     * Creates a conflict ignorant multiple INSERT command.
     * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
     * @param array[] $data list data to be inserted, each value should be an array in format (column name=>column value).
     * @return CDbCommand multiple insert command
     * @throws CDbException
     * @since 1.1.30
     */
    public function createMultipleInsertCommandWithIgnore($table, array $data) {
        return $this->composeMultipleInsertCommand($table, $data, array(
            "main" => "INSERT INTO {{tableName}} ({{columnInsertNames}}) VALUES {{rowInsertValues}} ON CONFLICT DO NOTHING",
        ));
    }
}
