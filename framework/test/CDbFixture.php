<?php

class CDbFixture extends CApplicationComponent
{
	public $basePath;
	public $connectionID='db';
	public $dbSchemaFile='schema.php';
	public $tableSchemaSuffix='.schema';

	private $_db;
	private $_rows;				// fixture name, row alias => row
	private $_records;			// fixture name, row alias => array(class name, primary key) or AR object

	public function init()
	{
		parent::init();
		if($this->basePath===null)
			$this->basePath=Yii::getPathOfAlias('application.tests.fixtures');
	}

	public function getRows($name)
	{
		if(isset($this->_rows[$name]))
			return $this->_rows[$name];
		else
			return false;
	}

	public function hasRecord($name,$alias)
	{
		return isset($this->_records[$name][$alias]);
	}

	public function getRecord($name,$alias)
	{
		if(isset($this->_records[$name][$alias]))
		{
			if($this->_records[$name][$alias] instanceof CActiveRecord)
				return $this->_records[$name][$alias];
			else
			{
				$config=$this->_records[$name][$alias];
				$modelClass=$config[0];
				unset($config[0]);
				return $this->_records[$name][$alias]=CActiveRecord::model($modelClass)->findByPk(count($config)>1 ? $config : $config[1]);
			}
		}
		else
			return false;
	}

	public function getDbConnection()
	{
		if($this->_db===null)
		{
			$this->_db=Yii::app()->getComponent($this->connectionID);
			if(!$this->_db instanceof CDbConnection)
				throw new CException(Yii::t('yii','CDbFixture.connectionID "{id}" is invalid. Please make sure it refers to the ID of a CDbConnection application component.',
					array('{id}'=>$id)));
		}
		return $this->_db;
	}

	public function prepare()
	{
		$fileName=$this->basePath.DIRECTORY_SEPARATOR.$this->dbSchemaFile.'.php';
		if(is_file($fileName))
			require($fileName);
	}

	public function load($fixtures)
	{
		$this->getDbConnection()->getSchema()->checkIntegrity(false);

		$this->_rows=array();
		$this->_records=array();
		foreach($fixtures as $varName=>$modelClass)
			$this->loadFixture($varName,$modelClass);

		$this->getDbConnection()->getSchema()->checkIntegrity(true);
	}

	public function unload($fixtures)
	{
		// unlock FK constraints

		foreach($fixtures as $varName=>$modelClass)
			$this->unloadFixture($varName,$modelClass);

		// reset sequence?
		// Lock FK constraints
	}

	protected function truncateTable($tableName)
	{
		$db=$this->getDbConnection();
		if(($table=$db->getSchema()->getTable($tableName))!==null)
		{
			$db->createCommand('DELETE FROM '.$table->rawName)->execute();
			$db->getSchema()->resetSequence($table,1);
		}
		else
			throw new Exception("Table '$tableName' does not exist.");
	}

	protected function resetTable($tableName)
	{
		$fileName=$this->basePath.DIRECTORY_SEPARATOR.$tableName.$this->tableSchemaSuffix.'.php';
		if(is_file($fileName))
			require($fileName);
		else
			$this->truncateTable($tableName);
	}

	protected function loadTable($tableName)
	{
		$fileName=$this->basePath.DIRECTORY_SEPARATOR.$tableName.'.php';
		if(!is_file($fileName))
			throw new Exception("Unable to find the fixture file '$fileName'.");
		return require($fileName);
	}

	protected function getTableName($modelClass)
	{
		if($modelClass[0]===':') // it is a table name, no model available
			return $tableName=substr($modelClass,1);
		else
		{
			$modelClass=Yii::import($modelClass,true);
			return CActiveRecord::model($modelClass)->tableName();
		}
	}

	public function loadFixture($varName,$modelClass)
	{
		$db=$this->getDbConnection();
		$tableName=$this->getTableName($modelClass);
		if($modelClass[0]!==':')
			$model=CActiveRecord::model($modelClass);

		if(($table=$db->getSchema()->getTable($tableName))===null)
			throw new Exception("Table '$tableName' does not exist.");

		$builder=$db->getSchema()->getCommandBuilder();

		$this->resetTable($tableName);

		foreach($this->loadTable($tableName) as $alias=>$row)
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

			if(is_integer($varName))
				continue;

			$this->_rows[$varName][$alias]=$row;

			if(is_string($alias) && isset($model))	// prepare for loading AR when needed
			{
				$this->_records[$varName][$alias][0]=$modelClass;
				if(is_array($table->primaryKey))
				{
					foreach($table->primaryKey as $key)
						$this->_records[$varName][$alias][$key]=$row[$key];
				}
				else
					$this->_records[$varName][$alias][]=$row[$table->primaryKey];
			}
		}
	}

	public function unloadFixture($varName,$modelClass)
	{
		$db=$this->getDbConnection();
		$tableName=$this->getTableName($modelClass);
	}
}