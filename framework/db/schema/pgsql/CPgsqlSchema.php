<?php
/**
 * CPgsqlSchema class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CPgsqlSchema is the class for retrieving metadata information from a PostgreSQL database.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.db.schema.pgsql
 * @since 1.0
 */
class CPgsqlSchema extends CDbSchema
{
	const DEFAULT_SCHEMA='public';
	private $_sequences=array();

	/**
	 * Quotes a table name for use in a query.
	 * @param string table name
	 * @return string the properly quoted table name
	 */
	public function quoteTableName($name)
	{
		return '"'.$name.'"';
	}

	/**
	 * Creates a table instance representing the metadata for the named table.
	 * @return CDbTableSchema driver dependent table metadata.
	 */
	protected function createTable($name)
	{
		$table=new CPgsqlTableSchema;
		$this->setTableNames($table,$name);
		if(!$this->findColumns($table))
			return null;
		$this->findConstraints($table);

		if(is_string($table->primaryKey) && isset($this->_sequences[$table->primaryKey]))
			$table->sequenceName=$this->_sequences[$table->primaryKey];

		return $table;
	}

	/**
	 * Generates various kinds of table names.
	 * @param CPgsqlTableSchema the table instance
	 * @param string the unquoted table name
	 */
	protected function setTableNames($table,$name)
	{
		$parts=explode('.',str_replace('"','',$name));
		if(isset($parts[1]))
		{
			$schemaName=$parts[0];
			$tableName=$parts[1];
		}
		else
		{
			$schemaName=self::DEFAULT_SCHEMA;
			$tableName=$parts[0];
		}

		$table->name=$tableName;
		$table->schemaName=$schemaName;
		if($schemaName===self::DEFAULT_SCHEMA)
			$table->rawName=$this->quoteTableName($tableName);
		else
			$table->rawName=$this->quoteTableName($schemaName).'.'.$this->quoteTableName($tableName);
	}

	/**
	 * Collects the table column metadata.
	 * @param CPgsqlTableSchema the table metadata
	 * @return boolean whether the table exists in the database
	 */
	protected function findColumns($table)
	{
		$sql=<<<EOD
SELECT a.attname, LOWER(format_type(a.atttypid, a.atttypmod)) AS type, d.adsrc, a.attnotnull, a.atthasdef
FROM pg_attribute a LEFT JOIN pg_attrdef d ON a.attrelid = d.adrelid AND a.attnum = d.adnum
WHERE a.attnum > 0 AND NOT a.attisdropped
	AND a.attrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname=:table
		AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE nspname = :schema))
