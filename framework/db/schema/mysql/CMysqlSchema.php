<?php
/**
 * CMysqlSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMysqlSchema is the class for retrieving metadata information from a MySQL database (version 4.1.x and 5.x).
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.schema.mysql
 * @since 1.0
 */
class CMysqlSchema extends CDbSchema
{
	/**
	 * Quotes a table name for use in a query.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 */
	public function quoteTableName($name)
	{
		return '`'.$name.'`';
	}

	/**
	 * Quotes a column name for use in a query.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 */
	public function quoteColumnName($name)
	{
		return '`'.$name.'`';
	}

	/**
	 * Compares two table names.
	 * The table names can be either quoted or unquoted. This method
	 * will consider both cases.
	 * @param string $name1 table name 1
	 * @param string $name2 table name 2
	 * @return boolean whether the two table names refer to the same table.
	 */
	public function compareTableNames($name1,$name2)
	{
		return parent::compareTableNames(strtolower($name1),strtolower($name2));
	}

	/**
	 * Resets the sequence value of a table's primary key.
	 * The sequence will be reset such that the primary key of the next new row inserted
	 * will have the specified value or 1.
	 * @param CDbTableSchema $table the table schema whose primary key sequence will be reset
	 * @param mixed $value the value for the primary key of the next new row inserted. If this is not set,
	 * the next new row's primary key will have a value 1.
	 * @since 1.1
	 */
	public function resetSequence($table,$value=null)
	{
		if($table->sequenceName!==null)
		{
			if($value===null)
				$value=$this->getDbConnection()->createCommand("SELECT MAX(`{$table->primaryKey}`) FROM {$table->rawName}")->queryScalar()+1;
			else
				$value=(int)$value;
			$this->getDbConnection()->createCommand("ALTER TABLE {$table->rawName} AUTO_INCREMENT=$value")->execute();
		}
	}

	/**
	 * Enables or disables integrity check.
	 * @param boolean $check whether to turn on or off the integrity check.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * @since 1.1
	 */
	public function checkIntegrity($check=true,$schema='')
	{
		$this->getDbConnection()->createCommand('SET FOREIGN_KEY_CHECKS='.($check?1:0))->execute();
	}

	/**
	 * Creates a table instance representing the metadata for the named table.
	 * @param string $name table name
	 * @return CMysqlTableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function createTable($name)
	{
		$table=new CMysqlTableSchema;
		$this->resolveTableNames($table,$name);

		if($this->findColumns($table))
		{
			$this->findConstraints($table);
			return $table;
		}
		else
			return null;
	}

	/**
	 * Generates various kinds of table names.
	 * @param CMysqlTableSchema $table the table instance
	 * @param string $name the unquoted table name
	 */
	protected function resolveTableNames($table,$name)
	{
		$parts=explode('.',str_replace('`','',$name));
		if(isset($parts[1]))
		{
			$table->schemaName=$parts[0];
			$table->name=$parts[1];
			$table->rawName=$this->quoteTableName($table->schemaName).'.'.$this->quoteTableName($table->name);
		}
		else
		{
			$table->name=$parts[0];
			$table->rawName=$this->quoteTableName($table->name);
		}
	}

	/**
	 * Collects the table column metadata.
	 * @param CMysqlTableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$sql='SHOW COLUMNS FROM '.$table->rawName;
		try
		{
			$columns=$this->getDbConnection()->createCommand($sql)->queryAll();
		}
		catch(Exception $e)
		{
			return false;
		}
		foreach($columns as $column)
		{
			$c=$this->createColumn($column);
			$table->columns[$c->name]=$c;
			if($c->isPrimaryKey)
			{
				if($table->primaryKey===null)
					$table->primaryKey=$c->name;
				else if(is_string($table->primaryKey))
					$table->primaryKey=array($table->primaryKey,$c->name);
				else
					$table->primaryKey[]=$c->name;
				if(strpos(strtolower($column['Extra']),'auto_increment')!==false)
					$table->sequenceName='';
			}
		}
		return true;
	}

	/**
	 * Creates a table column.
	 * @param array $column column metadata
	 * @return CDbColumnSchema normalized column metadata
	 */
	protected function createColumn($column)
	{
		$c=new CMysqlColumnSchema;
		$c->name=$column['Field'];
		$c->rawName=$this->quoteColumnName($c->name);
		$c->allowNull=$column['Null']==='YES';
		$c->isPrimaryKey=strpos($column['Key'],'PRI')!==false;
		$c->isForeignKey=false;
		$c->init($column['Type'],$column['Default']);
		return $c;
	}

	/**
	 * @return float server version.
	 */
	protected function getServerVersion()
	{
		$version=$this->getDbConnection()->getAttribute(PDO::ATTR_SERVER_VERSION);
		$digits=array();
		preg_match('/(\d+)\.(\d+)\.(\d+)/', $version, $digits);
		return floatval($digits[1].'.'.$digits[2].$digits[3]);
	}

	/**
	 * Collects the foreign key column details for the given table.
	 * @param CMysqlTableSchema $table the table metadata
	 */
	protected function findConstraints($table)
	{
		$row=$this->getDbConnection()->createCommand('SHOW CREATE TABLE '.$table->rawName)->queryRow();
		$matches=array();
		$regexp='/FOREIGN KEY\s+\(([^\)]+)\)\s+REFERENCES\s+([^\(^\s]+)\s*\(([^\)]+)\)/mi';
		foreach($row as $sql)
		{
			if(preg_match_all($regexp,$sql,$matches,PREG_SET_ORDER))
				break;
		}
		foreach($matches as $match)
		{
			$keys=array_map('trim',explode(',',str_replace('`','',$match[1])));
			$fks=array_map('trim',explode(',',str_replace('`','',$match[3])));
			foreach($keys as $k=>$name)
			{
				$table->foreignKeys[$name]=array(str_replace('`','',$match[2]),$fks[$k]);
				if(isset($table->columns[$name]))
					$table->columns[$name]->isForeignKey=true;
			}
		}
	}

	/**
	 * Returns all table names in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * If not empty, the returned table names will be prefixed with the schema name.
	 * @return array all table names in the database.
	 * @since 1.0.2
	 */
	protected function findTableNames($schema='')
	{
		if($schema==='')
			return $this->getDbConnection()->createCommand('SHOW TABLES')->queryColumn();
		$names=$this->getDbConnection()->createCommand('SHOW TABLES FROM '.$this->quoteTableName($schema))->queryColumn();
		foreach($names as &$name)
			$name=$schema.'.'.$name;
		return $names;
	}
}
