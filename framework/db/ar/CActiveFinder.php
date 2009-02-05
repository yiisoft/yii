<?php
/**
 * CActiveRecord class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CActiveFinder implements eager loading and lazy loading of related active records.
 *
 * When used in eager loading, this class provides the same set of find methods as
 * {@link CActiveRecord}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.ar
 * @since 1.0
 */
class CActiveFinder extends CComponent
{
	/**
	 * @var boolean join all tables all at once. Defaults to false.
	 * This property is internally used.
	 * @since 1.0.2
	 */
	public $joinAll=false;
	/**
	 * @var boolean whether the base model has limit or offset.
	 * This property is internally used.
	 * @since 1.0.2
	 */
	public $baseLimited;

	private $_joinCount=0;
	private $_joinTree;
	private $_builder;

	/**
	 * Constructor.
	 * A join tree is built up based on the declared relationships between active record classes.
	 * @param CActiveRecord the model that initiates the active finding process
	 * @param mixed the relation names to be actively looked for
	 */
	public function __construct($model,$with)
	{
		$this->_builder=$model->getCommandBuilder();
		$this->_joinTree=new CJoinElement($this,$model);
		$this->buildJoinTree($this->_joinTree,$with);
	}

	/**
	 * Uses the most aggressive join approach.
	 * By default, several join statements may be generated in order to avoid
	 * fetching duplicated data. By calling this method, all tables will be joined
	 * together all at once.
	 * @return CActiveFinder the finder object
	 * @since 1.0.2
	 */
	public function together()
	{
		$this->joinAll=true;
		return $this;
	}

	/**
	 * This is relational version of {@link CActiveRecord::find()}.
	 */
	public function find($condition='',$params=array())
	{
		$criteria=$this->_builder->createCriteria($condition,$params);
		$this->_joinTree->find($criteria);
		if(count($this->_joinTree->records))
			return reset($this->_joinTree->records);
		else
			return null;
	}

	/**
	 * This is relational version of {@link CActiveRecord::findAll()}.
	 */
	public function findAll($condition='',$params=array())
	{
		$criteria=$this->_builder->createCriteria($condition,$params);
		$this->_joinTree->find($criteria);
		return array_values($this->_joinTree->records);
	}

	/**
	 * This is relational version of {@link CActiveRecord::findByPk()}.
	 */
	public function findByPk($pk,$condition='',$params=array())
	{
		$criteria=$this->_builder->createPkCriteria($this->_joinTree->model->getTableSchema(),$pk,$condition,$params);
		$this->_joinTree->find($criteria);
		if(count($this->_joinTree->records))
			return reset($this->_joinTree->records);
		else
			return null;
	}

	/**
	 * This is relational version of {@link CActiveRecord::findAllByPk()}.
	 */
	public function findAllByPk($pk,$condition='',$params=array())
	{
		$criteria=$this->_builder->createPkCriteria($this->_joinTree->model->getTableSchema(),$pk,$condition,$params);
		$this->_joinTree->find($criteria);
		return array_values($this->_joinTree->records);
	}

	/**
	 * This is relational version of {@link CActiveRecord::findByAttributes()}.
	 */
	public function findByAttributes($attributes,$condition='',$params=array())
	{
		$criteria=$this->_builder->createColumnCriteria($this->_joinTree->model->getTableSchema(),$attributes,$condition,$params);
		$this->_joinTree->find($criteria);
		if(count($this->_joinTree->records))
			return reset($this->_joinTree->records);
		else
			return null;
	}

	/**
	 * This is relational version of {@link CActiveRecord::findAllByAttributes()}.
	 */
	public function findAllByAttributes($attributes,$condition='',$params=array())
	{
		$criteria=$this->_builder->createColumnCriteria($this->_joinTree->model->getTableSchema(),$attributes,$condition,$params);
		$this->_joinTree->find($criteria);
		return array_values($this->_joinTree->records);
	}

	/**
	 * This is relational version of {@link CActiveRecord::findBySql()}.
	 */
	public function findBySql($sql,$params=array())
	{
		$command=$this->_builder->createSqlCommand($sql,$params);
		$baseRecord=$this->_joinTree->model->populateRecord($command->queryRow());
		$this->_joinTree->findWithBase($baseRecord);
		return $baseRecord;
	}

