<?php
/**
 * This file contains the CDbCommand class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbCommand represents an SQL statement to execute against a database.
 *
 * It is usually created by calling {@link CDbConnection::createCommand}.
 * The SQL statement to be executed may be set via {@link setText Text}.
 *
 * To execute a non-query SQL (such as insert, delete, update), call
 * {@link execute}. To execute an SQL statement that returns result data set
 * (such as SELECT), use {@link query} or its convenient versions {@link queryRow},
 * {@link queryColumn}, or {@link queryScalar}.
 *
 * If an SQL statement returns results (such as a SELECT SQL), the results
 * can be accessed via the returned {@link CDbDataReader}.
 *
 * CDbCommand supports SQL statment preparation and parameter binding.
 * Call {@link bindParam} to bind a PHP variable to a parameter in SQL.
 * Call {@link bindValue} to bind a value to an SQL parameter.
 * When binding a parameter, the SQL statement is automatically prepared.
 * You may also call {@link prepare} to explicitly prepare an SQL statement.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db
 * @since 1.0
 */
class CDbCommand extends CComponent
{
	private $_connection;
	private $_text='';
	private $_statement=null;
	private $_params;

	/**
	 * Constructor.
	 * @param CDbConnection the database connection
	 * @param string the SQL statement to be executed
	 */
	public function __construct(CDbConnection $connection,$text)
	{
		$this->_connection=$connection;
		$this->setText($text);
	}

	/**
	 * Set the statement to null when serializing.
	 */
	public function __sleep()
	{
		$this->_statement=null;
		return array_keys(get_object_vars($this));
	}

	/**
	 * @return string the SQL statement to be executed
	 */
	public function getText()
	{
		return $this->_text;
	}

	/**
	 * Specifies the SQL statement to be executed.
	 * Any previous execution will be terminated or cancel.
	 * @param string the SQL statement to be executed
	 */
	public function setText($value)
	{
		$this->_text=$value;
		$this->cancel();
	}

	/**
	 * @return CDbConnection the connection associated with this command
	 */
	public function getConnection()
	{
		return $this->_connection;
	}

	/**
	 * @return PDOStatement the underlying PDOStatement for this command
	 * It could be null if the statement is not prepared yet.
	 */
	public function getPdoStatement()
	{
		return $this->_statement;
	}

	/**
	 * Prepares the SQL statement to be executed.
	 * For complex SQL statement that is to be executed multiple times,
	 * this may improve performance.
	 * For SQL statement with binding parameters, this method is invoked
	 * automatically.
	 */
	public function prepare()
	{
		if($this->_statement==null)
		{
			try
			{
				$this->_statement=$this->getConnection()->getPdoInstance()->prepare($this->getText());
				$this->_params=array();
			}
			catch(Exception $e)
			{
				Yii::log('Error in preparing SQL: '.$this->getText(),CLogger::LEVEL_ERROR,'system.db.CDbCommand');
				throw new CDbException(Yii::t('yii','CDbCommand failed to prepare the SQL statement: {error}',
					array('{error}'=>$e->getMessage())));
			}
		}
	}

	/**
	 * Cancels the execution of the SQL statement.
	 */
	public function cancel()
	{
		$this->_statement=null;
	}

	/**
	 * Binds a parameter to the SQL statement to be executed.
	 * @param mixed Parameter identifier. For a prepared statement
	 * using named placeholders, this will be a parameter name of
	 * the form :name. For a prepared statement using question mark
	 * placeholders, this will be the 1-indexed position of the parameter.
	 * @param mixed Name of the PHP variable to bind to the SQL statement parameter
	 * @param int SQL data type of the parameter. If null, the type is determined by the PHP type of the value.
	 * @param int length of the data type
	 * @see http://www.php.net/manual/en/function.PDOStatement-bindParam.php
	 */
	public function bindParam($name, &$value, $dataType=null, $length=null)
	{
		$this->prepare();
		if($dataType===null)
			$this->_statement->bindParam($name,$value,$this->_connection->getPdoType(gettype($value)));
		else if($length===null)
			$this->_statement->bindParam($name,$value,$dataType);
		else
			$this->_statement->bindParam($name,$value,$dataType,$length);
		if($this->_connection->enableParamLogging)
			$this->_params[]=$name.'=['.gettype($value).']';
	}

	/**
	 * Binds a value to a parameter.
	 * @param mixed Parameter identifier. For a prepared statement
	 * using named placeholders, this will be a parameter name of
	 * the form :name. For a prepared statement using question mark
	 * placeholders, this will be the 1-indexed position of the parameter.
	 * @param mixed The value to bind to the parameter
	 * @param int SQL data type of the parameter. If null, the type is determined by the PHP type of the value.
	 * @see http://www.php.net/manual/en/function.PDOStatement-bindValue.php
	 */
	public function bindValue($name, $value, $dataType=null)
	{
		$this->prepare();
		if($dataType===null)
			$this->_statement->bindValue($name,$value,$this->_connection->getPdoType(gettype($value)));
		else
			$this->_statement->bindValue($name,$value,$dataType);
		if($this->_connection->enableParamLogging)
			$this->_params[]=$name.'='.var_export($value,true);
	}

