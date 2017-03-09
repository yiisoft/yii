<?php
/**
 * MessageCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * MessageCommand extracts messages to be translated from source files.
 * The extracted messages are saved as PHP message source files
 * under the specified directory.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.cli.commands
 * @since 1.0
 */
class MessageCommand extends CConsoleCommand
{
	public function getHelp()
	{
		return <<<EOD
USAGE
  yiic message <config-file>

DESCRIPTION
  This command searches for messages to be translated in the specified
  source files and compiles them into PHP arrays as message source.

PARAMETERS
 * config-file: required, the path of the configuration file. You can find
   an example in framework/messages/config.php.

   The file can be placed anywhere and must be a valid PHP script which
   returns an array of name-value pairs. Each name-value pair represents
   a configuration option.

   The following options are available:

   - sourcePath: string, root directory of all source files.
   - messagePath: string, root directory containing message translations.
   - languages: array, list of language codes that the extracted messages
     should be translated to. For example, array('zh_cn','en_au').
   - fileTypes: array, a list of file extensions (e.g. 'php', 'xml').
     Only the files whose extension name can be found in this list
     will be processed. If empty, all files will be processed.
   - exclude: array, a list of directory and file exclusions. Each
     exclusion can be either a name or a path. If a file or directory name
     or path matches the exclusion, it will not be copied. For example,
     an exclusion of '.svn' will exclude all files and directories whose
     name is '.svn'. And an exclusion of '/a/b' will exclude file or
     directory 'sourcePath/a/b'.
   - translator: the name of the function for translating messages.
     Defaults to 'Yii::t'. This is used as a mark to find messages to be
     translated. Accepts both string for single function name or array for
     multiple function names.
   - overwrite: if message file must be overwritten with the merged messages.
   - removeOld: if message no longer needs translation it will be removed,
     instead of being enclosed between a pair of '@@' marks.
   - sort: sort messages by key when merging, regardless of their translation
     state (new, obsolete, translated.)
   - fileHeader: A boolean indicating whether the file should contain a default
     comment that explains the message file or a string representing
     some PHP code or comment to add before the return tag in the message file.

EOD;
	}

	/**
	 * Execute the action.
	 * @param array $args command line parameters specific for this command
	 */
	public function run($args)
	{
		if(!isset($args[0]))
			$this->usageError('the configuration file is not specified.');
		if(!is_file($args[0]))
			$this->usageError("the configuration file {$args[0]} does not exist.");

		$config=require($args[0]);
		$translator='Yii::t';
		extract($config);

		if(!isset($sourcePath,$messagePath,$languages))
			$this->usageError('The configuration file must specify "sourcePath", "messagePath" and "languages".');
		if(!is_dir($sourcePath))
			$this->usageError("The source path $sourcePath is not a valid directory.");
		if(!is_dir($messagePath))
			$this->usageError("The message path $messagePath is not a valid directory.");
		if(empty($languages))
			$this->usageError("Languages cannot be empty.");

		if(!isset($overwrite))
			$overwrite = false;

		if(!isset($removeOld))
			$removeOld = false;

		if(!isset($sort))
			$sort = false;

		if(!isset($fileHeader))
			$fileHeader = true;

		$options=array();
		if(isset($fileTypes))
			$options['fileTypes']=$fileTypes;
		if(isset($exclude))
			$options['exclude']=$exclude;
		$files=CFileHelper::findFiles(realpath($sourcePath),$options);

		$messages=array();
		foreach($files as $file)
			$messages=array_merge_recursive($messages,$this->extractMessages($file,$translator));

		foreach($languages as $language)
		{
			$dir=$messagePath.DIRECTORY_SEPARATOR.$language;
			if(!is_dir($dir))
				@mkdir($dir);
			foreach($messages as $category=>$msgs)
			{
				$msgs=array_values(array_unique($msgs));
				$this->generateMessageFile($msgs,$dir.DIRECTORY_SEPARATOR.$category.'.php',$overwrite,$removeOld,$sort,$fileHeader);
			}
		}
	}

	protected function extractMessages($fileName,$translator)
	{
		echo "Extracting messages from $fileName...\n";
		$subject=file_get_contents($fileName);
		$messages=array();
		if(!is_array($translator))
			$translator=array($translator);

		foreach ($translator as $currentTranslator)
		{
			$n=preg_match_all('/\b'.$currentTranslator.'\s*\(\s*(\'[\w.\/]*?(?<!\.)\'|"[\w.]*?(?<!\.)")\s*,\s*(\'.*?(?<!\\\\)\'|".*?(?<!\\\\)")\s*[,\)]/s',$subject,$matches,PREG_SET_ORDER);

			for($i=0;$i<$n;++$i)
			{
				if(($pos=strpos($matches[$i][1],'.'))!==false)
					$category=substr($matches[$i][1],$pos+1,-1);
				else
					$category=substr($matches[$i][1],1,-1);
				$message=$matches[$i][2];
				try
				{
					$evalResult = eval("return $message;");  // use eval to eliminate quote escape
				}
				catch (ParseError $e)
				{
					$evalResult = false;
				}
				$messages[$category][] = $evalResult;
			}
		}
		return $messages;
	}

	protected function generateMessageFile($messages,$fileName,$overwrite,$removeOld,$sort,$fileHeader)
	{
		echo "Saving messages to $fileName...";
		if(is_file($fileName))
		{
			$translated=require($fileName);
			sort($messages);
			ksort($translated);
			if(array_keys($translated)==$messages)
			{
				echo "nothing new...skipped.\n";
				return;
			}
			$merged=array();
			$untranslated=array();
			foreach($messages as $message)
			{
				if(array_key_exists($message,$translated) && strlen($translated[$message])>0)
					$merged[$message]=$translated[$message];
				else
					$untranslated[]=$message;
			}
			ksort($merged);
			sort($untranslated);
			$todo=array();
			foreach($untranslated as $message)
				$todo[$message]='';
			ksort($translated);
			foreach($translated as $message=>$translation)
			{
				if(!isset($merged[$message]) && !isset($todo[$message]) && !$removeOld)
				{
					if(substr($translation,0,2)==='@@' && substr($translation,-2)==='@@')
						$todo[$message]=$translation;
					else
						$todo[$message]='@@'.$translation.'@@';
				}
			}
			$merged=array_merge($todo,$merged);
			if($sort)
				ksort($merged);
			if($overwrite === false)
				$fileName.='.merged';
			echo "translation merged.\n";
		}
		else
		{
			$merged=array();
			foreach($messages as $message)
				$merged[$message]='';
			ksort($merged);
			echo "saved.\n";
		}
		$array=str_replace("\r",'',var_export($merged,true));
		if($fileHeader===true)
			$fileHeader=<<<EOD
/**
 * Message translations.
 *
 * This file is automatically generated by 'yiic message' command.
 * It contains the localizable messages extracted from source code.
 * You may modify this file by translating the extracted messages.
 *
 * Each array element represents the translation (value) of a message (key).
 * If the value is empty, the message is considered as not translated.
 * Messages that no longer need translation will have their translations
 * enclosed between a pair of '@@' marks.
 *
 * Message string can be used with plural forms format. Check i18n section
 * of the guide for details.
 *
 * NOTE, this file must be saved in UTF-8 encoding.
 */
EOD;
		elseif($fileHeader===false)
			$fileHeader='';

		file_put_contents($fileName,<<<EOD
<?php
$fileHeader
return $array;

EOD
		);
	}
}
