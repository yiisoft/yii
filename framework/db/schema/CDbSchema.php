<?php
/**
 * CDbSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbSchema is the base class for retrieving metadata information.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.schema
 * @since 1.0
 */
abstract class CDbSchema extends CComponent
{
	private $_tables=array();
	private $_connection;
	private $_builder;
	private $_cacheExclude=array();

	/**
	 * Creates a table instance representing the metadata for the named table.
	 * @return CDbTableSchema driver dependent table metadata, null if the table does not exist.
	 */
	abstract protected function createTable($name);

	/**
	 * Constructor.
	 * @param CDbConnection database connection.
	 */
	public function __construct($conn)
	{
		$conn->setActive(true);
		$this->_connection=$conn;
		foreach($conn->schemaCachingExclude as $name)
			$this->_cacheExclude[$name]=true;
	}

	/**
	 * @return CDbConnection database connection. The connection is active.
	 */
	public function getDbConnection()
	{
		return $this->_connection;
	}

	/**
	 * Obtains the metadata for the named table.
	 * @param string table name
	 * @return CDbTableSchema table metadata. Null if the named table does not exist.
	 */
	public function getTable($name)
	{
		if(isset($this->_tables[$name]))
			return $this->_tables[$name];
		else if(!isset($this->_cacheExclude[$name]) && ($duration=$this->_connection->schemaCachingDuration)>0 && ($cache=Yii::app()->getCache())!==null)
		{
			$key='yii:dbschema'.$this->_connection->connectionString.':'.$this->_connection->username.':'.$name;
			if(($table=$cache->get($key))===false)
			{
				$table=$this->createTable($name);
				$cache->set($key,$table,$duration);
			}
			return $this->_tables[$name]=$table;
		}
		else
			return $this->_tables[$name]=$this->createTable($name);
	}

	/**
	 * @return CDbCommandBuilder the SQL command builder for this connection.
	 */
	public function getCommandBuilder()
	{
		if($this->_builder!==null)
			return $this->_builder;
		else
			return $this->_builder=$this->createCommandBuilder();
	}

	/**
	 * Creates a command builder for the database.
	 * This method may be overridden by child classes to create a DBMS-specific command builder.
	 * @return CDbCommandBuilder command builder instance
	 */
	protected function createCommandBuilder()
	{
		return new CDbCommandBuilder($this);
	}

	/**
	 * Refreshes the schema.
	 * This method resets the loaded table metadata and command builder
	 * so that they can be recreated to reflect the change of schema.
	 */
	public function refresh()
	{
		$this->_tables=array();
		$this->_builder=null;
	}

	/**
	 * Quotes a table name for use in a query.
	 * @param string table name
	 * @return string the properly quoted table name
	 */
	public function quoteTableName($name)
	{
		return "'".$name."'";
	}

	/**
	 * Quotes a column name for use in a query.
	 * @param string column name
	 * @return string the properly quoted column name
	 */
	public function quoteColumnName($name)
	{
		return '"'.$name.'"';
	}

	/**
	 * Compares two table names.
	 * The table names can be either quoted or unquoted. This method
	 * will consider both cases.
	 * @param string table name 1
	 * @param string table name 2
	 * @return boolean whether the two table names refer to the same table.
	 */
	public function compareTableNames($name1,$name2)
	{
		$name1=str_replace(array('"','`',"'"),'',$name1);
		$name2=str_replace(array('"','`',"'"),'',$name2);
		if(($pos=strrpos($name1,'.'))!==false)
			$name1=substr($name1,$pos+1);
		if(($pos=strrpos($name2,'.'))!==false)
			$name2=substr($name2,$pos+1);
		return $name1===$name2;
	}
}
