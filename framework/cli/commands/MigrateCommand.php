<?php
/**
 * MigrateCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * MigrateCommand manages the database migrations.
 *
 * The implementation of this command and other supporting classes refers to
 * the yii-dbmigrations extension ((https://github.com/pieterclaerhout/yii-dbmigrations),
 * authored by Pieter Claerhout.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.cli.commands
 * @since 1.1.6
 */
class MigrateCommand extends CConsoleCommand
{
	/**
	 * @var string the directory that stores the migrations. Defaults to 'protected/migrations'.
	 */
	public $migrationPath;
	/**
	 * @var string the name of the table for keeping applied migration information.
	 * This table will be automatically created if not exists. Defaults to 'tbl_migration'.
	 * The table structure is: (version varchar(255) primary key, apply_time integer)
	 */
	public $migrationTable='tbl_migration';
	/**
	 * @var string the application component ID that specifies the database connection for
	 * storing migration information. Defaults to 'db'.
	 */
	public $connectionID='db';
	/**
	 * @var string the path of the template file for generating new migrations. If not set,
	 * an internal template will be used.
	 */
	public $templateFile;
	/**
	 * @var string the default command action. It defaults to 'up'.
	 */
	public $defaultAction='up';

	public function beforeAction($action,$params)
	{
		if($this->migrationPath===null)
			$this->migrationPath=Yii::getPathOfAlias('application.migrations');
		if(!is_dir($this->migrationPath))
			die('Error: The migration directory does not exist: '.$this->migrationPath);

		$yiiVersion=Yii::getVersion();
		echo "\nYii Migration Tool v1.0 (based on Yii v{$yiiVersion})\n\n";

		return true;
	}

	public function actionUp($args=array())
	{
		if(($migrations=$this->getNewMigrations())===array())
		{
			echo "No new migration found. Your system is up-to-date.\n";
			return;
		}

		$total=count($migrations);
		$step=isset($args[0]) ? (int)$args[0] : 0;
		if($step>0)
			$migrations=array_slice($migrations,0,$step);

		$n=count($migrations);
		if($n===$total)
			echo "Total $n new ".($n===1 ? 'migration':'migrations')." to be applied:\n";
		else
			echo "Total $n out of $total new ".($total===1 ? 'migration':'migrations')." to be applied:\n";

		foreach($migrations as $migration)
			echo "    $migration\n";
		echo "\n";

		if($this->confirm('Apply the above '.($n===1 ? 'migration':'migrations')."?"))
		{
			foreach($migrations as $migration)
				$this->migrateUp($migration);
			echo "\nMigrated up successfully.\n";
		}
	}

	public function actionDown($args=array())
	{
		$step=isset($args[0]) ? (int)$args[0] : 1;
		if($step<1)
			die("Error: The step parameter must be greater than 0.");

		if(($migrations=$this->getMigrationHistory($step))===array())
		{
			echo "No migration has been done before.\n";
			return;
		}
		$migrations=array_keys($migrations);

		$n=count($migrations);
		echo "Total $n ".($n===1 ? 'migration':'migrations')." to be reverted:\n";
		foreach($migrations as $migration)
			echo "    $migration\n";
		echo "\n";

		if($this->confirm('Revert the above '.($n===1 ? 'migration':'migrations')."?"))
		{
			foreach($migrations as $migration)
				$this->migrateDown($migration);
			echo "\nMigrated down successfully.\n";
		}
	}

	public function actionRedo($args=array())
	{
		$step=isset($args[0]) ? (int)$args[0] : 1;
		if($step<1)
			die("Error: The step parameter must be greater than 0.");

		if(($migrations=$this->getMigrationHistory($step))===array())
		{
			echo "No migration has been done before.\n";
			return;
		}
		$migrations=array_keys($migrations);

		$n=count($migrations);
		echo "Total $n ".($n===1 ? 'migration':'migrations')." to be redone:\n";
		foreach($migrations as $migration)
			echo "    $migration\n";
		echo "\n";

		if($this->confirm('Redo the above '.($n===1 ? 'migration':'migrations')."?"))
		{
			foreach($migrations as $migration)
				$this->migrateDown($migration);
			foreach(array_reverse($migrations) as $migration)
				$this->migrateUp($migration);
			echo "\nMigration redone successfully.\n";
		}
	}

