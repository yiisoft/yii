<?php
/**
 * This file contains the CDbFixtureManager class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CDbFixtureManager manages database fixtures during tests.
 *
 * A fixture represents a list of rows for a specific table. For a test method,
 * using a fixture means that at the begin of the method, the table has and only
 * has the rows that are given in the fixture. Therefore, the table's state is
 * predictable.
 *
 * A fixture is represented as a PHP script whose name (without suffix) is the
 * same as the table name (if schema name is needed, it should be prefixed to
 * the table name). The PHP script returns an array representing a list of table
 * rows. Each row is an associative array of column values indexed by column names.
 *
 * A fixture can be associated with an init script which sits under the same fixture
 * directory and is named as "TableName.init.php". The init script is used to
 * initialize the table before populating the fixture data into the table.
 * If the init script does not exist, the table will be emptied.
 *
 * Fixtures must be stored under the {@link basePath} directory. The directory
 * may contain a file named "init.php" which will be executed once at the beginning
 * of the test execution.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.test
 * @since 1.1
 */
class CDbFixtureManager extends CApplicationComponent
{
	/**
	 * The name of the initialization script for the whole test execution.
	 */
	const INIT_SCRIPT='init.php';
	/**
	 * The suffix for fixture initialization scripts.
	 */
	const INIT_SCRIPT_SUFFIX='.init.php';

	/**
	 * @var string the base path containing all fixtures. Defaults to null, meaning
	 * the path 'protected/tests/fixtures'.
	 */
	public $basePath;
	/**
	 * @var string the ID of the database connection. Defaults to 'db'.
	 * Note, data in this database may be deleted or modified during testing.
	 * Make sure you have a backup database.
	 */
	public $connectionID='db';
	/**
	 * @var array list of database schemas that the test tables may reside in. Defaults to
	 * array(''), meaning using the default schema (an empty string refers to the
	 * default schema). This property is mainly used when turning on and off integrity checks
	 * so that fixture data can be populated into the database without causing problem.
	 */
	public $schemas=array('');

	private $_db;
	private $_fixtures;
	private $_rows;				// fixture name, row alias => row
	private $_records;			// fixture name, row alias => record (or class name)


	/**
	 * Initializes this application component.
	 */
	public function init()
	{
		parent::init();
		if($this->basePath===null)
			$this->basePath=Yii::getPathOfAlias('application.tests.fixtures');
	}

	/**
	 * Returns the database connection used to load fixtures.
	 * @return CDbConnection the database connection
	 */
	public function getDbConnection()
	{
		if($this->_db===null)
		{
			$this->_db=Yii::app()->getComponent($this->connectionID);
			if(!$this->_db instanceof CDbConnection)
				throw new CException(Yii::t('yii','CDbTestFixture.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
					array('{id}'=>$this->connectionID)));
		}
		return $this->_db;
	}

	/**
	 * Prepares the fixtures for the whole test.
	 * This method should be called when tests start and should only be called once.
	 * The method will execute the database init script if it is available.
	 * It will then load every fixture found under {@link basePath}.
	 */
	public function prepare()
	{
		$initFile=$this->basePath . DIRECTORY_SEPARATOR . self::INIT_SCRIPT;

		$this->checkIntegrity(false);

		if(is_file($initFile))
			require($initFile);

		foreach($this->getFixtures() as $fixture)
			$this->loadFixture($fixture);

		$this->checkIntegrity(true);
	}

	/**
	 * Resets the table to the state that it contains no fixture data.
	 * If there is an init script named "tests/fixtures/TableName.init.php",
	 * the script will be executed.
	 * Otherwise, {@link truncateTable} will be invoked to delete all rows in the table
	 * and reset primary key sequence, if any.
	 * @param string the table name
	 */
	public function resetTable($tableName)
	{
		$initFile=$this->basePath . DIRECTORY_SEPARATOR . $tableName . self::INIT_SCRIPT_SUFFIX;
		if(is_file($initFile))
			require($initFile);
		else
			$this->truncateTable($tableName);
	}

	/**
	 * Loads the fixture for the specified table.
	 * This method will insert rows given in the fixture into the corresponding table.
	 * The loaded rows will be returned by this method.
	 * If the table has auto-incremental primary key, each row will contain updated primary key value.
	 * If the fixture does not exist, this method will return false.
	 * Note, you may want to call {@link resetTable} before calling this method
	 * so that the table is emptied first.
	 * @param string table name
	 * @return array the loaded fixture rows indexed by row aliases (if any).
	 * False is returned if the table does not have a fixture.
	 */
	public function loadFixture($tableName)
	{
		$fileName=$this->basePath.DIRECTORY_SEPARATOR.$tableName.'.php';
		if(!is_file($fileName))
			return false;

		$rows=array();
		$schema=$this->getDbConnection()->getSchema();
		$builder=$schema->getCommandBuilder();
		$table=$schema->getTable($tableName);

		foreach(require($fileName) as $alias=>$row)
		{
			$builder->createInsertCommand($table,$row)->execute();
			$primaryKey=$table->primaryKey;
			if($table->sequenceName!==null)
			{
				if(is_string($primaryKey) && !isset($row[$primaryKey]))
					$row[$primaryKey]=$builder->getLastInsertID($table);
				else if(is_array($primaryKey))
				{
					foreach($primaryKey as $pk)
					{
						if(!isset($row[$pk]))
						{
							$row[$pk]=$builder->getLastInsertID($table);
							break;
						}
					}
				}
			}
			$rows[$alias]=$row;
		}
		return $rows;
	}