ORDER BY a.attnum
EOD;
		$command=$this->getDbConnection()->createCommand($sql);
		$command->bindValue(':table',$table->name);
		$command->bindValue(':schema',$table->schemaName);

		if(($columns=$command->queryAll())===array())
			return false;

		foreach($columns as $column)
		{
			$c=$this->createColumn($column);
			$table->columns[$c->name]=$c;

			if(stripos($column['adsrc'],'nextval')===0 && preg_match('/nextval\([^\']*\'([^\']+)\'[^\)]*\)/i',$column['adsrc'],$matches))
			{
				if(strpos($matches[1],'.')!==false || $table->schemaName===self::DEFAULT_SCHEMA)
					$this->_sequences[$c->name]=$matches[1];
				else
					$this->_sequences[$c->name]=$table->schemaName.'.'.$matches[1];
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
		$c=new CPgsqlColumnSchema;
		$c->name=$column['attname'];
		$c->rawName=$this->quoteColumnName($c->name);
		$c->allowNull=!$column['attnotnull'];
		$c->isPrimaryKey=false;
		$c->isForeignKey=false;

		$c->init($column['type'],$column['atthasdef'] ? $column['adsrc'] : null);

		return $c;
	}

	/**
	 * Collects the primary and foreign key column details for the given table.
	 * @param CPgsqlTableSchema the table metadata
	 */
	protected function findConstraints($table)
	{
		$sql=<<<EOD
SELECT conname, consrc, contype, indkey FROM (
	SELECT
		conname,
		CASE WHEN contype='f' THEN
			pg_catalog.pg_get_constraintdef(oid)
		ELSE
			'CHECK (' || consrc || ')'
		END AS consrc,
		contype,
		conrelid AS relid,
		NULL AS indkey
	FROM
		pg_catalog.pg_constraint
	WHERE
		contype IN ('f', 'c')
	UNION ALL
	SELECT
		pc.relname,
		NULL,
		CASE WHEN indisprimary THEN
				'p'
		ELSE
				'u'
		END,
		pi.indrelid,
		indkey
	FROM
		pg_catalog.pg_class pc,
		pg_catalog.pg_index pi
	WHERE
		pc.oid=pi.indexrelid
		AND EXISTS (
			SELECT 1 FROM pg_catalog.pg_depend d JOIN pg_catalog.pg_constraint c
			ON (d.refclassid = c.tableoid AND d.refobjid = c.oid)
			WHERE d.classid = pc.tableoid AND d.objid = pc.oid AND d.deptype = 'i' AND c.contype IN ('u', 'p')
	)
) AS sub
WHERE relid = (SELECT oid FROM pg_catalog.pg_class WHERE relname=:table
	AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace
	WHERE nspname=:schema))
EOD;
		$command=$this->getDbConnection()->createCommand($sql);
		$command->bindValue(':table',$table->name);
		$command->bindValue(':schema',$table->schemaName);
		foreach($command->queryAll() as $row)
		{
			if($row['contype']==='p') // primary key
				$this->findPrimaryKey($table,$row['indkey']);
			else if($row['contype']==='f') // foreign key
				$this->findForeignKey($table,$row['consrc']);
		}
	}

	/**
	 * Collects primary key information.
	 * @param CPgsqlTableSchema the table metadata
	 * @param string pgsql primary key index list
	 */
	protected function findPrimaryKey($table,$indices)
	{
		$indices=join(', ',split(' ',$indices));
		$sql=<<<EOD
SELECT attnum, attname FROM pg_catalog.pg_attribute WHERE
	attrelid=(
		SELECT oid FROM pg_catalog.pg_class WHERE relname=:table AND relnamespace=(
			SELECT oid FROM pg_catalog.pg_namespace WHERE nspname=:schema
		)
	)
    AND attnum IN ({$indices})
EOD;
		$command=$this->getDbConnection()->createCommand($sql);
		$command->bindValue(':table',$table->name);
		$command->bindValue(':schema',$table->schemaName);
		foreach($command->queryAll() as $row)
		{
			$name=$row['attname'];
			if(isset($table->columns[$name]))
			{
				$table->columns[$name]->isPrimaryKey=true;
				if($table->primaryKey===null)
					$table->primaryKey=$name;
				else if(is_string($table->primaryKey))
					$table->primaryKey=array($table->primaryKey,$name);
				else
					$table->primaryKey[]=$name;
			}
		}
	}

	/**
	 * Collects foreign key information.
	 * @param CPgsqlTableSchema the table metadata
	 * @param string pgsql foreign key definition
	 */
	protected function findForeignKey($table,$src)
	{
		$matches=array();
		$brackets='\(([^\)]+)\)';
		$pattern="/FOREIGN\s+KEY\s+{$brackets}\s+REFERENCES\s+([^\(]+){$brackets}/i";
		if(preg_match($pattern,$src,$matches))
		{
			$keys=preg_split('/,\s+/', $matches[1]);
			$tableName=str_replace('"','',$matches[2]);
			$fkeys=preg_split('/,\s+/', $matches[3]);
			foreach($keys as $i=>$key)
			{
				$table->foreignKeys[$key]=array($tableName,$fkeys[$i]);
				if(isset($table->columns[$key]))
					$table->columns[$key]->isForeignKey=true;
			}
		}
	}
}
