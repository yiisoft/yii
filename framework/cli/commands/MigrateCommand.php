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
	 * @var string the default command action. It defaults to 'to'.
	 */
	public $defaultAction='to';

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

	public function actionTo($version=null, $args=array())
	{
		if($version===null)
		{
			$this->actionUp(-1);
			return;
		}
		$originalVersion=$version;
		if(preg_match('/^\d{14}$/',$version))
			$version='m'.$version;
		else if(preg_match('/^(m\d{14})_.*?/',$version,$matches))
			$version=$matches[1];
		else
			die("Error: The version option must be either a timestamp (e.g. 20101129185401)\nor the full name of a migration (e.g. m20101129185401_create_user_table).\n");

		// try migrate up
		$migrations=$this->getNewMigrations();
		foreach($migrations as $i=>$migration)
		{
			if(strpos($migration,$version.'_')===0)
			{
				$this->actionUp($i+1);
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
					$this->actionDown($i);
				return;
			}
		}

		die("Error: Unable to find the version '$originalVersion'.\n");
	}

	public function actionUp($step=1)
	{
		if(($migrations=$this->getNewMigrations())===array())
		{
			echo "No new migration found. Your system is up-to-date.\n";
			return;
		}

		if(($step=(int)$step)>0)
			$migrations=array_slice($migrations,0,$step);

		$n=count($migrations);
		echo "Total $n new ".($n===1 ? 'migration':'migrations')." to be applied:\n";
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

	public function actionDown($step=1)
	{
		if(($step=(int)$step)<=0)
			die("Error: The step option must be greater than 0.");

		if(($migrations=$this->getMigrationHistory($step))===array())
		{
			echo "No migration has been done before.\n";
			return;
		}
		$migrations=array_keys($migrations);

		$n=count($migrations);
		echo "Total $n ".($n===1 ? 'migration':'migrations')." to be removed:\n";
		foreach($migrations as $migration)
			echo "    $migration\n";
		echo "\n";

		if($this->confirm('Remove the above '.($n===1 ? 'migration':'migrations')."?"))
		{
			foreach($migrations as $migration)
				$this->migrateDown($migration);
			echo "\nMigrated down successfully.\n";
		}
	}

	public function actionRedo($step=1)
	{
		if(($step=(int)$step)<=0)
			die("Error: The step option must be greater than 0.");

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

	public function actionHistory($limit=10)
	{
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

	public function actionList()
	{
		$migrations=$this->getNewMigrations();
		if($migrations===array())
			echo "No new migrations found. Your system is up-to-date.\n";
		else
		{
			$n=count($migrations);
			echo "Found $n new ".($n===1 ? 'migration' : 'migrations').":\n";
			foreach($migrations as $migration)
				echo "    ".$migration."\n";
		}
	}

	public function actionCreate($name='untitled')
	{
		if(!preg_match('/^\w+$/',$name))
			die('Error: The name of the migration must contain letters, digits and underscore characters only.');

		$name='m'.gmdate('YmdHis').'_'.$name;
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
		echo "*** removing $class\n";
		$start=microtime(true);
		$migration=$this->instantiateMigration($class);
		$migration->down();
		$this->getDbConnection()->createCommand()->delete($this->migrationTable, 'version=:version', array(':version'=>$class));
		$time=microtime(true)-$start;
		echo "*** removed $class (time: ".sprintf("%.3f",$time)."s)\n\n";
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
			$applied[substr($version,1,14)]=true;

		$migrations=array();
		$handle=opendir($this->migrationPath);
		while(($file=readdir($handle))!==false)
		{
			if($file==='.' || $file==='..')
				continue;
			$path=$this->migrationPath.DIRECTORY_SEPARATOR.$file;
			if(preg_match('/^(m(\d{14})_.*?)\.php$/',$file,$matches) && is_file($path) && !isset($applied[$matches[2]]))
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
  yiic migrate <action> [options]

DESCRIPTION
  This command provides support for database migrations.

ACTIONS
 * to [--version=20101129185401]
   Migrates the database up or down to a specific version. The optional
   'version' option specifies which version the database should be migrated
   up or down to. If 'version' is not given, it means the database should be
   migrated up with all available migrations. This action is the default
   action of the migrate command.
   Examples:
   - yiic migrate
     applies all available migrations
   - yiic migrate to --version=20101129185401
     migrates up or down to version 20101129185401.

 * up [--step=1]
   Applies a number of migrations. The number is specified by the optional
   parameter 'step' which defaults to 1.
   Examples:
   - yiic migrate up
     applies the next available migration
   - yiic migrate up --step=3
     applies the next 3 available migrations

 * down [--step=1]
   Reverts a number of migrations. The number is specified by the optional
   parameter 'step' which defaults to 1.
   Examples:
   - yiic migrate down
     reverts the most recently applied migration
   - yiic migrate down --step=3
     reverts the 3 most recently applied migrations

 * redo [--step=1]
   Redoes a number of migrations. The number is specified by the optional
   parameter 'step' which defaults to 1. This command is equivalent to reverting
   the spcified number of migrations and then applying them.
   Examples:
   - yiic migrate redo
     reverts the most recently applied migration and then applies it
   - yiic migrate redo --step=3
     reverts the 3 most recently applied migrations and then applies them

 * create [--name=untitled]
   Creates a new migration whose name is specified by the optional parameter
   'name'. The new migration will be saved as a PHP class under the migration
   directory (which defaults to 'protected/migrations'). The class name will
   be prefixed with a UTC timestamp to avoid conflict among different parties.
   Examples:
   - yiic migrate create
     creates a new migration named as 'm20101129185401_untitled'
   - yiic migrate create --name=create_user_table
     creates a new migration named as 'm20101129185401_create_user_table'

 * history [--limit=10]
   Displays the most recently applied migrations. The optional parameter
   'limit' specifies the number of migrations to be displayed.
   It defaults to 10.

 * list
   Displays the migrations that have not been applied yet.

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