	/**
	 * Returns the names of the tables that have fixture data.
	 * All fixtures are assumed to be located under {@link basePath}.
	 * @return array the names of the tables that have fixture data
	 */
	public function getFixtures()
	{
		if($this->_fixtures===null)
		{
			$this->_fixtures=array();
			$schema=$this->getDbConnection()->getSchema();
			$folder=opendir($this->basePath);
			$suffixLen=strlen(self::INIT_SCRIPT_SUFFIX);
			while($file=readdir($folder))
			{
				if($file==='.' || $file==='..' || $file===self::INIT_SCRIPT)
					continue;
				$path=$this->basePath.DIRECTORY_SEPARATOR.$file;
				if(substr($file,-4)==='.php' && is_file($path) && substr($file,-$suffixLen)!==self::INIT_SCRIPT_SUFFIX)
				{
					$tableName=substr($file,0,-4);
					if($schema->getTable($tableName)!==null)
						$this->_fixtures[$tableName]=$path;
				}
			}
			closedir($folder);
		}
		return $this->_fixtures;
	}

	/**
	 * Enables or disables database integrity check.
	 * This method may be used to temporarily turn off foreign constraints check.
	 * @param boolean whether to enable database integrity check
	 */
	public function checkIntegrity($check)
	{
		foreach($this->schemas as $schema)
			$this->getDbConnection()->getSchema()->checkIntegrity($check,$schema);
	}

	/**
	 * Removes all rows from the specified table and resets its primary key sequence, if any.
	 * You may need to call {@link checkIntegrity} to turn off integrity check temporarily
	 * before you call this method.
	 * @param string the table name
	 */
	public function truncateTable($tableName)
	{
		$db=$this->getDbConnection();
		$schema=$db->getSchema();
		if(($table=$schema->getTable($tableName))!==null)
		{
			$db->createCommand('DELETE FROM '.$table->rawName)->execute();
			$schema->resetSequence($table,1);
		}
		else
			throw new CException("Table '$tableName' does not exist.");
	}

	/**
	 * Truncates all tables in the specified schema.
	 * You may need to call {@link checkIntegrity} to turn off integrity check temporarily
	 * before you call this method.
	 * @param string the schema name. Defaults to empty string, meaning the default database schema.
	 * @see truncateTable
	 */
	public function truncateTables($schema='')
	{
		$tableNames=$this->getDbConnection()->getSchema()->getTableNames($schema);
		foreach($tableNames as $tableName)
			$this->truncateTable($tableName);
	}

	/**
	 * Loads the specified fixtures.
	 * For each fixture, the corresponding table will be reset first by calling
	 * {@link resetTable} and then be populated with the fixture data.
	 * The loaded fixture data may be later retrieved using {@link getRows}
	 * and {@link getRecord}.
	 * Note, if a table does not have fixture data, {@link resetTable} will still
	 * be called to reset the table.
	 * @param array fixtures to be loaded. The array keys are fixture names,
	 * and the array values are either AR class names or table names.
	 * If table names, they must begin with a colon character (e.g. 'Post'
	 * means an AR class, while ':Post' means a table name).
	 */
	public function load($fixtures)
	{
		$schema=$this->getDbConnection()->getSchema();
		$schema->checkIntegrity(false);

		$this->_rows=array();
		$this->_records=array();
		foreach($fixtures as $fixtureName=>$tableName)
		{
			if($tableName[0]===':')
			{
				$tableName=substr($tableName,1);
				unset($modelClass);
			}
			else
			{
				$modelClass=Yii::import($tableName,true);
				$tableName=CActiveRecord::model($modelClass)->tableName();
			}
			$this->resetTable($tableName);
			$rows=$this->loadFixture($tableName);
			if(is_array($rows) && is_string($fixtureName))
			{
				$this->_rows[$fixtureName]=$rows;
				if(isset($modelClass))
				{
					foreach(array_keys($rows) as $alias)
						$this->_records[$fixtureName][$alias]=$modelClass;
				}
			}
		}

		$schema->checkIntegrity(true);
	}

	/**
	 * Returns the fixture data rows.
	 * The rows will have updated primary key values if the primary key is auto-incremental.
	 * @param string the fixture name
	 * @return array the fixture data rows. False is returned if there is no such fixture data.
	 */
	public function getRows($name)
	{
		if(isset($this->_rows[$name]))
			return $this->_rows[$name];
		else
			return false;
	}

	/**
	 * Returns the specified ActiveRecord instance in the fixture data.
	 * @param string the fixture name
	 * @param string the alias for the fixture data row
	 * @return CActiveRecord the ActiveRecord instance. False is returned if there is no such fixture row.
	 */
	public function getRecord($name,$alias)
	{
		if(isset($this->_records[$name][$alias]))
		{
			if(is_string($this->_records[$name][$alias]))
			{
				$row=$this->_rows[$name][$alias];
				$model=CActiveRecord::model($this->_records[$name][$alias]);
				$key=$model->getTableSchema()->primaryKey;
				if(is_string($key))
					$pk=$row[$key];
				else
				{
					foreach($key as $k)
						$pk[$k]=$row[$k];
				}
				$this->_records[$name][$alias]=$model->findByPk($pk);
			}
			return $this->_records[$name][$alias];
		}
		else
			return false;
	}
}