	/**
	 * Executes the SQL statement.
	 * This method is meant only for executing non-query SQL statement.
	 * No result set will be returned.
	 * @return integer number of rows affected by the execution.
	 * @throws CException execution failed
	 */
	public function execute()
	{
		$params=$this->_connection->enableParamLogging && !empty($this->_params) ? '. Bind with parameter ' . implode(', ',$this->_params) : '';
		Yii::trace('Executing SQL: '.$this->getText().$params,'system.db.CDbCommand');
		try
		{
			if($this->_statement instanceof PDOStatement)
			{
				$this->_statement->execute();
				return $this->_statement->rowCount();
			}
			else
				return $this->getConnection()->getPdoInstance()->exec($this->getText());
		}
		catch(Exception $e)
		{
			Yii::log('Error in executing SQL: '.$this->getText().$params,CLogger::LEVEL_ERROR,'system.db.CDbCommand');
			throw new CDbException(Yii::t('yii','CDbCommand failed to execute the SQL statement: {error}',
				array('{error}'=>$e->getMessage())));
		}
	}

	/**
	 * Executes the SQL statement and returns query result.
	 * This method is for executing an SQL query that returns result set.
	 * @return CDbDataReader the reader object for fetching the query result
	 * @throws CException execution failed
	 */
	public function query()
	{
		return $this->queryInternal('',0);
	}

	/**
	 * Executes the SQL statement and returns all rows.
	 * @param boolean whether each row should be returned as an associated array with
	 * column names as the keys or the array keys are column indexes (0-based).
	 * @return array all rows of the query result. Each array element is an array representing a row.
	 * An empty array is returned if the query results in nothing.
	 * @throws CException execution failed
	 */
	public function queryAll($fetchAssociative=true)
	{
		return $this->queryInternal('fetchAll',$fetchAssociative ? PDO::FETCH_ASSOC : PDO::FETCH_NUM);
	}

	/**
	 * Executes the SQL statement and returns the first row of the result.
	 * This is a convenient method of {@link query} when only the first row of data is needed.
	 * @param boolean whether the row should be returned as an associated array with
	 * column names as the keys or the array keys are column indexes (0-based).
	 * @return array the first row of the query result, false if no result.
	 * @throws CException execution failed
	 */
	public function queryRow($fetchAssociative=true)
	{
		return $this->queryInternal('fetch',$fetchAssociative ? PDO::FETCH_ASSOC : PDO::FETCH_NUM);
	}

	/**
	 * Executes the SQL statement and returns the value of the first column in the first row of data.
	 * This is a convenient method of {@link query} when only a single scalar
	 * value is needed (e.g. obtaining the count of the records).
	 * @return mixed the value of the first column in the first row of the query result. False is returned if there is no value.
	 * @throws CException execution failed
	 */
	public function queryScalar()
	{
		$result=$this->queryInternal('fetchColumn',0);
		if(is_resource($result) && get_resource_type($result)==='stream')
			return stream_get_contents($result);
		else
			return $result;
	}

	/**
	 * Executes the SQL statement and returns the first column of the result.
	 * This is a convenient method of {@link query} when only the first column of data is needed.
	 * Note, the column returned will contain the first element in each row of result.
	 * @return array the first column of the query result. Empty array if no result.
	 * @throws CException execution failed
	 */
	public function queryColumn()
	{
		return $this->queryInternal('fetchAll',PDO::FETCH_COLUMN);
	}

	/**
	 * @param string method of PDOStatement to be called
	 * @param mixed the first parameter to be passed to the method
	 * @return mixed the method execution result
	 */
	private function queryInternal($method,$mode)
	{
		$params=$this->_connection->enableParamLogging && !empty($this->_params) ? '. Bind with parameter ' . implode(', ',$this->_params) : '';
		Yii::trace('Querying SQL: '.$this->getText().$params,'system.db.CDbCommand');
		try
		{
			if($this->_statement instanceof PDOStatement)
				$this->_statement->execute();
			else
				$this->_statement=$this->getConnection()->getPdoInstance()->query($this->getText());
			if($method==='')
				return new CDbDataReader($this);
			$result=$this->_statement->{$method}($mode);
			$this->_statement->closeCursor();
			return $result;
		}
		catch(Exception $e)
		{
			Yii::log('Error in querying SQL: '.$this->getText().$params,CLogger::LEVEL_ERROR,'system.db.CDbCommand');
			throw new CDbException(Yii::t('yii','CDbCommand failed to execute the SQL statement: {error}',
				array('{error}'=>$e->getMessage())));
		}
	}
}
