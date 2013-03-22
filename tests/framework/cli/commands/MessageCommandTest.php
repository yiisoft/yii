<?php

Yii::import('system.cli.commands.MessageCommand');

/**
 * Test case for "system.cli.commands.MessageCommand"
 * @see MessageCommand
 */
class MessageCommandTest extends CTestCase
{
	protected $sourcePath='';
	protected $messagePath='';
	protected $configFileName='';

	public function setUp()
	{
		$this->sourcePath=Yii::getPathOfAlias('application.runtime.test_source');
		$this->createDir($this->sourcePath);
		$this->messagePath=Yii::getPathOfAlias('application.runtime.test_messages');
		$this->createDir($this->messagePath);
		$this->configFileName=Yii::getPathOfAlias('application.runtime').DIRECTORY_SEPARATOR.'message_command_test_config.php';
	}

	public function tearDown()
	{
		$this->removeDir($this->sourcePath);
		$this->removeDir($this->messagePath);
		if(file_exists($this->configFileName))
			unlink($this->configFileName);
	}

	/**
	 * Creates directory.
	 * @param $dirName directory full name
	 */
	protected function createDir($dirName)
	{
		if(!file_exists($dirName))
			mkdir($dirName,0777,true);
	}

	/**
	 * Removes directory.
	 * @param $dirName directory full name
	 */
	protected function removeDir($dirName)
	{
		if(!empty($dirName) && file_exists($dirName))
		{
			$this->removeFileSystemObject($dirName);
		}
	}

	/**
	 * Removes file system object: directory or file.
	 * @param string $fileSystemObjectFullName file system object full name.
	 */
	protected function removeFileSystemObject($fileSystemObjectFullName)
	{
		if(!is_dir($fileSystemObjectFullName))
		{
			unlink($fileSystemObjectFullName);
		} else {
			$dirHandle = opendir($fileSystemObjectFullName);
			while(($fileSystemObjectName=readdir($dirHandle))!==false)
			{
				if($fileSystemObjectName==='.' || $fileSystemObjectName==='..')
					continue;
				$this->removeFileSystemObject($fileSystemObjectFullName.DIRECTORY_SEPARATOR.$fileSystemObjectName);
			}
			closedir($dirHandle);
			rmdir($fileSystemObjectFullName);
		}
	}

	/**
	 * @return MessageCommand message command instance
	 */
	protected function createMessageCommand()
	{
		//$command=new MessageCommand('message',null);
		$command=$this->getMock('MessageCommand',array('usageError'),array('message',null));
		$command->expects($this->any())->method('usageError')->will($this->throwException(new Exception('usageError')));
		return $command;
	}

	/**
	 * Emulates running of the message command.
	 * @param array $args command shell arguments
	 * @return string command output
	 */
	protected function runMessageCommand(array $args)
	{
		$command=$this->createMessageCommand();
		ob_start();
		ob_implicit_flush(false);
		$command->run($args);
		return ob_get_clean();
	}

	/**
	 * Creates message command config file at {@link configFileName}
	 * @param array $config message command config.
	 */
	protected function composeConfigFile(array $config)
	{
		if(file_exists($this->configFileName))
			unlink($this->configFileName);
		$fileContent='<?php return '.var_export($config,true).';';
		file_put_contents($this->configFileName,$fileContent);
	}

	/**
	 * Creates source file with given content
	 * @param string $content file content
	 * @param string|null $name file self name
	 */
	protected function createSourceFile($content,$name=null)
	{
		if(empty($name))
			$name=md5(uniqid()).'.php';
		file_put_contents($this->sourcePath.DIRECTORY_SEPARATOR.$name,$content);
	}

	// Tests:

	public function testEmptyArgs()
	{
		$this->setExpectedException('Exception','usageError');
		$this->runMessageCommand(array());
	}

	public function testConfigFileNotExist()
	{
		$this->setExpectedException('Exception','usageError');
		$this->runMessageCommand(array('not_existing_file.php'));
	}

	public function testCreateTranslation()
	{
		$language = 'en';

		$category='test_category';
		$message='test message';
		$sourceFileContent = "Yii::t('{$category}','{$message}')";
		$this->createSourceFile($sourceFileContent);

		$this->composeConfigFile(array(
			'languages'=>array($language),
			'sourcePath'=>$this->sourcePath,
			'messagePath'=>$this->messagePath,
		));
		$this->runMessageCommand(array($this->configFileName));

		$this->assertTrue(file_exists($this->messagePath.DIRECTORY_SEPARATOR.$language),'No language dir created!');
		$messageFileName=$this->messagePath.DIRECTORY_SEPARATOR.$language.DIRECTORY_SEPARATOR.$category.'.php';
		$this->assertTrue(file_exists($messageFileName),'No message file created!');
		$messages=require($messageFileName);
		$this->assertTrue(is_array($messages),'Unable to compose messages!');
		$this->assertTrue(array_key_exists($message,$messages),'Source message is missing!');
	}



}