	public function actionTo($args=array())
	{
		if(isset($args[0]))
			$version=$args[0];
		else
			$this->usageError('Please specify which version to migrate to.');

		$originalVersion=$version;
		if(preg_match('/^m?(\d{6}_\d{6})(_.*?)?$/',$version,$matches))
			$version='m'.$matches[1];
		else
			die("Error: The version option must be either a timestamp (e.g. 101129_185401)\nor the full name of a migration (e.g. m101129_185401_create_user_table).\n");

		// try migrate up
		$migrations=$this->getNewMigrations();
		foreach($migrations as $i=>$migration)
		{
			if(strpos($migration,$version.'_')===0)
			{
				$this->actionUp(array($i+1));
				return;
			}
		}

		// try migrate down
		$migrations=array_keys($this->getMigrationHistory(-1));
		foreach($migrations as $i=>$migration)
		{
			if(strpos($migration,$version.'_')===0)
			{
				if($i===0)
					echo "Already at '$originalVersion'. Nothing needs to be done.\n";
				else
					$this->actionDown(array($i));
				return;
			}
		}

		die("Error: Unable to find the version '$originalVersion'.\n");
	}

	public function actionHistory($args=array())
	{
		$limit=isset($args[0]) ? (int)$args[0] : -1;
		$migrations=$this->getMigrationHistory($limit);
		if($migrations===array())
			echo "No migration has been done before.\n";
		else
		{
			$n=count($migrations);
			if($limit>0)
				echo "Showing the last $n applied ".($n===1 ? 'migration' : 'migrations').":\n";
			else
				echo "Total $n ".($n===1 ? 'migration has' : 'migrations have')." been applied before:\n";
			foreach($migrations as $version=>$time)
				echo "    (".date('Y-m-d H:i:s',$time).') '.$version."\n";
		}
	}

	public function actionList($args=array())
	{
		$limit=isset($args[0]) ? (int)$args[0] : -1;
		$migrations=$this->getNewMigrations();
		if($migrations===array())
			echo "No new migrations found. Your system is up-to-date.\n";
		else
		{
			$n=count($migrations);
			if($limit>0 && $n>$limit)
			{
				$migrations=array_slice($migrations,0,$limit);
				echo "Showing $limit out of $n new ".($n===1 ? 'migration' : 'migrations').":\n";
			}
			else
				echo "Found $n new ".($n===1 ? 'migration' : 'migrations').":\n";

			foreach($migrations as $migration)
				echo "    ".$migration."\n";
		}
	}

	public function actionCreate($args=array())
	{
		if(isset($args[0]))
			$name=$args[0];
		else
			$this->usageError('Please provide the name of the new migration.');

		if(!preg_match('/^\w+$/',$name))
			die('Error: The name of the migration must contain letters, digits and/or underscore characters only.');

		$name='m'.gmdate('ymd_His').'_'.$name;
		$content=strtr($this->getTemplate(), array('{ClassName}'=>$name));
		$file=$this->migrationPath.DIRECTORY_SEPARATOR.$name.'.php';

		if($this->confirm("Create new migration '$file'?"))
		{
			file_put_contents($file, $content);
			echo "New migration created successfully.\n";
		}
	}

	protected function confirm($message)
	{
		echo $message.' [yes|no] ';
		return !strncasecmp(trim(fgets(STDIN)),'y',1);
	}

	protected function migrateUp($class)
	{
		echo "*** applying $class\n";
		$start=microtime(true);
		$migration=$this->instantiateMigration($class);
		$migration->up();
		$this->getDbConnection()->createCommand()->insert($this->migrationTable, array(
			'version'=>$class,
			'apply_time'=>time(),
		));
		$time=microtime(true)-$start;
		echo "*** applied $class (time: ".sprintf("%.3f",$time)."s)\n\n";
	}

	protected function migrateDown($class)
	{
		echo "*** reverting $class\n";
		$start=microtime(true);
		$migration=$this->instantiateMigration($class);
		$migration->down();
		$this->getDbConnection()->createCommand()->delete($this->migrationTable, 'version=:version', array(':version'=>$class));
		$time=microtime(true)-$start;
		echo "*** reverted $class (time: ".sprintf("%.3f",$time)."s)\n\n";
	}

	protected function instantiateMigration($class)
	{
		$file=$this->migrationPath.DIRECTORY_SEPARATOR.$class.'.php';
		require_once($file);
		return new $class;
	}