	/**
	 * This is relational version of {@link CActiveRecord::findAllBySql()}.
	 */
	public function findAllBySql($sql,$params=array())
	{
		$command=$this->_builder->createSqlCommand($sql,$params);
		$baseRecords=$this->_joinTree->model->populateRecords($command->queryAll());
		$this->_joinTree->findWithBase($baseRecords);
		return $baseRecords;
	}

	/**
	 * Finds the related objects for the specified active record.
	 * This method is internally invoked by {@link CActiveRecord} to support lazy loading.
	 * @param CActiveRecord the base record whose related objects are to be loaded
	 */
	public function lazyFind($baseRecord)
	{
		$this->_joinTree->lazyFind($baseRecord);
	}

	/**
	 * Builds up the join tree representing the relationships involved in this query.
	 * @param CJoinElement the parent tree node
	 * @param mixed the names of the related objects relative to the parent tree node
	 */
	private function buildJoinTree($parent,$with)
	{
		if(is_string($with))
		{
			if(($pos=strrpos($with,'.'))!==false)
			{
				$parent=$this->buildJoinTree($parent,substr($with,0,$pos));
				$with=substr($with,$pos+1);
			}
			if(isset($parent->children[$with]))
				return $parent->children[$with];
			else if(($relation=$parent->model->getActiveRelation($with))!==null)
				return new CJoinElement($this,$relation,$parent,++$this->_joinCount);
			else
				throw new CDbException(Yii::t('yii','Relation "{name}" is not defined in active record class "{class}".',
					array('{class}'=>get_class($parent->model), '{name}'=>$with)));
		}

		// $with is an array, keys are relation name, values are relation spec
		foreach($with as $key=>$value)
		{
			if(is_string($key) && is_array($value))
			{
				$element=$this->buildJoinTree($parent,$key);
				$relation=clone $element->relation;
				foreach($value as $name=>$option)
					$relation->$name=$option;
				if($relation->alias!==null)
					$element->tableAlias=$relation->alias;
				$element->relation=$relation;
			}
			else if(is_string($value))  // the key is integer, so value is the relation name
				$this->buildJoinTree($parent,$value);
		}
	}
}


/**
 * CJoinElement represents a tree node in the join tree created by {@link CActiveFinder}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.ar
 * @since 1.0
 */
class CJoinElement
{
	/**
	 * @var integer the unique ID of this tree node
	 */
	public $id;
	/**
	 * @var CActiveRelation the relation represented by this tree node
	 */
	public $relation;
	/**
	 * @var CActiveRecord the model associated with this tree node
	 */
	public $model;
	/**
	 * @var array list of active records found by the queries. They are indexed by primary key values.
	 */
	public $records=array();
	/**
	 * @var array list of child join elements
	 */
	public $children=array();
	/**
	 * @var string table alias for this join element
	 */
	public $tableAlias;

	private $_finder;
	private $_builder;
	private $_parent;
	private $_pkAlias;  				// string or name=>alias
	private $_columnAliases=array();	// name=>alias
	private $_joined=false;
	private $_table;
	private $_related=array();			// PK, relation name, related PK => true

	/**
	 * Constructor.
	 * @param mixed the relation (if the second parameter is not null)
	 * or the model (if the second parameter is null) associated with this tree node.
	 * @param CJoinElement the parent tree node
	 * @param integer the ID of this tree node that is unique among all the tree nodes
	 */
	public function __construct($finder,$relation,$parent=null,$id=0)
	{
		$this->_finder=$finder;
		$this->id=$id;
		if($parent!==null)
		{
			$this->relation=$relation;
			$this->_parent=$parent;
			$parent->children[]=$this;
			$this->_builder=$parent->_builder;
			$this->tableAlias=$relation->alias===null?'t'.$id:$relation->alias;
			$this->model=CActiveRecord::model($relation->className);
			$this->_table=$this->model->getTableSchema();
		}
		else  // root element, the first parameter is the model.
		{
			$this->model=$relation;
			$this->_builder=$relation->getCommandBuilder();
			$this->_table=$relation->getTableSchema();
		}

		// set up column aliases, such as t1_c2
		$table=$this->_table;
		$prefix='t'.$id.'_c';
		foreach($table->getColumnNames() as $key=>$name)
		{
			$this->_columnAliases[$name]=$prefix.$key;
			if($table->primaryKey===$name)
				$this->_pkAlias=$prefix.$key;
			else if(is_array($table->primaryKey) && in_array($name,$table->primaryKey))
				$this->_pkAlias[$name]=$prefix.$key;
		}
	}

