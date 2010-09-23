<?php
/**
 * CMssqlSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMssqlSchema is the class for retrieving metadata information from a MS SQL Server database.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @author Christophe Boulain <Christophe.Boulain@gmail.com>
 * @version $Id$
 * @package system.db.schema.mssql
 * @since 1.0.4
 */
class CMssqlSchema extends CDbSchema
{
	const DEFAULT_SCHEMA='dbo';


	/**
	 * Quotes a table name for use in a query.
	 * @param string $name table name
	 * @return string the properly quoted table name
	 */
	public function quoteTableName($name)
	{
		if (strpos($name,'.')===false)
			return '['.$name.']';
		$names=explode('.',$name);
		foreach ($names as &$n)
			$n = '['.$n.']';
		return implode('.',$names);
	}

	/**
	 * Quotes a column name for use in a query.
	 * @param string $name column name
	 * @return string the properly quoted column name
	 */
	public function quoteColumnName($name)
	{
		return '['.$name.']';
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
		$name1=str_replace(array('[',']'),'',$name1);
		$name2=str_replace(array('[',']'),'',$name2);
		return parent::compareTableNames(strtolower($name1),strtolower($name2));
	}

	/**
	 * Creates a table instance representing the metadata for the named table.
	 * @param string $name table name
	 * @return CMssqlTableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function createTable($name)
	{
		$table=new CMssqlTableSchema;
		$this->resolveTableNames($table,$name);
		//if (!in_array($table->name, $this->tableNames)) return null;
		$table->primaryKey=$this->findPrimaryKey($table);
		$table->foreignKeys=$this->findForeignKeys($table);
		if($this->findColumns($table))
		{
			return $table;
		}
		else
			return null;
	}

	/**
	 * Generates various kinds of table names.
	 * @param CMssqlTableSchema $table the table instance
	 * @param string $name the unquoted table name
	 */
	protected function resolveTableNames($table,$name)
	{
		$parts=explode('.',str_replace(array('[',']'),'',$name));
		if(($c=count($parts))==3)
		{
			// Catalog name, schema name and table name provided
			$table->catalogName=$parts[0];
			$table->schemaName=$parts[1];
			$table->name=$parts[2];
			$table->rawName=$this->quoteTableName($table->catalogName).'.'.$this->quoteTableName($table->schemaName).'.'.$this->quoteTableName($table->name);
		}
		elseif ($c==2)
		{
			// Only schema name and table name provided
			$table->name=$parts[1];
			$table->schemaName=$parts[0];
			$table->rawName=$this->quoteTableName($table->schemaName).'.'.$this->quoteTableName($table->name);
		}
		else
		{
			// Only the name given, we need to get at least the schema name
			//if (empty($this->_schemaNames)) $this->findTableNames();
			$table->name=$parts[0];
			$table->schemaName=self::DEFAULT_SCHEMA;
			$table->rawName=$this->quoteTableName($table->schemaName).'.'.$this->quoteTableName($table->name);
		}
	}

	/**
	 * Gets the primary key column(s) details for the given table.
	 * @param CMssqlTableSchema $table table
	 * @return mixed primary keys (null if no pk, string if only 1 column pk, or array if composite pk)
	 */
	protected function findPrimaryKey($table)
	{
		$kcu='INFORMATION_SCHEMA.KEY_COLUMN_USAGE';
		$tc='INFORMATION_SCHEMA.TABLE_CONSTRAINTS';
		if (isset($table->catalogName))
		{
			$kcu=$table->catalogName.'.'.$kcu;
			$tc=$table->catalogName.'.'.$tc;
		}

		$sql = <<<EOD
		SELECT k.column_name field_name
			FROM {$this->quoteTableName($kcu)} k
		    LEFT JOIN {$this->quoteTableName($tc)} c
		      ON k.table_name = c.table_name
		     AND k.constraint_name = c.constraint_name
		   WHERE c.constraint_type ='PRIMARY KEY'
		   	    AND k.table_name = :table
				AND k.table_schema = :schema
EOD;
		$command = $this->getDbConnection()->createCommand($sql);
		$command->bindValue(':table', $table->name);
		$command->bindValue(':schema', $table->schemaName);
		$primary=$command->queryColumn();
		switch (count($primary))
		{
			case 0: // No primary key on table
				$primary=null;
				break;
			case 1: // Only 1 primary key
				$primary=$primary[0];
				break;
		}
		return $primary;
	}

