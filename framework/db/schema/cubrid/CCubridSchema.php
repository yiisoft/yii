<?php
/**
 * CCubridSchema class file.
 *
 * @author Esen Sagynov <kadismal@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CCubridSchema is the class for retrieving metadata information from a CUBRID database (version 8.4.0 and later).
 *
 * @author Esen Sagynov <kadismal@gmail.com>
 * @package system.db.schema.cubrid
 * @since 1.1.16
 */
class CCubridSchema extends CDbSchema
{
	public $columnTypes=array(
		'pk' => 'INTEGER NOT NULL AUTO_INCREMENT PRIMARY KEY',
		// same as STRING or CHARACTER VARYING
		'string' => 'VARCHAR(255)',
		'text' => 'VARCHAR(65535)',
		'integer' => 'INTEGER',
		'float' => 'NUMERIC',
		'real' => 'NUMERIC',
		'decimal' => 'NUMERIC',
		'datetime' => 'DATETIME',
		'timestamp' => 'TIMESTAMP',
		'time' => 'TIME',
		'date' => 'DATE',
		'binary' => 'BIT VARYING',
		'bool' => 'SHORT',
		'boolean' => 'SHORT',
		'money' => 'NUMERIC(19,4)',
	);

	/**
	* Quotes a table name for use in a query.
	* A simple table name does not schema prefix.
	* @param string $name table name
	* @return string the properly quoted table name
	*/
	public function quoteSimpleTableName($name)
	{
		return '`'.$name.'`';
	}

	/**
	* Quotes a column name for use in a query.
	* A simple column name does not contain prefix.
	* @param string $name column name
	* @return string the properly quoted column name
	*/
	public function quoteSimpleColumnName($name)
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
	 * Creates a table instance representing the metadata for the named table.
	 * @param string $name table name
	 * @return CCubridTableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function loadTable($name)
	{
		$table=new CCubridTableSchema;
		$this->resolveTableNames($table,$name);

		if($this->findColumns($table))
		{
			$this->findPrimaryKeys($table);
			$this->findConstraints($table);
			return $table;
		}
		else
			return null;
	}

	/**
	 * Generates various kinds of table names.
	 * @param CCubridTableSchema $table the table instance
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
	 * @param CCubridTableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		// it may be good to use CUBRID PHP API to retrieve column info.
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
		$c=new CCubridColumnSchema;
		$c->name=$column['Field'];
		$c->rawName=$this->quoteColumnName($c->name);
		$c->allowNull=$column['Null']==='YES';
		$c->isPrimaryKey=strpos($column['Key'],'PRI')!==false;
		$c->isForeignKey=false;
		$c->init($column['Type'],$column['Default']);
		$c->autoIncrement=strpos(strtolower($column['Extra']),'auto_increment')!==false;

		return $c;
	}

	/**
	 * @return float server version.
	 */
	protected function getServerVersion()
	{
		$version=$this->getDbConnection()->getAttribute(PDO::ATTR_SERVER_VERSION);
		$digits=array();
		preg_match('/(\d+)\.(\d+)\.(\d+).(\d+)/', $version, $digits);
		return floatval($digits[1].'.'.$digits[2].$digits[3].'.'.$digits[4]);
	}

	/**
	 * Collects the foreign key column details for the given table.
	 * @param CCubridTableSchema $table the table metadata
	 */
	protected function findConstraints($table)
	{
		$schemas=$this->getDbConnection()->getPdoInstance()->cubrid_schema(PDO::CUBRID_SCH_IMPORTED_KEYS,$table->name);

		foreach($schemas as $schema)
		{
			$table->foreignKeys[$schema["FKCOLUMN_NAME"]]=array($schema["PKTABLE_NAME"],$schema["PKCOLUMN_NAME"]);
			if(isset($table->columns[$schema["FKCOLUMN_NAME"]]))
				$table->columns[$schema["FKCOLUMN_NAME"]]->isForeignKey=true;
		}
	}

	/**
	 * Collects the primary key column details for the given table.
	 * @param CCubridTableSchema $table the table metadata
	 */
	protected function findPrimaryKeys($table)
	{
		$pks=$this->getDbConnection()->getPdoInstance()->cubrid_schema(PDO::CUBRID_SCH_PRIMARY_KEY,$table->name);

		foreach($pks as $pk)
		{
			$c = $table->columns[$pk['ATTR_NAME']];
			$c->isPrimaryKey = true;

			if($table->primaryKey===null)
				$table->primaryKey=$c->name;
			elseif(is_string($table->primaryKey))
				$table->primaryKey=array($table->primaryKey,$c->name);
			else
				$table->primaryKey[]=$c->name;
			if($c->autoIncrement)
				$table->sequenceName='';
		}
	}

	/**
	 * Returns all table names in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * If not empty, the returned table names will be prefixed with the schema name.
	 * @return array all table names in the database.
	 */
	protected function findTableNames($schema='')
	{
		// CUBRID does not allow to look into another database from within another connection.
		// If necessary user has to establish a connection to that particular database and
		// query to show all tables. For this reason if a user executes this funtion
		// we will return all table names of the currently connected database.
		return $this->getDbConnection()->createCommand('SHOW TABLES')->queryColumn();
	}
}