	/**
	 * Performs the recursive finding with the criteria.
	 * @param CDbCriteria the query criteria
	 */
	public function find($criteria=null)
	{
		if($this->_parent===null) // root element
		{
			$query=new CJoinQuery($this,$criteria);
			$this->_finder->baseLimited=($criteria->offset>=0 || $criteria->limit>=0);
			$this->buildQuery($query);
			$this->runQuery($query);
		}
		else if(!$this->_joined && !empty($this->_parent->records)) // not joined before
		{
			$query=new CJoinQuery($this->_parent);
			$this->_joined=true;
			$query->join($this);
			$this->buildQuery($query);
			$this->_parent->runQuery($query);
		}

		foreach($this->children as $child) // find recursively
			$child->find();
	}

	/**
	 * Performs lazy find with the specified base record.
	 * @param CActiveRecord the active record whose related object is to be fetched.
	 */
	public function lazyFind($baseRecord)
	{
		if(is_string($this->_table->primaryKey))
			$this->records[$baseRecord->{$this->_table->primaryKey}]=$baseRecord;
		else
		{
			$pk=array();
			foreach($this->_table->primaryKey as $name)
				$pk[$name]=$baseRecord->$name;
			$this->records[serialize($pk)]=$baseRecord;
		}

		$child=reset($this->children);
		$query=new CJoinQuery($this);
		$this->_joined=true;
		$child->_joined=true;
		$query->join($child);
		if($child->relation instanceof CHasManyRelation)
		{
			$query->limit=$child->relation->limit;
			$query->offset=$child->relation->offset;
			$this->_finder->baseLimited=($query->offset>=0 || $query->limit>=0);
			$query->groups[]=str_replace($child->relation->aliasToken.'.',$child->tableAlias.'.',$child->relation->group);
			$query->havings[]=str_replace($child->relation->aliasToken.'.',$child->tableAlias.'.',$child->relation->having);
		}
		$child->buildQuery($query);
		$this->runQuery($query);
		foreach($child->children as $c)
			$c->find();
	}

	/**
	 * Performs the eager loading with the base records ready.
	 * @param mixed the available base record(s).
	 */
	public function findWithBase($baseRecords)
	{
		if(!is_array($baseRecords))
			$baseRecords=array($baseRecords);
		if(is_string($this->_table->primaryKey))
		{
			foreach($baseRecords as $baseRecord)
				$this->records[$baseRecord->{$this->_table->primaryKey}]=$baseRecord;
		}
		else
		{
			foreach($baseRecords as $baseRecord)
			{
				$pk=array();
				foreach($this->_table->primaryKey as $name)
					$pk[$name]=$baseRecord->$name;
				$this->records[serialize($pk)]=$baseRecord;
			}
		}

		$query=new CJoinQuery($this);
		$this->buildQuery($query);
		if(count($query->joins)>1)
			$this->runQuery($query);
		foreach($this->children as $child)
			$child->find();
	}

	/**
	 * Builds the join query with all descendant HAS_ONE and BELONGS_TO nodes.
	 * @param CJoinQuery the query being built up
	 */
	public function buildQuery($query)
	{
		foreach($this->children as $child)
		{
			if($child->relation instanceof CHasOneRelation || $child->relation instanceof CBelongsToRelation
				|| ($this->_finder->joinAll && !$this->_finder->baseLimited))
			{
				$child->_joined=true;
				$query->join($child);
				$child->buildQuery($query);
			}
		}
	}

	/**
	 * Executes the join query and populates the query results.
	 * @param CJoinQuery the query to be executed.
	 */
	public function runQuery($query)
	{
		$command=$query->createCommand($this->_builder);
		foreach($command->queryAll() as $row)
			$this->populateRecord($query,$row);
	}

