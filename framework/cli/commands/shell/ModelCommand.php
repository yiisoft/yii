<?php
/**
 * ModelCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * @version $Id$
 */

/**
 * ModelCommand generates a model class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.cli.commands.shell
 * @since 1.0
 */
class ModelCommand extends CConsoleCommand
{
	/**
	 * @var string the template file for the model class.
	 * Defaults to null, meaning using 'framework/cli/views/shell/model/model.php'.
	 */
	public $templateFile;

	private $_schema;
	private $_relations; // where we keep table relations
	private $_tables;

	public function getHelp()
	{
		return <<<EOD
USAGE
  model <class-name> [table-name]

DESCRIPTION
  This command generates a model class with the specified class name.

PARAMETERS
 * class-name: required, model class name. By default, the generated
   model class file will be placed under the directory aliased as
   'application.models'. To override this default, specify the class
   name in terms of a path alias, e.g., 'application.somewhere.ClassName'.

   If the model class belongs to a module, it should be specified
   as 'ModuleID.models.ClassName'.

   If the class name ends with '*', then a model class will be generated
   for every table in the database.

 * table-name: optional, the associated database table name. If not given,
   it is assumed to be the model class name.

   Note, when the class name ends with '*', this parameter will be
   ignored.

EXAMPLES
 * Generates the Post model:
        model Post

 * Generates the Post model which is associated with table 'posts':
        model Post posts

 * Generates the Post model which should belong to module 'admin':
        model admin.models.Post

 * Generates a model class for every table in the current database:
        model *

 * Generates a model class for every table in the current database.
   The model class files are stored under 'protected/models2':
        model application.models2.*

EOD;
	}

	/**
	 * Checks if the given table is a "many to many" helper table.
	 * Their PK has 2 fields, and both of those fields are also FK to other separate tables.
	 * @param CDbTableSchema table to inspect
	 * @return boolean true if table matches description of helpter table.
	 */
	protected function isRelationTable($table)
	{
		$pk = $table->primaryKey;
		return (count($pk) === 2 // we want 2 columns
			&& isset($table->foreignKeys[$pk[0]]) // pk column 1 is also a foreign key
			&& isset($table->foreignKeys[$pk[1]]) // pk column 2 is also a foriegn key
			&& $table->foreignKeys[$pk[0]][0] !== $table->foreignKeys[$pk[1]][0]); // and the foreign keys point different tables
	}

	/**
	 * Generate code to put in ActiveRecord class's relations() function.
	 * @return array indexed by table names, each entry contains array of php code to go in appropriate ActiveRecord class.
	 *		Empty array is returned if database couldn't be connected.
	 */
	protected function generateRelations()
	{
		if(($db=Yii::app()->getDb()) === null)
		{
			echo "Warning: you do not have a 'db' database connection as required by Active Record.\n";
			return array();
		}

		$db->active = true;
		$this->_schema = $schema = $db->schema;
		$relations = array();

		foreach ($schema->tables as $table)
		{
			$tableName = $table->name;

			if ($this->isRelationTable($table))
			{
				$pks = $table->primaryKey;
				$fks = $table->foreignKeys;

				$table0 = $fks[$pks[1]][0];
				$table1 = $fks[$pks[0]][0];
				$className0 = $this->generateClassName($table0);
				$className1 = $this->generateClassName($table1);

				$relationName = $this->generateRelationName($table0, $table1, true);
				$relations[$className0][$relationName] = "array(self::MANY_MANY, '$className1', '$tableName($pks[0], $pks[1])')";

				$relationName = $this->generateRelationName($table1, $table0, true);
				$relations[$className1][$relationName] = "array(self::MANY_MANY, '$className0', '$tableName($pks[0], $pks[1])')";
			}
			else
			{
				foreach ($table->foreignKeys as $fkName => $fkEntry)
				{
					// Put table and key name in variables for easier reading
					$refTable = $fkEntry[0]; // Table name that current fk references to
					$refKey = $fkEntry[1];   // Key in that table being referenced
					$className = $this->generateClassName($tableName);
					$refClassName = $this->generateClassName($refTable);

					// Add relation for this table
					$relationName = $this->generateRelationName($tableName, $fkName, false);
					$relations[$className][$relationName] = "array(self::BELONGS_TO, '$refClassName', '$fkName')";

					// Add relation for the referenced table
					$relationType = $table->primaryKey === $fkName ? 'HAS_ONE' : 'HAS_MANY';
					$relationName = $this->generateRelationName($refTable, $tableName, $relationType==='HAS_MANY');
					$relations[$refClassName][$relationName] = "array(self::$relationType, '$className', '$fkName')";
				}
			}
		}

		return $relations;
	}

	/**
	 * Generates model class name based on a table name
	 * @param string the table name
	 * @return string the generated model class name
	 */
	protected function generateClassName($tableName)
	{
		if(!isset($this->_tables[$tableName]))
		{
			$name=ucwords(trim(strtolower(str_replace(array('-','_'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $tableName)))));
			$this->_tables[$tableName]=str_replace(' ','',$name);
		}
		return $this->_tables[$tableName];
	}

