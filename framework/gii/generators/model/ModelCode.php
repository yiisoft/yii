<?php

class ModelCode extends CCodeModel
{
	public $tablePrefix;
	public $tableName;
	public $modelClass;
	public $modelPath='application.models';
	public $baseClass='CActiveRecord';

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('tablePrefix, baseClass, tableName, modelClass, modelPath', 'filter', 'filter'=>'trim'),
			array('tableName, modelPath, modelClass, baseClass', 'required'),
			array('tablePrefix, tableName, modelPath', 'match', 'pattern'=>'/^\w+[\w\.]*$/', 'message'=>'{attribute} should only contain word characters and dots.'),
			array('tablePrefix, modelClass, baseClass', 'match', 'pattern'=>'/^\w+$/', 'message'=>'{attribute} should only contain word characters.'),
			array('tableName', 'validateTableName'),
			array('modelPath', 'validateModelPath'),
			array('baseClass', 'validateBaseClass'),
			array('tablePrefix, modelPath, baseClass', 'sticky'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'tablePrefix'=>'Table Prefix',
			'tableName'=>'Table Name',
			'modelPaht'=>'Model Path',
			'modelClass'=>'Model Class',
			'baseClass'=>'Base Class',
		));
	}

	public function init()
	{
		$this->tablePrefix=Yii::app()->db->tablePrefix;
		parent::init();
	}

	public function prepare()
	{
		$this->files=array();
		$templatePath=$this->templatePath;

		$this->files[]=new CCodeFile(
			Yii::getPathOfAlias($this->modelPath).'/'.$this->modelClass.'.php',
			$this->render($templatePath.'/model.php')
		);
	}

	public function validateTableName($attribute,$params)
	{
		if($this->hasErrors('tableName'))
			return;
		if($this->getTableSchema()===null)
			$this->addError('tableName',"Table '{$this->tableName}' does not exist.");
	}

	public function validateModelPath($attribute,$params)
	{
		if($this->hasErrors('modelPath'))
			return;
		if(Yii::getPathOfAlias($this->modelPath)===false)
			$this->addError('modelPath','Model Path must be a valid path alias.');
	}

	public function validateBaseClass($attribute,$params)
	{
		if($this->hasErrors('baseClass'))
			return;
		$class=@Yii::import($this->baseClass,true);
		if(!is_string($class) || !class_exists($class,false))
			$this->addError('baseClass', "Class '{$this->baseClass}' does not exist or has syntax error.");
		else if($class!=='CActiveRecord' && !is_subclass_of($class,'CActiveRecord'))
			$this->addError('baseClass', "'{$this->model}' must extend from CActiveRecord.");
	}

	public function getTableSchema()
	{
		return Yii::app()->db->getSchema()->getTable($this->tableName);
	}

	public function getColumns()
	{
		return Yii::app()->db->schema->getTable($this->tableName)->columns;
	}

	public function getLabels()
	{
		$labels=array();
		foreach($this->tableSchema->columns as $column)
		{
			$label=ucwords(trim(strtolower(str_replace(array('-','_'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $column->name)))));
			$label=preg_replace('/\s+/',' ',$label);
			if(strcasecmp(substr($label,-3),' id')===0)
				$label=substr($label,0,-3);
			if($label==='Id')
				$label='ID';
			$labels[$column->name]=$label;
		}
		return $labels;
	}

	public function getRules()
	{
		$rules=array();
		$table=$this->tableSchema;

		$required=array();
		$integers=array();
		$numerical=array();
		$length=array();
		$safe=array();
		foreach($table->columns as $column)
		{
			if($column->isPrimaryKey && $table->sequenceName!==null)
				continue;
			$r=!$column->allowNull && $column->defaultValue===null;
			if($r)
				$required[]=$column->name;
			if($column->type==='integer')
				$integers[]=$column->name;
			else if($column->type==='double')
				$numerical[]=$column->name;
			else if($column->type==='string' && $column->size>0)
				$length[$column->size][]=$column->name;
			else if(!$column->isPrimaryKey && !$r)
				$safe[]=$column->name;
		}
		if($required!==array())
			$rules[]="array('".implode(', ',$required)."', 'required')";
		if($integers!==array())
			$rules[]="array('".implode(', ',$integers)."', 'numerical', 'integerOnly'=>true)";
		if($numerical!==array())
			$rules[]="array('".implode(', ',$numerical)."', 'numerical')";
		if($length!==array())
		{
			foreach($length as $len=>$cols)
				$rules[]="array('".implode(', ',$cols)."', 'length', 'max'=>$len)";
		}
		if($safe!==array())
			$rules[]="array('".implode(', ',$safe)."', 'safe')";

		return $rules;
	}

	public function getRelations()
	{
		$relations=$this->generateRelations();
		return isset($relations[$this->modelClass]) ? $relations[$this->modelClass] : array();
	}

	public function getTableNameWithoutPrefix()
	{
		return $this->removePrefix($this->tableName);
	}

	protected function removePrefix($tableName,$addBrackets=true)
	{
		$prefix=$this->tablePrefix!='' ? $this->tablePrefix : Yii::app()->db->tablePrefix;
		if($prefix!='')
		{
			$lb=$addBrackets ? '{{':'';
			$rb=$addBrackets ? '}}':'';
			if(($pos=strrpos($tableName,'.'))!==false)
			{
				$schema=substr($tableName,0,$pos);
				$name=substr($tableName,$pos+1);
				if(strpos($name,$prefix)===0)
					return $schema.'.'.$lb.substr($name,strlen($prefix)).$rb;
			}
			else if(strpos($tableName,$prefix)===0)
				return $lb.substr($tableName,strlen($prefix)).$rb;
		}
		return $tableName;
	}

	/**
	 * Generate code to put in ActiveRecord class's relations() function.
	 * @return array indexed by table names, each entry contains array of php code to go in appropriate ActiveRecord class.
	 *		Empty array is returned if database couldn't be connected.
	 */
	protected function generateRelations()
	{
		$relations=array();
		foreach(Yii::app()->db->schema->getTables() as $table)
		{
			$tableName=$table->name;

			if ($this->isRelationTable($table))
			{
				$pks=$table->primaryKey;
				$fks=$table->foreignKeys;

				$table0=$fks[$pks[1]][0];
				$table1=$fks[$pks[0]][0];
				$className0=$this->generateClassName($table0);
				$className1=$this->generateClassName($table1);

				$unprefixedTableName=$this->removePrefix($tableName,true);

				$relationName=$this->generateRelationName($table0, $table1, true);
				$relations[$className0][$relationName]="array(self::MANY_MANY, '$className1', '$unprefixedTableName($pks[0], $pks[1])')";

				$relationName=$this->generateRelationName($table1, $table0, true);
				$relations[$className1][$relationName]="array(self::MANY_MANY, '$className0', '$unprefixedTableName($pks[0], $pks[1])')";
			}
			else
			{
				$className=$this->generateClassName($tableName);
				foreach ($table->foreignKeys as $fkName => $fkEntry)
				{
					// Put table and key name in variables for easier reading
					$refTable=$fkEntry[0]; // Table name that current fk references to
					$refKey=$fkEntry[1];   // Key in that table being referenced
					$refClassName=$this->generateClassName($refTable);

					// Add relation for this table
					$relationName=$this->generateRelationName($tableName, $fkName, false);
					$relations[$className][$relationName]="array(self::BELONGS_TO, '$refClassName', '$fkName')";

					// Add relation for the referenced table
					$relationType=$table->primaryKey === $fkName ? 'HAS_ONE' : 'HAS_MANY';
					$relationName=$this->generateRelationName($refTable, $this->removePrefix($tableName,false), $relationType==='HAS_MANY');
					$relations[$refClassName][$relationName]="array(self::$relationType, '$className', '$fkName')";
				}
			}
		}
		return $relations;
	}

	/**
	 * Checks if the given table is a "many to many" pivot table.
	 * Their PK has 2 fields, and both of those fields are also FK to other separate tables.
	 * @param CDbTableSchema table to inspect
	 * @return boolean true if table matches description of helpter table.
	 */
	protected function isRelationTable($table)
	{
		$pk=$table->primaryKey;
		return (count($pk) === 2 // we want 2 columns
			&& isset($table->foreignKeys[$pk[0]]) // pk column 1 is also a foreign key
			&& isset($table->foreignKeys[$pk[1]]) // pk column 2 is also a foriegn key
			&& $table->foreignKeys[$pk[0]][0] !== $table->foreignKeys[$pk[1]][0]); // and the foreign keys point different tables
	}

	protected function generateClassName($tableName)
	{
		if($this->tableSchema->name===$tableName)
			return $this->modelClass;

		$tableName=$this->removePrefix($tableName,false);
		$className='';
		foreach(explode('_',$tableName) as $name)
		{
			if($name!=='')
				$className.=ucfirst($name);
		}
		return $className;
	}

	/**
	 * Generate a name for use as a relation name (inside relations() function in a model).
	 * @param string the name of the table to hold the relation
	 * @param string the foreign key name
	 * @param boolean whether the relation would contain multiple objects
	 * @return string the relation name
	 */
	protected function generateRelationName($tableName, $fkName, $multiple)
	{
		if(strcasecmp(substr($fkName,-2),'id')===0 && strcasecmp($fkName,'id'))
			$relationName=rtrim(substr($fkName, 0, -2),'_');
		else
			$relationName=$fkName;
		$relationName[0]=strtolower($relationName);

		$rawName=$relationName;
		if($multiple)
			$relationName=$this->pluralize($relationName);

		$table=Yii::app()->db->schema->getTable($tableName);
		$i=0;
		while(isset($table->columns[$relationName]))
			$relationName=$rawName.($i++);

		$names=preg_split('/_+/',$relationName,-1,PREG_SPLIT_NO_EMPTY);
		if(empty($names)) return $relationName;  // unlikely
		for($name=$names[0], $i=1;$i<count($names);++$i)
			$name.=ucfirst($names[$i]);
		return $name;
	}
}