	/**
	 * Populates the active records with the query data.
	 * @param CJoinQuery the query executed
	 * @param array a row of data
	 * @return CActiveRecord the populated record
	 */
	private function populateRecord($query,$row)
	{
		// determine the primary key value
		if(is_string($this->_pkAlias))  // single key
		{
			if(!isset($row[$this->_pkAlias]))	// no matching related objects
				return null;
			else
				$pk=$row[$this->_pkAlias];
		}
		else // is_array, composite key
		{
			$pk=array();
			foreach($this->_pkAlias as $name=>$alias)
			{
				if(!isset($row[$alias]))	// no matching related objects
					return null;
				else
					$pk[$name]=$row[$alias];
			}
			$pk=serialize($pk);
		}

		// retrieve or populate the record according to the primary key value
		if(isset($this->records[$pk]))
			$record=$this->records[$pk];
		else
		{
			$attributes=array();
			$aliases=array_flip($this->_columnAliases);
			foreach($row as $alias=>$value)
			{
				if(isset($aliases[$alias]))
					$attributes[$aliases[$alias]]=$value;
			}
			$record=$this->model->populateRecord($attributes);
			$this->records[$pk]=$record;
		}

		// populate child records recursively
		foreach($this->children as $child)
		{
			if(!isset($query->elements[$child->id]))
				continue;
			$childRecord=$child->populateRecord($query,$row);
			if($child->relation instanceof CHasOneRelation || $child->relation instanceof CBelongsToRelation)
				$record->addRelatedRecord($child->relation->name,$childRecord,false);
			else // has_many and many_many
			{
				// need to double check to avoid adding duplicated related objects
				if($childRecord instanceof CActiveRecord)
					$fpk=serialize($childRecord->getPrimaryKey());
				else
					$fpk=0;
				if(!isset($this->_related[$pk][$child->relation->name][$fpk]))
				{
					$record->addRelatedRecord($child->relation->name,$childRecord,true);
					$this->_related[$pk][$child->relation->name][$fpk]=true;
				}
			}
		}

		return $record;
	}

	/**
	 * @return string the table name and the table alias (if any). This can be used directly in SQL query without escaping.
	 */
	public function getTableNameWithAlias()
	{
		if($this->tableAlias!==null)
			return $this->_table->rawName . ' ' . $this->tableAlias;
		else
			return $this->_table->rawName;
	}

	/**
	 * Generates the list of columns to be selected.
	 * Columns will be properly aliased and primary keys will be added to selection if they are not specified.
	 * @param mixed columns to be selected. Defaults to '*', indicating all columns.
	 * @return string the column selection
	 */
	public function getColumnSelect($select='*')
	{
		$schema=$this->_builder->getSchema();
		$prefix=$this->getColumnPrefix();
		$columns=array();
		if($select==='*')
		{
			foreach($this->_table->getColumnNames() as $name)
				$columns[]=$prefix.$schema->quoteColumnName($name).' AS '.$this->_columnAliases[$name];
		}
		else
		{
			if(is_string($select))
				$select=explode(',',$select);
			$selected=array();
			foreach($select as $name)
			{
				$name=trim($name);
				$matches=array();
				if(($pos=strrpos($name,'.'))!==false)
					$key=substr($name,$pos+1);
				else
					$key=$name;
				if(isset($this->_columnAliases[$key]))  // simple column names
				{
					$columns[]=$prefix.$schema->quoteColumnName($key).' AS '.$this->_columnAliases[$key];
					$selected[$key]=1;
				}
				else if(preg_match('/^(.*?)\s+AS\s+(\w+)$/i',$name,$matches)) // if the column is already aliased
				{
					$alias=$matches[2];
					if(!isset($this->_columnAliases[$alias]))
					{
						$this->_columnAliases[$alias]=$alias;
						$columns[]=$name;
						$selected[$alias]=1;
					}
				}
				else
					throw new CDbException(Yii::t('yii','Active record "{class}" is trying to select an invalid column "{column}". Note, the column must exist in the table or be an expression with alias.',
						array('{class}'=>get_class($this->model), '{column}'=>$name)));
			}
			// add primary key selection if they are not selected
			if(is_string($this->_pkAlias) && !isset($selected[$this->_pkAlias]))
				$columns[]=$prefix.$schema->quoteColumnName($this->_table->primaryKey).' AS '.$this->_pkAlias;
			else if(is_array($this->_pkAlias))
			{
				foreach($this->_primaryKey as $name)
					if(!isset($selected[$name]))
						$columns[]=$prefix.$schema->quoteColumnName($name).' AS '.$this->_pkAlias[$name];
			}
		}

		$select=implode(', ',$columns);
		if($this->relation!==null)
			return str_replace($this->relation->aliasToken.'.', $prefix, $select);
		else
			return $select;
	}