	/**
	 * Generate a name for use as a relation name (inside relations() function in a model).
	 * @param string the name of the table to hold the relation
	 * @param string the foreign key name
	 * @param boolean whether the relation would contain multiple objects
	 */
	protected function generateRelationName($tableName, $fkName, $multiple)
	{
		if(strcasecmp(substr($fkName,-2),'id')===0)
			$relationName=rtrim(substr($fkName, 0, -2),'_');
		else
			$relationName=$fkName;
		$relationName[0]=strtolower($relationName);

		$rawName=$relationName;
		if($multiple)
			$relationName=$this->pluralize($relationName);

		$table=$this->_schema->getTable($tableName);
		$i=0;
		while(isset($table->columns[$relationName]))
			$relationName=$rawName.($i++);
		return $relationName;
	}

	/**
	 * Converts a word to its plural form.
	 * @param string the word to be pluralized
	 * @return string the pluralized word
	 */
	protected function pluralize($name)
	{
		$rules=array(
			'/(x|ch|ss|sh|us|as|is|os)$/i' => '\1es',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/(m)an$/i' => '\1en',
			'/(child)$/i' => '\1ren',
			'/(r)y$/i' => '\1ies',
			'/s$/' => 's',
		);
		foreach($rules as $rule=>$replacement)
		{
			if(preg_match($rule,$name))
				return preg_replace($rule,$replacement,$name);
		}
		return $name.'s';
	}

	/**
	 * Execute the action.
	 * @param array command line parameters specific for this command
	 */
	public function run($args)
	{
		if(!isset($args[0]))
		{
			echo "Error: model class name is required.\n";
			echo $this->getHelp();
			return;
		}
		$className=$args[0];

		if(($pos=strrpos($className,'.'))===false)
			$basePath=Yii::getPathOfAlias('application.models');
		else
		{
			$basePath=Yii::getPathOfAlias(substr($className,0,$pos));
			$className=substr($className,$pos+1);
		}

		if ($className==='*')
		{
			$this->_relations = $this->generateRelations();
			$classNames = array_keys($this->_relations);
		}
		else
		{
			// preset table=>class name map before relation generation
			$this->_tables = array($tableName => $className);
			$this->_relations = $this->generateRelations();
			$classNames = array($className);
			$tableName = isset($args[1])?$args[1]:$className;
			$this->_tables = array($tableName => $className);
		}

		$list=array();
		foreach ($this->_tables as $tableName=>$className)
		{
			$files[$className]=$classFile=$basePath.DIRECTORY_SEPARATOR.$className.'.php';
			$templateFile=$this->templateFile===null?YII_PATH.'/cli/views/shell/model/model.php':$this->templateFile;
			$list[$className.'.php']=array(
				'source'=>$templateFile,
				'target'=>$classFile,
				'callback'=>array($this,'generateModel'),
				'params'=>array($className,$tableName),
			);
		}

		$this->copyFiles($list);

		foreach($files as $className=>$file)
		{
			if(!class_exists($className,false))
				include_once($file);
		}

		$classes=join(", ", $classNames);

		echo <<<EOD

The following model classes are successfully generated:
    $classes

If you have a 'db' database connection, you can test these models now with:
    \$model={$className}::model()->find();
    print_r(\$model);

EOD;
	}

	public function generateModel($source,$params)
	{
		list($className,$tableName)=$params;
		$content=file_get_contents($source);
		$rules=array();
		$labels=array();
		$relations=array();
		if(($db=Yii::app()->getDb())!==null)
		{
			$db->active=true;
			if(($table=$db->schema->getTable($tableName))!==null)
			{
				$required=array();
				$integers=array();
				$numerical=array();
				foreach($table->columns as $column)
				{
					$label=ucwords(trim(strtolower(str_replace(array('-','_'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $column->name)))));
					if(strcasecmp(substr($label,-3),' id')===0)
						$label=substr($label,0,-3);
					$labels[]="'{$column->name}'=>'$label'";
					if($column->isPrimaryKey && $table->sequenceName!==null || $column->isForeignKey)
						continue;
					if(!$column->allowNull && $column->defaultValue===null)
						$required[]=$column->name;
					if($column->type==='integer')
						$integers[]=$column->name;
					else if($column->type==='double')
						$numerical[]=$column->name;
					else if($column->type==='string' && $column->size>0)
						$rules[]="array('{$column->name}','length','max'=>{$column->size})";
				}
				if($required!==array())
					$rules[]="array('".implode(', ',$required)."', 'required')";
				if($integers!==array())
					$rules[]="array('".implode(', ',$integers)."', 'numerical', 'integerOnly'=>true)";
				if($numerical!==array())
					$rules[]="array('".implode(', ',$numerical)."', 'numerical')";

				if(isset($this->_relations[$className]) && is_array($this->_relations[$className]))
					$relations=$this->_relations[$className];
			}
			else
				echo "Warning: the table '$tableName' does not exist in the database.\n";
		}
		else
			echo "Warning: you do not have a 'db' database connection as required by Active Record.\n";

		return $this->renderFile($source,array(
			'className'=>$className,
			'tableName'=>$tableName,
			'columns'=>isset($table) ? $table->columns : array(),
			'rules'=>$rules,
			'labels'=>$labels,
			'relations'=>$relations,
		),true);
	}
}