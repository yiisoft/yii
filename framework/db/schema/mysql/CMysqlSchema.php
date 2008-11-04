<?php
/**
 * CMysqlSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
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
	private $_tableNames;
	private $_schemaNames;

	/**
	 * Quotes a table name for use in a query.
	 * @param string table name
	 * @return string the properly quoted table name
	 */
	public function quoteTableName($name)
	{
		return '`'.$name.'`';
	}

	/**
	 * Quotes a column name for use in a query.
	 * @param string column name
	 * @return string the properly quoted column name
	 */
	public function quoteColumnName($name)
	{
		return '`'.$name.'`';
	}

	/**
	 * Creates a table instance representing the metadata for the named table.
	 * @return CMysqlTableSchema driver dependent table metadata. Null if the table does not exist.
	 */
	protected function createTable($name)
	{
		$table=new CMysqlTableSchema;
		$this->setTableNames($table,$name);

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
	 * @param CMysqlTableSchema the table instance
	 * @param string the unquoted table name
	 */
	protected function setTableNames($table,$name)
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
	 * @param CMysqlTableSchema the table metadata
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
	 * @param array column metadata
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
	 * @param CMysqlTableSchema the table metadata
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
		$foreign = array();
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
}