	/**
	 * @return string the primary key selection
	 */
	public function getPrimaryKeySelect()
	{
		$schema=$this->_builder->getSchema();
		$prefix=$this->getColumnPrefix();
		$columns=array();
		if(is_string($this->_pkAlias))
			$columns[]=$prefix.$schema->quoteColumnName($this->_table->primaryKey).' AS '.$this->_pkAlias;
		else if(is_array($this->_pkAlias))
		{
			foreach($this->_pkAlias as $name=>$alias)
				$columns[]=$prefix.$schema->quoteColumnName($name).' AS '.$alias;
		}
		return implode(', ',$columns);
	}

	/**
	 * @return string the condition that specifies only the rows with the selected primary key values.
	 */
	public function getPrimaryKeyRange()
	{
		if(empty($this->records))
			return '';
		$values=array_keys($this->records);
		if(is_array($this->_table->primaryKey))
		{
			foreach($values as &$value)
				$value=unserialize($value);
		}
		return $this->_builder->createPkCondition($this->_table,$values,$this->getColumnPrefix());
	}

	/**
	 * @return string the WHERE clause. Column references are properly disambiguated.
	 */
	public function getCondition()
	{
		if($this->relation->condition!=='' && $this->tableAlias!==null)
			return str_replace($this->relation->aliasToken.'.', $this->tableAlias.'.', $this->relation->condition);
		else
			return $this->relation->condition;
	}

	/**
	 * @return string the ORDER BY clause. Column references are properly disambiguated.
	 */
	public function getOrder()
	{
		if($this->relation->order!=='' && $this->tableAlias!==null)
			return str_replace($this->relation->aliasToken.'.',$this->tableAlias.'.',$this->relation->order);
		else
			return $this->relation->order;
	}

	/**
	 * @return string the column prefix for column reference disambiguation
	 */
	public function getColumnPrefix()
	{
		if($this->tableAlias!==null)
			return $this->tableAlias.'.';
		else
			return $this->_table->rawName.'.';
	}

	/**
	 * @return string the join statement (this node joins with its parent)
	 */
	public function getJoinCondition()
	{
		$parent=$this->_parent;
		$relation=$this->relation;
		if($this->relation instanceof CManyManyRelation)
		{
			if(!preg_match('/^\s*(.*?)\((.*)\)\s*$/',$this->relation->foreignKey,$matches))
				throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key. The format of the foreign key must be "joinTable(fk1,fk2,...)".',
					array('{class}'=>get_class($parent->model),'{relation}'=>$this->relation->name)));

