<?php
/**
 * CDbStatePersister class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @package system.base
 * @since 1.1.17
 */

/**
 * CDbStatePersister implements a database persistent data storage.
 *
 * It can be used to keep data available through multiple requests and sessions.
 *
 * By default, CDbStatePersister stores data in a table named 'state'.
 * You may change the location by setting the {@link stateTableName} property.
 *
 * To retrieve the data from CDbStatePersister, call {@link load()}. To save the data,
 * call {@link save()}.
 *
 * Comparison among state persister, session and cache is as follows:
 * <ul>
 * <li>session: data persisting within a single user session.</li>
 * <li>state persister: data persisting through all requests/sessions (e.g. hit counter).</li>
 * <li>cache: volatile and fast storage. It may be used as storage medium for session or state persister.</li>
 * </ul>
 *
 * @package system.base
 * @since 1.1.17
 */
class CDbStatePersister extends CApplicationComponent implements IStatePersister
{
	/**
	 * @var string the database table name storing the state data. Make sure the table
	 * exists or database user is granted to CREATE tables.
	 */
	public $stateTableName='state';
	/**
	 * @var string connection ID
	 */
	public $dbComponent='db';
	/**
	 * @var CDbConnection instance
	 */
	public $db;
	/**
	 * @var string Column name for value-field
	 */
	public $valueField='value';
	/**
	 * @var string Column name for key-field
	 */
	public $keyField='key';


	/**
	 * Initializes the component.
	 * This method overrides the parent implementation by making sure {@link stateFile}
	 * contains valid value.
	 */
	public function init()
	{
		parent::init();
		if($this->stateTableName===null)
			throw new CException(Yii::t('yii', 'stateTableName param cannot be null.'));
		$this->db=Yii::app()->getComponent($this->dbComponent);
		if($this->db===null)
			throw new CException(Yii::t('yii', '\'{db}\' component doesn\'t exist.',array(
				'{db}'=>$this->dbComponent
			)));
		if(!($this->db instanceof CDbConnection))
			throw new CException(Yii::t ('yii', '\'{db}\' component is not a valid CDbConnection instance.',array(
				'{db}'=>$this->dbComponent
			)));
		if($this->db->schema->getTable($this->stateTableName,true)===null)
			$this->createTable();
	}

	/**
	 * Loads state data from persistent storage.
	 * @return mixed state data. Null if no state data available.
	 */
	public function load()
	{
		$command=$this->db->createCommand();
		$command=$command->select($this->valueField)->from($this->stateTableName);
		$command=$command->where($this->db->quoteColumnName($this->keyField).'=:key',array(
			':key'=>Yii::app()->name
		));
		$state=$command->queryScalar();
		if(false!==$state)
			return unserialize($state);
		else
			return null;
	}

	/**
	 * Saves application state in persistent storage.
	 * @param mixed $state state data (must be serializable).
	 * @return int
	 */
	public function save($state)
	{
		$command=$this->db->createCommand();
		if(false===$this->exists())
			return $command->insert($this->stateTableName,array(
				$this->keyField=>Yii::app()->name,
				$this->valueField=>serialize($state)
			));
		else
			return $command->update($this->stateTableName,array($this->valueField=>serialize($state)),
				$this->db->quoteColumnName($this->keyField).'=:key',
				array(':key'=>Yii::app()->name)
		);
	}

	/**
	 * @return mixed
	 */
	public function exists()
	{
		$command=$this->db->createCommand();
		$command=$command->select($this->keyField)->from($this->stateTableName);
		$command=$command->where($this->db->quoteColumnName($this->keyField).'=:key',array(
			':key'=>Yii::app()->name
		));
		return $command->queryScalar();
	}

	/**
	 * Creates state persister table
	 * @throws CException
	 */
	protected function createTable()
	{
		try
		{
			$command=$this->db->createCommand();
			$command->createTable($this->stateTableName,array(
				$this->keyField=>'string NOT NULL',
				$this->valueField=>'text NOT NULL',
				'PRIMARY KEY ('.$this->db->quoteColumnName($this->keyField).')'
			));
		}
		catch (CDbException $e)
		{
			throw new CException(Yii::t('yii','Can\'t create state persister table. Check CREATE privilege for \'{db}\' connection user or create table manually with SQL: {sql}.',array('{db}'=>$this->dbComponent,'{sql}'=>$command->text ) ) );
		}
	}
}
