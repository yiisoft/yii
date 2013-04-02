<?php
/**
 * CSqliteCommandBuilder class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CSqliteCommandBuilder provides basic methods to create query commands for SQLite tables.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.db.schema.sqlite
 * @since 1.0
 */
class CSqliteCommandBuilder extends CDbCommandBuilder
{
	/**
	 * Generates the expression for selecting rows with specified composite key values.
	 * This method is overridden because SQLite does not support the default
	 * IN expression with composite columns.
	 * @param CDbTableSchema $table the table schema
	 * @param array $values list of primary key values to be selected within
	 * @param string $prefix column prefix (ended with dot)
	 * @return string the expression for selection
	 */
	protected function createCompositeInCondition($table,$values,$prefix)
	{
		$keyNames=array();
		foreach(array_keys($values[0]) as $name)
			$keyNames[]=$prefix.$table->columns[$name]->rawName;
		$vs=array();
		foreach($values as $value)
			$vs[]=implode("||','||",$value);
		return implode("||','||",$keyNames).' IN ('.implode(', ',$vs).')';
	}

	/**
	 * Creates a multiple INSERT command.
	 * This method could be used to achieve better performance during insertion of the large
	 * amount of data into the database tables.
	 * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
	 * @param array[] $data list data to be inserted, each value should be an array in format (column name=>column value).
	 * If a key is not a valid column name, the corresponding value will be ignored.
	 * @return CDbCommand multiple insert command
	 */
	public function createMultipleInsertCommand($table,array $data)
	{
		$this->ensureTable($table);
		$params=array();
		$columnInsertNames=array();
		$rowInsertValues=array();

		$columns=array();
		foreach($data as $rowData)
		{
			foreach($rowData as $columnName=>$columnValue)
			{
				if(array_search($columnName,$columns,true)===false)
					if($table->getColumn($columnName)!==null)
						$columns[]=$columnName;
			}
		}
		foreach($columns as $name)
			$columnInsertNames[]=$this->getDbConnection()->quoteColumnName($name);

		foreach($data as $rowKey=>$rowData)
		{
			$columnInsertValues=array();
			foreach($columns as $columnName)
			{
				$column=$table->getColumn($columnName);
				$columnValue=array_key_exists($columnName,$rowData) ? $rowData[$columnName] : new CDbExpression('NULL');
				if($columnValue instanceof CDbExpression)
				{
					$columnInsertValue=$columnValue->expression;
					foreach($columnValue->params as $columnValueParamName=>$columnValueParam)
						$params[$columnValueParamName]=$columnValueParam;
				}
				else
				{
					$columnInsertValue=':'.$columnName.'_'.$rowKey;
					$params[':'.$columnName.'_'.$rowKey]=$column->typecast($columnValue);
				}
				$columnInsertValues[]=$columnInsertValue.' AS '.$columnName;
			}
			$rowInsertValues[]='SELECT '.implode(', ', $columnInsertValues);
		}

		$sql='INSERT INTO '.$this->getDbConnection()->quoteTableName($table->name)
			.' ('.implode(', ',$columnInsertNames).') '.implode(' UNION ',$rowInsertValues);
		$command=$this->getDbConnection()->createCommand($sql);

		foreach($params as $name=>$value)
			$command->bindValue($name,$value);

		return $command;
	}
}
