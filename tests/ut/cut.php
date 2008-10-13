<?php
/**
 * This script is needed by all unit tests.
 */
require_once(dirname(__FILE__).'/yii.php');
require_once('PHPUnit/Framework.php');
require_once('PHPUnit/Framework/TestCase.php');
require_once('PHPUnit/Framework/IncompleteTestError.php');
require_once('PHPUnit/Framework/TestSuite.php');
require_once('PHPUnit/TextUI/TestRunner.php');
require_once('PHPUnit/Util/Filter.php');

define('YII_UT_PATH',dirname(__FILE__));

class CTestCase extends PHPUnit_Framework_TestCase
{
}

class CTestSuite extends PHPUnit_Framework_TestSuite
{
}

class CTestRunner extends PHPUnit_TextUI_TestRunner
{
}

class YiiUnitTester
{
	private $_tests=array();
	private $_name;

	public function __construct($basePath, $name)
	{
		if(($path=realpath($basePath))===false || !is_dir($path))
			die("Test directory $basepath does not exist.");
		$this->_name=$name;
		$this->collectTests($path);
	}

	public function run($params=array())
	{
		$codeCoverage=false;
		if(count($params)===3 && $params[1]==='-c')
		{
			$key=$params[2];
			$codeCoverage=true;
		}
		else if(count($params)===2)
			$key=$params[1];
		else
		{
			echo <<<EOD

Usage:
- Running a single test script named "CMapTest.php:"
  php {$params[0]} [-c] CMapTest   (or CMap)

- Running all tests under some directory named "collections":
  php {$params[0]} [-c] collections/

In both cases, option -c specifies running code coverage report.
The result will be saved under "./reports" directory.

EOD;
			die();
		}

		$reportPath=dirname(__FILE__).DIRECTORY_SEPARATOR.'reports';
		if($codeCoverage)
		{
			 if(!extension_loaded('xdebug'))
			 	 die('xdebug PHP extension is required for running code coverage report.');
			 if(!is_dir($reportPath))
			 	 die('The report directory '.$reportPath.' does not exist.');
		}

		$paths=$this->findTests($key);
		echo "Total ".count($paths)." test scripts found.\n";

		$suite=new CTestSuite($this->_name);
		foreach($paths as $path)
		{
			include_once($path);
			$suite->addTestSuite(basename($path,'.php'));
		}

		$args=array('verbose'=>true);
		if($codeCoverage)
			$args['reportDirectory']=$reportPath;

		CTestRunner::run($suite,$args);

		if($codeCoverage)
		{
			echo "\nYour code coverage report is located at:\n";
			echo $reportPath.DIRECTORY_SEPARATOR."index.html\n";
		}
	}

	public function findTests($key)
	{
		$key=trim($key);
		if($key==='*')
			return $this->_tests;
		$paths=array();
		if(isset($key[0]) && ($dirName=rtrim($key,'/'))!==$key)
		{
			foreach($this->_tests as $path)
			{
				if($dirName===basename(dirname($path)))
					$paths[]=$path;
			}
		}
		else
		{
			foreach($this->_tests as $path)
			{
				$fileName=basename($path);
				if(($key.'.php')===$fileName || ($key.'Test.php')===$fileName)
					$paths[]=$path;
			}
		}
		return $paths;
	}

	protected function collectTests($basePath)
	{
		$folder=@opendir($basePath);
		while($entry=@readdir($folder))
		{
			if($entry[0]==='.')  // skip all files/directories whose name starts with a dot.
				continue;
			$path=$basePath.DIRECTORY_SEPARATOR.$entry;
			if($this->isTestFile($entry) && is_file($path))
				$this->_tests[]=$path;
			else if(is_dir($path))
				$this->collectTests($path);
		}
		@closedir($folder);
	}

	protected function isTestFile($entry)
	{
		return strrpos($entry,'Test.php')===strlen($entry)-8;
	}
}