	/**
	 * Gets foreign relationship constraint keys and table name
	 * @param CMssqlTableSchema $table table
	 * @return array foreign relationship table name and keys.
	 */
	protected function findForeignKeys($table)
	{
		$rc='INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS';
		$kcu='INFORMATION_SCHEMA.KEY_COLUMN_USAGE';
		if (isset($table->catalogName))
		{
			$kcu=$table->catalogName.'.'.$kcu;
			$rc=$table->catalogName.'.'.$rc;
		}

		//From http://msdn2.microsoft.com/en-us/library/aa175805(SQL.80).aspx
		$sql = <<<EOD
		SELECT
		     KCU1.CONSTRAINT_NAME AS 'FK_CONSTRAINT_NAME'
		   , KCU1.TABLE_NAME AS 'FK_TABLE_NAME'
		   , KCU1.COLUMN_NAME AS 'FK_COLUMN_NAME'
		   , KCU1.ORDINAL_POSITION AS 'FK_ORDINAL_POSITION'
		   , KCU2.CONSTRAINT_NAME AS 'UQ_CONSTRAINT_NAME'
		   , KCU2.TABLE_NAME AS 'UQ_TABLE_NAME'
		   , KCU2.COLUMN_NAME AS 'UQ_COLUMN_NAME'
		   , KCU2.ORDINAL_POSITION AS 'UQ_ORDINAL_POSITION'
		FROM {$this->quoteTableName($rc)} RC
		JOIN {$this->quoteTableName($kcu)} KCU1
		ON KCU1.CONSTRAINT_CATALOG = RC.CONSTRAINT_CATALOG
		   AND KCU1.CONSTRAINT_SCHEMA = RC.CONSTRAINT_SCHEMA
		   AND KCU1.CONSTRAINT_NAME = RC.CONSTRAINT_NAME
		JOIN {$this->quoteTableName($kcu)} KCU2
		ON KCU2.CONSTRAINT_CATALOG =
		RC.UNIQUE_CONSTRAINT_CATALOG
		   AND KCU2.CONSTRAINT_SCHEMA =
		RC.UNIQUE_CONSTRAINT_SCHEMA
		   AND KCU2.CONSTRAINT_NAME =
		RC.UNIQUE_CONSTRAINT_NAME
		   AND KCU2.ORDINAL_POSITION = KCU1.ORDINAL_POSITION
		WHERE KCU1.TABLE_NAME = :table
EOD;
		$command = $this->getDbConnection()->createCommand($sql);
		$command->bindValue(':table', $table->name);
		$fkeys=array();
		foreach($command->queryAll() as $info)
		{
			$fkeys[$info['FK_COLUMN_NAME']]=array($info['UQ_TABLE_NAME'],$info['UQ_COLUMN_NAME'],);

		}
		return $fkeys;
	}


	/**
	 * Collects the table column metadata.
	 * @param CMssqlTableSchema $table the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$where=array();
		$where[]="TABLE_NAME='".$table->name."'";
		if (isset($table->catalogName))
			$where[]="TABLE_CATALOG='".$table->catalogName."'";
		if (isset($table->schemaName))
			$where[]="TABLE_SCHEMA='".$table->schemaName."'";
		$sql="SELECT *, columnproperty(object_id(table_schema+'.'+table_name), column_name, 'IsIdentity') as IsIdentity ".
			 "FROM INFORMATION_SCHEMA.COLUMNS WHERE ".join(' AND ',$where);
		if (($columns=$this->getDbConnection()->createCommand($sql)->queryAll())===array())
			return false;

		foreach($columns as $column)
		{
			$c=$this->createColumn($column);
			if (is_array($table->primaryKey))
				$c->isPrimaryKey=in_array($c->name, $table->primaryKey);
			else
				$c->isPrimaryKey=strcasecmp($c->name,$table->primaryKey)===0;

			$c->isForeignKey=isset($table->foreignKeys[$c->name]);
			$table->columns[$c->name]=$c;
			if ($column['IsIdentity']==1 && $table->sequenceName===null)
				$table->sequenceName=$table->name;
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
		$c=new CMssqlColumnSchema;
		$c->name=$column['COLUMN_NAME'];
		$c->rawName=$this->quoteColumnName($c->name);
		$c->allowNull=$column['IS_NULLABLE']=='YES';
		if ($column['NUMERIC_PRECISION_RADIX']!==null)
		{
			// We have a numeric datatype
			$c->size=$c->precision=$column['NUMERIC_PRECISION']!==null?(int)$column['NUMERIC_PRECISION']:null;
			$c->scale=$column['NUMERIC_SCALE']!==null?(int)$column['NUMERIC_SCALE']:null;
		}
		elseif ($column['DATA_TYPE']=='image' || $column['DATA_TYPE']=='text')
			$c->size=$c->precision=null;
		else
			$c->size=$c->precision=($column['CHARACTER_MAXIMUM_LENGTH']!== null)?(int)$column['CHARACTER_MAXIMUM_LENGTH']:null;

		$c->init($column['DATA_TYPE'],$column['COLUMN_DEFAULT']);
		return $c;
	}

	/**
	 * Returns all table names in the database.
	 * @param string $schema the schema of the tables. Defaults to empty string, meaning the current or default schema.
	 * If not empty, the returned table names will be prefixed with the schema name.
	 * @return array all table names in the database.
	 * @since 1.0.4
	 */
	protected function findTableNames($schema='')
	{
		if($schema==='')
			$schema=self::DEFAULT_SCHEMA;
		$sql=<<<EOD
SELECT TABLE_NAME, TABLE_SCHEMA FROM [INFORMATION_SCHEMA].[TABLES]
WHERE TABLE_SCHEMA=:schema
EOD;
		$command=$this->getDbConnection()->createCommand($sql);
		$command->bindParam(":schema", $schema);
		$rows=$command->queryAll();
		$names=array();
		foreach ($rows as $row)
		{
			if ($schema == self::DEFAULT_SCHEMA)
				$names[]=$row['TABLE_NAME'];
			else
				$names[]=$schema.'.'.$row['TABLE_SCHEMA'].'.'.$row['TABLE_NAME'];
		}

		return $names;
	}

	/**
	 * Creates a command builder for the database.
	 * This method overrides parent implementation in order to create a MSSQL specific command builder
	 * @return CDbCommandBuilder command builder instance
	 */
	protected function createCommandBuilder()
	{
		return new CMssqlCommandBuilder($this);
	}
}
