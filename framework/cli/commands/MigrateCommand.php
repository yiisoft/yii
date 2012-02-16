<?php
/**
 * MigrateCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * MigrateCommand manages the database migrations.
 *
 * The implementation of this command and other supporting classes referenced
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
	const BASE_MIGRATION='m000000_000000_base';

	/**
	 * @var string the directory that stores the migrations. This must be specified
	 * in terms of a path alias, and the corresponding directory must exist.
	 * Defaults to 'application.migrations' (meaning 'protected/migrations').
	 */
	public $migrationPath='application.migrations';
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
	 * @var string the path of the template file for generating new migrations. This
	 * must be specified in terms of a path alias (e.g. application.migrations.template).
	 * If not set, an internal template will be used.
	 */
	public $templateFile;
	/**
	 * @var string the default command action. It defaults to 'up'.
	 */
	public $defaultAction='up';
	/**
	 * @var boolean whether to execute the migration in an interactive mode. Defaults to true.
	 * Set this to false when performing migration in a cron job or background process.
	 */
	public $interactive=true;
	
	/**
    * @var string the modulename for the application migrations.
    * It is defaulted to 'app'
    */
    public $applicationModuleName = 'app';
    
    /**
    * @var array  disabled modules
    */
    public $disabledModules = array();

	public function beforeAction($action,$params)
	{
		$path=Yii::getPathOfAlias($this->migrationPath);
		if($path===false || !is_dir($path))
			die('Error: The migration directory does not exist: '.$this->migrationPath."\n");
		$this->migrationPath=$path;

		$yiiVersion=Yii::getVersion();
		echo "\nYii Migration Tool v1.1 (based on Yii v{$yiiVersion})\n\n";

		return true;
	}

	public function actionUp($args)
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
			{
				if($this->migrateUp($migration)===false)
				{
					echo "\nMigration failed. All later migrations are canceled.\n";
					return;
				}
			}
			echo "\nMigrated up successfully.\n";
		}
	}

	public function actionDown($args)
	{
		$step=isset($args[0]) ? (int)$args[0] : 1;
		if($step<1)
			die("Error: The step parameter must be greater than 0.\n");

		if(($migrations=$this->getMigrationHistory($step))===array())
		{
			echo "No migration has been done before.\n";
			return;
		}

		$n=count($migrations);
		echo "Total $n ".($n===1 ? 'migration':'migrations')." to be reverted:\n";
		foreach($migrations as $migration)
			echo "    {$migration['module']}.{$migration['version']}\n";
		echo "\n";

		if($this->confirm('Revert the above '.($n===1 ? 'migration':'migrations')."?"))
		{
			foreach($migrations as $migration)
			{
				if($this->migrateDown("{$migration['module']}.{$migration['version']}")===false)
				{
					echo "\nMigration failed. All later migrations are canceled.\n";
					return;
				}
			}
			echo "\nMigrated down successfully.\n";
		}
	}

	public function actionRedo($args)
	{
		$step=isset($args[0]) ? (int)$args[0] : 1;
		if($step<1)
			die("Error: The step parameter must be greater than 0.\n");

		if(($migrations=$this->getMigrationHistory($step))===array())
		{
			echo "No migration has been done before.\n";
			return;
		}

		$n=count($migrations);
		echo "Total $n ".($n===1 ? 'migration':'migrations')." to be redone:\n";
		foreach($migrations as $migration)
			echo "    {$migration['module']}.{$migration['version']}\n";
		echo "\n";

		if($this->confirm('Redo the above '.($n===1 ? 'migration':'migrations')."?"))
		{
			foreach($migrations as $migration)
			{
				if($this->migrateDown("{$migration['module']}.{$migration['version']}")===false)
				{
					echo "\nMigration failed. All later migrations are canceled.\n";
					return;
				}
			}
			foreach(array_reverse($migrations) as $migration)
			{
				if($this->migrateUp("{$migration['module']}.{$migration['version']}")===false)
				{
					echo "\nMigration failed. All later migrations are canceled.\n";
					return;
				}
			}
			echo "\nMigration redone successfully.\n";
		}
	}

	public function actionTo($args)
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

	public function actionMark($args)
	{
		if(isset($args[0]))
			$version=$args[0];
		else
			$this->usageError('Please specify which version to mark to.');
		$originalVersion=$version;
		if(preg_match('/^m?(\d{6}_\d{6})(_.*?)?$/',$version,$matches))
			$version='m'.$matches[1];
		else
			die("Error: The version option must be either a timestamp (e.g. 101129_185401)\nor the full name of a migration (e.g. m101129_185401_create_user_table).\n");

		$db=$this->getDbConnection();

		// try mark up
		$migrations=$this->getNewMigrations();
		foreach($migrations as $i=>$migration)
		{
			if(strpos($migration,$version.'_')===0)
			{
				if($this->confirm("Set migration history at $originalVersion?"))
				{
					$command=$db->createCommand();
					for($j=0;$j<=$i;++$j)
					{
						$command->insert($this->migrationTable, array(
							'version'=>$migrations[$j],
							'apply_time'=>time(),
						));
					}
					echo "The migration history is set at $originalVersion.\nNo actual migration was performed.\n";
				}
				return;
			}
		}

		// try mark down
		$migrations=array_keys($this->getMigrationHistory(-1));
		foreach($migrations as $i=>$migration)
		{
			if(strpos($migration,$version.'_')===0)
			{
				if($i===0)
					echo "Already at '$originalVersion'. Nothing needs to be done.\n";
				else
				{
					if($this->confirm("Set migration history at $originalVersion?"))
					{
						$command=$db->createCommand();
						for($j=0;$j<$i;++$j)
							$command->delete($this->migrationTable, $db->quoteColumnName('version').'=:version', array(':version'=>$migrations[$j]));
						echo "The migration history is set at $originalVersion.\nNo actual migration was performed.\n";
					}
				}
				return;
			}
		}

		die("Error: Unable to find the version '$originalVersion'.\n");
	}

	public function actionHistory($args)
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
			foreach($migrations as $migration)
				echo "    (".date('Y-m-d H:i:s',$migration['apply_time']).') '.$migration['version']."\n";
		}
	}

	public function actionNew($args)
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
	
	/**
	* @param string $module optional name of the module. @since v1.1, Yii version 1.1.11
	*/
	public function actionCreate($args, $module = null)
    {
            if(isset($args[0]))
                    $name=$args[0];
            else
                    $this->usageError('Please provide the name of the new migration.');
            
            
            if(!preg_match('/^\w+$/',$name))
                    die("Error: The name of the migration must contain letters, digits and/or underscore characters only.\n");

            $name='m'.gmdate('ymd_His').'_'.$name;
            $content=strtr($this->getTemplate(), array('{ClassName}'=>$name));
            if(!is_null($module)){
                $directory = Yii::getPathOfAlias('application.modules.'.$module.'.migrations');
                if(!is_dir($directory) || !is_readable($directory))
                    die("Error: Migration directory doesn't exist: $directory.\n");
                $file=$directory.DIRECTORY_SEPARATOR.$name.'.php';
            }else{
                $file=$this->migrationPath.DIRECTORY_SEPARATOR.$name.'.php';
            }
            if($this->confirm("Create new migration '$file'?"))
            {
                    file_put_contents($file, $content);
                    echo "New migration created successfully.\n";
            }
    }

	public function confirm($message)
	{
		if(!$this->interactive)
			return true;
		return parent::confirm($message);
	}

	protected function migrateUp($class)
    {
            if($class===self::BASE_MIGRATION)
                    return;
            
            echo "*** applying $class\n";
            $splitted = explode('.',$class);
            $start=microtime(true);
            $migration=$this->instantiateMigration($class);
            $time=microtime(true)-$start;
            if($migration->up()!==false)
            {
                    $this->getDbConnection()->createCommand()->insert($this->migrationTable, array(
                            'version'=>$splitted[1],
                            'module'=>$splitted[0],
                            'apply_time'=>time(),
                    ));
                    echo "*** applied $class (time: ".sprintf("%.3f",$time)."s)\n\n";
            }
            else
            {
                    echo "*** failed to apply $class (time: ".sprintf("%.3f",$time)."s)\n\n";
                    return false;
            }
    }

	protected function migrateDown($class)
    {
            if($class===self::BASE_MIGRATION)
                    return;

            echo "*** reverting $class\n";
            $start=microtime(true);
            $migration=$this->instantiateMigration($class);
            $time=microtime(true)-$start;
            if($migration->down()!==false)
            {
                    $splitted = explode('.',$class);
                    $db=$this->getDbConnection();
                    $db->createCommand()->delete($this->migrationTable, $db->quoteColumnName('version').'=:version', array(':version'=> end($splitted) ));
                    echo "*** reverted $class (time: ".sprintf("%.3f",$time)."s)\n\n";
            }
            else
            {
                    echo "*** failed to revert $class (time: ".sprintf("%.3f",$time)."s)\n\n";
                    return false;
            }
    }

	protected function instantiateMigration($class)
    {
            $file= $this->getMigrationPath($class);
            require_once($file);
            $s = explode('.',$class);
            $migration=new $s[1];
            $migration->setDbConnection($this->getDbConnection());
            return $migration;
    }

	/**
	 * @var CDbConnection
	 */
	private $_db;
	protected function getDbConnection()
	{
		if($this->_db!==null)
			return $this->_db;
		else if(($this->_db=Yii::app()->getComponent($this->connectionID)) instanceof CDbConnection)
			return $this->_db;
		else
			die("Error: CMigrationCommand.connectionID '{$this->connectionID}' is invalid. Please make sure it refers to the ID of a CDbConnection application component.\n");
	}

	protected function getMigrationHistory($limit)
	{
		$db=$this->getDbConnection();
		if(($table = $db->schema->getTable($this->migrationTable)) === null)
		{
			$this->createMigrationHistoryTable();
		}else{
			//checks for v.1 migration table and add column for module if needed
			if(!in_array('module', array_keys($table->columns))){
				$db->createCommand()->addColumn($this->migrationTable, 'module', 'string NOT NULL');
				$db->createCommand()->update($this->migrationTable, array('module' => $this->applicationModuleName));
			}
		}
		$migrations = array();
        foreach($db->createCommand()
                ->select('*')
                ->from($this->migrationTable)
                ->order('version DESC')
                ->limit($limit)
                ->queryAll() as $m){
            $migrations[] = $m;
        }
        return $migrations;
	}

	protected function createMigrationHistoryTable()
	{
		$db=$this->getDbConnection();
		echo 'Creating migration history table "'.$this->migrationTable.'"...';
		$db->createCommand()->createTable($this->migrationTable, array(
		                            'version' => 'string NOT NULL PRIMARY KEY',
		                            'module' => 'string NOT NULL',
		                            'apply_time' => 'integer',
		                    ));
        $db->createCommand()->insert($this->migrationTable, array(
                'version'=>self::BASE_MIGRATION,
                'module' => $this->applicationModuleName,
                'apply_time'=>time(),
        ));
		echo "done.\n";
	}
	/**
	* @since v1.1, Yii 1.1.11
	*/
	protected function getMigrationPath($class)
    {
        $splitted = explode('.',$class);
        if($splitted[0] == $this->applicationModuleName){
            return $this->migrationPath.DIRECTORY_SEPARATOR.$splitted[1].'.php';
        }else{
            $path = Yii::app()->modulePath.DIRECTORY_SEPARATOR.$splitted[0].DIRECTORY_SEPARATOR.'migrations'.DIRECTORY_SEPARATOR.$splitted[1].'.php';
            return $path;
        }
    }
	
    protected function getNewMigrations()
    {
            $applied=array();
            foreach($this->getMigrationHistory(-1) as $migration){
                    $applied[$migration['version'].'.php']=true;
            }
            $migrations=array();
            $handle=opendir($this->migrationPath);
            while(($file=readdir($handle))!==false)
            {
                    if($file==='.' || $file==='..')
                            continue;
                    $path=$this->migrationPath.DIRECTORY_SEPARATOR.$file;
                    if(preg_match('/^(m(\d{6}_\d{6})_.*?)\.php$/',$file,$matches) && is_file($path) && !isset($applied[$file])){
                            $migrations[]= $this->applicationModuleName.'.'.$matches[1];
                    }
            }
            closedir($handle);
            sort($migrations);
            
            foreach(Yii::app()->modules as $module){
                    preg_match('/^(.*)\./',$module['class'],$match);                    
                    $module_name = end($match);
                    $directory = Yii::getPathOfAlias('application.modules.'.end($match).'.migrations');
                    if(!is_dir($directory) || !is_readable($directory))continue;
                    $handle=opendir($directory);
                    while(($file=readdir($handle))!==false)
                    {
                            if($file==='.' || $file==='..')
                                    continue;
                            $path=$directory.DIRECTORY_SEPARATOR.$file;
                            if(preg_match('/^(m(\d{6}_\d{6})_.*?)\.php$/',$file,$matches) && is_file($path) && !isset($applied[$file])){
                                    $migrations[]= $module_name.'.'.$matches[1];
                            }
                    }
                    closedir($handle);
            }
            return $migrations;
    }
	public function getHelp()
	{
		return <<<EOD
USAGE
  yiic migrate [action] [parameter]

DESCRIPTION
  This command provides support for database migrations. The optional
  'action' parameter specifies which specific migration task to perform.
  It can take these values: up, down, to, create, history, new, mark.
  If the 'action' parameter is not given, it defaults to 'up'.
  Each action takes different parameters. Their usage can be found in
  the following examples.

EXAMPLES
 * yiic migrate
   Applies ALL new migrations. This is equivalent to 'yiic migrate up'.

 * yiic migrate create create_user_table
   Creates a new migration named 'create_user_table'.

 * yiic migrate up 3
   Applies the next 3 new migrations.

 * yiic migrate down
   Reverts the last applied migration.

 * yiic migrate down 3
   Reverts the last 3 applied migrations.

 * yiic migrate to 101129_185401
   Migrates up or down to version 101129_185401.

 * yiic migrate mark 101129_185401
   Modifies the migration history up or down to version 101129_185401.
   No actual migration will be performed.

 * yiic migrate history
   Shows all previously applied migration information.

 * yiic migrate history 10
   Shows the last 10 applied migrations.

 * yiic migrate new
   Shows all new migrations.

 * yiic migrate new 10
   Shows the next 10 migrations that have not been applied.

EOD;
	}

	protected function getTemplate()
	{
		if($this->templateFile!==null)
			return file_get_contents(Yii::getPathOfAlias($this->templateFile).'.php');
		else
			return <<<EOD
<?php

class {ClassName} extends CDbMigration
{
	public function up()
	{
	}

	public function down()
	{
		echo "{ClassName} does not support migration down.\\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}
EOD;
	}
}