	private $_db;
	protected function getDbConnection()
	{
		if($this->_db!==null)
			return $this->_db;
		else if(($this->_db=Yii::app()->getComponent($this->connectionID)) instanceof CDbConnection)
		{
			$this->_db->setActive(true);
			return $this->_db;
		}
		else
			die("Error: CMigrationCommand.connectionID '{$this->connectionID}' is invalid. Please make sure it refers to the ID of a CDbConnection application component.");
	}

	protected function getMigrationHistory($limit)
	{
		$db=$this->getDbConnection();
		if($db->schema->getTable($this->migrationTable)===null)
		{
			echo 'Creating migration history table "'.$this->migrationTable.'"...';
			$db->createCommand()->createTable($this->migrationTable, array(
				'version'=>'string NOT NULL PRIMARY KEY',
				'apply_time'=>'integer',
			));
			echo "done.\n";
		}
		return CHtml::listData($db->createCommand()
			->select('version, apply_time')
			->from($this->migrationTable)
			->order('version DESC')
			->limit($limit)
			->queryAll(), 'version', 'apply_time');
	}

	protected function getNewMigrations()
	{
		$applied=array();
		foreach($this->getMigrationHistory(-1) as $version=>$time)
			$applied[substr($version,1,13)]=true;

		$migrations=array();
		$handle=opendir($this->migrationPath);
		while(($file=readdir($handle))!==false)
		{
			if($file==='.' || $file==='..')
				continue;
			$path=$this->migrationPath.DIRECTORY_SEPARATOR.$file;
			if(preg_match('/^(m(\d{6}_\d{6})_.*?)\.php$/',$file,$matches) && is_file($path) && !isset($applied[$matches[2]]))
				$migrations[]=$matches[1];
		}
		closedir($handle);
		sort($migrations);
		return $migrations;
	}

	public function getHelp()
	{
		return <<<EOD
USAGE
  yiic migrate <action> [parameter]

DESCRIPTION
  This command provides support for database migrations.

ACTIONS
 * up [step]
   Applies a number of new migrations. The number can be specified by
   the optional parameter 'step'. If it is not given, ALL new migrations
   will be applied.
   Examples:
   - yiic migrate
     applies ALL new migrations. Note that the 'up' action is a default
     action. Therefore, the above command is equivalent to
     yiic migrate up
   - yiic migrate up 3
     applies the next 3 new migrations

 * down [step]
   Reverts a number of migrations. The number is specified by the optional
   parameter 'step' which defaults to 1.
   Examples:
   - yiic migrate down
     reverts the most recently applied migration
   - yiic migrate down 3
     reverts the 3 most recently applied migrations

 * to <version>
   Migrates the database up or down to a specific version. The 'version'
   parameter specifies which version the database should be migrated
   up or down to. It must be given in terms of a timestamp or the full
   migration class name.
   Examples:
   - yiic migrate to 101129_185401
     migrates up or down to version 101129_185401.
   - yiic migrate to m101129_185401_create_user_table
     migrates up or down to version m101129_185401_create_user_table.

 * redo [step]
   Redoes a number of migrations. The number is specified by the optional
   parameter 'step' which defaults to 1. This command is equivalent to
   reverting the spcified number of migrations and then applying them.
   Examples:
   - yiic migrate redo
     reverts the most recently applied migration and then applies it
   - yiic migrate redo 3
     reverts the 3 most recently applied migrations and then applies them

 * create <name>
   Creates a new migration whose name is specified by the parameter
   'name'. The new migration will be saved as a PHP class under the migration
   directory (which defaults to 'protected/migrations'). The class name will
   be prefixed with a UTC timestamp in the format of 'yymmdd_hhmmss' to
   avoid conflict with other migrations. The name should only contain
   letters, digits, and/or underscore characters.
   Examples:
   - yiic migrate create create_user_table
     creates a new migration named as 'm20101129185401_create_user_table'

 * history [limit]
   Displays the most recently applied migrations. The optional parameter
   'limit' specifies the number of migrations to be displayed.
   If the limit is not given, all applied migrations will be displayed.

 * list [limit]
   Displays the migrations that have not been applied yet.
   The optional parameter 'limit' specifies the number of migrations to
   be displayed. If the limit is not given, all unapplied migrations will
   be displayed.

EOD;
	}

	protected function getTemplate()
	{
		if($this->templateFile!==null)
			return file_get_contents($this->templateFile);
		else
			return <<<EOD
<?php

class {ClassName} extends CDbMigration
{
    public function up()
    {
    }

    /*
    public function down()
    {
    }
    */
}
EOD;
	}
}
