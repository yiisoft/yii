<?php
/**
 * Utf8Command class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Utf8Command will help you to make sure files are encoded properly.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.build
 * @since 1.1.11
 */
class Utf8Command extends CConsoleCommand
{
	public function getHelp()
	{
		return <<<EOD
USAGE
  yiic utf8 <action> <file>


DESCRIPTION
  This command can detect and remove UTF-8 BOM headers. It also supports
  detection of wrong file encodings (non UTF-8).


PARAMETERS
 * action: required, the name of the action to execute. The following
 actions are available:
   - checkbom: checks for UTF-8 BOM header
   - fixbom: removes UTF-8 BOM header
   - checkencoding: checks for correct UTF-8 encoding
 * file: optional, the file to process. If not set, all (!) translation files
   will be processed.
EOD;
	}


	public function run($args)
	{
		if(!isset($args[0]))
			$this->usageError("Please specify a valid action");
		if(!in_array($args[0],array('checkbom','fixbom','checkencoding')))
			$this->usageError("Invalid action '{$args[0]}' specified");
		if(isset($args[1]) && !file_exists($args[1]))
			$this->usageError("File '{$args[1]}' does not exist");


		if('checkbom'===$args[0])
		{
			if(isset($args[1]))
			{
				if($this->checkBom($args[1]))
					echo "UTF-8 BOM header detected";
				else
					echo "File seems to be clean";
			}
			else
			{
				$affectedFiles='';
				foreach($this->findTranslationFiles() as $file)
				{
					if($this->checkBom($file))
						$affectedFiles.="{$file}\r\n";
				}
				if(empty($affectedFiles))
					echo "All files seem to be clean";
				else
					echo "Detected UTF-8 BOM header in the following files:\r\n".trim($affectedFiles);
			}
		}
		elseif('fixbom'===$args[0])
		{
			if(isset($args[1]))
			{
				if(!$this->checkBom($args[1]))
					echo "Nothing to fix, no UTF-8 BOM header detected";
				else
				{
					$this->fixBom($args[1]);
					echo "UTF-8 BOM header removed";
				}
			}
			else
			{
				$affectedFiles='';
				foreach($this->findTranslationFiles() as $file)
				{
					if($this->checkBom($file))
					{
						$affectedFiles.="{$file}\r\n";
						$this->fixBom($file);
					}
				}
				if(empty($affectedFiles))
					echo "Nothing to fix, all files seem to be clean";
				else
					echo "Removed UTF-8 BOM header from the following files:\r\n".trim($affectedFiles);
			}
		}
		elseif('checkencoding'===$args[0])
		{
			if(isset($args[1]))
			{
				if($this->checkEncoding($args[1]))
					echo "File does have a correct UTF-8 encoding";
				else
					echo "Wrong encoding detected";
			}
			else
			{
				$affectedFiles='';
				foreach($this->findTranslationFiles() as $file)
				{
					if(!$this->checkEncoding($file))
						$affectedFiles.="{$file}\r\n";
				}
				if(empty($affectedFiles))
					echo "All files seem to have the correct UTF-8 encoding";
				else
					echo "Wrong encoding of the following files detected:\r\n".trim($affectedFiles);
			}
		}
	}

	public function checkBom($file)
	{
		$data=file_get_contents($file,false,null,0,3);
		return bin2hex($data)==='efbbbf';
	}

	public function fixBom($file)
	{
		$data=file_get_contents($file,false,null,3);
		file_put_contents($file,$data);
	}

	public function checkEncoding($file)
	{
		$data=file_get_contents($file);
		return preg_match('/./u',$data) && $this->is_utf8($data);
	}

	public function findTranslationFiles()
	{
		return CFileHelper::findFiles(
			dirname(Yii::app()->basePath),
			array(
				'fileTypes' => array('txt', 'php'),
				'exclude' => array(
					'/index.php',
					'/members.txt',
					'/blog/source',
					'/css',
					'/framework',
					'/guide/source',
					'/protected',
					'/requirements/views/source',
					'/views/source',
				),
			)
		);
	}

	/** php.net/manual/de/function.mb-detect-encoding.php#85294 */
	public function is_utf8($str) {
		$c=0; $b=0;
		$bits=0;
		$len=strlen($str);
		for($i=0; $i<$len; $i++){
			$c=ord($str[$i]);
			if($c > 128){
				if(($c >= 254)) return false;
				elseif($c >= 252) $bits=6;
				elseif($c >= 248) $bits=5;
				elseif($c >= 240) $bits=4;
				elseif($c >= 224) $bits=3;
				elseif($c >= 192) $bits=2;
				else return false;
				if(($i+$bits) > $len) return false;
				while($bits > 1){
					$i++;
					$b=ord($str[$i]);
					if($b < 128 || $b > 191) return false;
					$bits--;
				}
			}
		}
		return true;
	}
}