			$schema=$this->_builder->getSchema();
			if(($joinTable=$schema->getTable($matches[1]))===null)
				throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is not specified correctly: the join table "{joinTable}" given in the foreign key cannot be found in the database.',
					array('{class}'=>get_class($parent->model), '{relation}'=>$this->relation->name, '{joinTable}'=>$matches[1])));
			$fks=preg_split('/[\s,]+/',$matches[2],-1,PREG_SPLIT_NO_EMPTY);

			return $this->joinManyMany($joinTable,$fks,$parent);
		}
		else
		{
			$fks=preg_split('/[\s,]+/',$relation->foreignKey,-1,PREG_SPLIT_NO_EMPTY);
			if($this->relation instanceof CBelongsToRelation)
			{
				$pke=$this;
				$fke=$parent;
			}
			else
			{
				$pke=$parent;
				$fke=$this;
			}
			return $this->joinOneMany($fke,$fks,$pke,$parent);
		}
	}

	/**
	 * Generates the join statement for one-many relationship.
	 * This works for HAS_ONE, HAS_MANY and BELONGS_TO.
	 * @param CJoinElement the join element containing foreign keys
	 * @param array the foreign keys
	 * @param CJoinElement the join element containg primary keys
	 * @param CJoinElement the parent join element
	 * @return string the join statement
	 * @throws CDbException if a foreign key is invalid
	 */
	private function joinOneMany($fke,$fks,$pke,$parent)
	{
		$schema=$this->_builder->getSchema();
		$joins=array();
		foreach($fks as $i=>$fk)
		{
			if(isset($fke->_table->foreignKeys[$fk]))
				$pk=$fke->_table->foreignKeys[$fk][1];
			else if(empty($fke->_table->foreignKeys))  // FK constraints undefined
			{
				if(is_array($pke->_table->primaryKey)) // composite PK
					$pk=$pke->_table->primaryKey[$i];
				else
					$pk=$pke->_table->primaryKey;
			}
			else
				throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key "{key}". The foreign key does not point to either joining table.',
					array('{class}'=>get_class($parent->model), '{relation}'=>$this->relation->name, '{key}'=>$fk)));
			$joins[]=$fke->getColumnPrefix().$schema->quoteColumnName($fk) . '=' . $pke->getColumnPrefix().$schema->quoteColumnName($pk);
		}
		if(!empty($this->relation->on))
			$joins[]=$this->relation->on;
		return $this->relation->joinType . ' ' . $this->getTableNameWithAlias() . ' ON ' . implode(' AND ',$joins);
	}

	/**
	 * Generates the join statement for many-many relationship.
	 * @param CDbTableSchema the join table
	 * @param array the foreign keys
	 * @param CJoinElement the parent join element
	 * @return string the join statement
	 * @throws CDbException if a foreign key is invalid
	 */
	private function joinManyMany($joinTable,$fks,$parent)
	{
		$schema=$this->_builder->getSchema();
		$joinAlias=$this->relation->name.'_'.$this->tableAlias;
		$parentCondition=array();
		$childCondition=array();
		foreach($fks as $i=>$fk)
		{
			if(isset($joinTable->foreignKeys[$fk]))
			{
				list($tableName,$pk)=$joinTable->foreignKeys[$fk];
				if(!isset($parentCondition[$pk]) && $schema->compareTableNames($parent->_table->rawName,$tableName))
					$parentCondition[$pk]=$parent->getColumnPrefix().$schema->quoteColumnName($pk).'='.$joinAlias.'.'.$schema->quoteColumnName($fk);
				else if(!isset($childCondition[$pk]) && $schema->compareTableNames($this->_table->rawName,$tableName))
					$childCondition[$pk]=$this->getColumnPrefix().$schema->quoteColumnName($pk).'='.$joinAlias.'.'.$schema->quoteColumnName($fk);
				else
					throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an invalid foreign key "{key}". The foreign key does not point to either joining table.',
						array('{class}'=>get_class($parent->model), '{relation}'=>$this->relation->name, '{key}'=>$fk)));
			}
			else if(empty($joinTable->foreignKeys)) // FK constraints not defined
			{
				if($i<count($parent->_table->primaryKey))
				{
					$pk=is_array($parent->_table->primaryKey) ? $parent->_table->primaryKey[$i] : $parent->_table->primaryKey;
					$parentCondition[$pk]=$parent->getColumnPrefix().$schema->quoteColumnName($pk).'='.$joinAlias.'.'.$schema->quoteColumnName($fk);
				}
				else
				{
					$j=$i-count($parent->_table->primaryKey);
					$pk=is_array($this->_table->primaryKey) ? $this->_table->primaryKey[$j] : $this->_table->primaryKey;
					$childCondition[$pk]=$this->getColumnPrefix().$schema->quoteColumnName($pk).'='.$joinAlias.'.'.$schema->quoteColumnName($fk);
				}
			}
			else
				throw new CDbException(Yii::t('yii','The column "{column}" is not a foreign key in table "{table}".',
					array('{column}'=>$fk, '{table}'=>$joinTable->name)));
		}
		if($parentCondition!==array() && $childCondition!==array())
		{
			$join=$this->relation->joinType.' '.$joinTable->rawName.' '.$joinAlias;
			$join.=' ON '.implode(' AND ',$parentCondition);
			$join.=' '.$this->relation->joinType.' '.$this->getTableNameWithAlias();
			$join.=' ON '.implode(' AND ',$childCondition);
			return $join;
		}
		else
			throw new CDbException(Yii::t('yii','The relation "{relation}" in active record class "{class}" is specified with an incomplete foreign key. The foreign key must consist of columns referencing both joining tables.',
				array('{class}'=>get_class($parent->model), '{relation}'=>$this->relation->name)));
	}
}


/**
 * CJoinQuery represents a JOIN SQL statement.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.ar
 * @since 1.0
 */
class CJoinQuery
{
	/**
	 * @var array list of column selections
	 */
	public $selects=array();
	/**
	 * @var array list of join statement
	 */
	public $joins=array();
	/**
	 * @var array list of WHERE clauses
	 */
	public $conditions=array();
	/**
	 * @var array list of ORDER BY clauses
	 */
	public $orders=array();
	/**
	 * @var array list of GROUP BY clauses
	 */
	public $groups=array();
	/**
	 * @var array list of HAVING clauses
	 */
	public $havings=array();
	/**
	 * @var integer row limit
	 */
	public $limit=-1;
	/**
	 * @var integer row offset
	 */
	public $offset=-1;
	/**
	 * @var array list of query parameters
	 */
	public $params=array();
	/**
	 * @var array list of join element IDs (id=>true)
	 */
	public $elements=array();

	/**
	 * Constructor.
	 * @param CJoinElement The root join tree.
	 * @param CDbCriteria the query criteria
	 */
	public function __construct($joinElement,$criteria=null)
	{
		if($criteria!==null)
		{
			$this->selects[]=$joinElement->getColumnSelect($criteria->select);
			$this->joins[]=$joinElement->getTableNameWithAlias();
			$this->joins[]=$criteria->join;
			$this->conditions[]=$criteria->condition;
			$this->orders[]=$criteria->order;
			$this->groups[]=$criteria->group;
			$this->havings[]=$criteria->having;
			$this->limit=$criteria->limit;
			$this->offset=$criteria->offset;
			$this->params=$criteria->params;
		}
		else
		{
			$this->selects[]=$joinElement->getPrimaryKeySelect();
			$this->joins[]=$joinElement->getTableNameWithAlias();
			$this->conditions[]=$joinElement->getPrimaryKeyRange();
		}
		$this->elements[$joinElement->id]=true;
	}

	/**
	 * Joins with another join element
	 * @param CJoinElement the element to be joined
	 */
	public function join($element)
	{
		$this->selects[]=$element->getColumnSelect($element->relation->select);
		$this->conditions[]=$element->getCondition();
		$this->orders[]=$element->getOrder();
		$this->joins[]=$element->getJoinCondition();
		$this->elements[$element->id]=true;
	}

	/**
	 * Creates the SQL statement.
	 * @param CDbCommandBuilder the command builder
	 * @return string the SQL statement
	 */
	public function createCommand($builder)
	{
		$sql='SELECT ' . implode(', ',$this->selects);
		$sql.=' FROM ' . implode(' ',$this->joins);

		$conditions=array();
		foreach($this->conditions as $condition)
			if($condition!=='')
				$conditions[]=$condition;
		if($conditions!==array())
			$sql.=' WHERE ' . implode(' AND ',$conditions);

		$groups=array();
		foreach($this->groups as $group)
			if($group!=='')
				$groups[]=$group;
		if($groups!==array())
			$sql.=' GROUP BY ' . implode(', ',$groups);

		$havings=array();
		foreach($this->havings as $having)
			if($having!=='')
				$havings[]=$having;
		if($havings!==array())
			$sql.=' HAVING ' . implode(' AND ',$havings);

		$orders=array();
		foreach($this->orders as $order)
			if($order!=='')
				$orders[]=$order;
		if($orders!==array())
			$sql.=' ORDER BY ' . implode(', ',$orders);

		$sql=$builder->applyLimit($sql,$this->limit,$this->offset);
		$command=$builder->getDbConnection()->createCommand($sql);
		$builder->bindValues($command,$this->params);
		return $command;
	}
}
