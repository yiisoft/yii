<?php
/**
 * YiiPearTask class file.
 *
 * @author Wei Zhuo <weizho@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require_once 'phing/Task.php';
require_once 'PEAR/PackageFileManager2.php';

/**
 * YiiPearTask create a PEAR package for the yii framework.
 *
 * @author Wei Zhuo <weizho@gmail.com>
 * @package build.tasks
 * @since 1.0
 */
class YiiPearTask extends Task
{
	private $pkgdir;
	private $channel;
	private $version;
	private $state;
	private $category;
	private $package;
	private $summary;
	private $pkgdescription;
	private $notes;
	private $license;

	function setPkgdir($value)
	{
		$this->pkgdir=$value;
	}

	function setChannel($value)
	{
		$this->channel=$value;
	}

	function setVersion($value)
	{
		$this->version=$value;
	}

	function setState($value)
	{
		$this->state=$value;
	}

	function setCategory($value)
	{
		$this->category=$value;
	}

	function setPackage($value)
	{
		$this->package=$value;
	}

	function setSummary($value)
	{
		$this->summary=$value;
	}

	function setPkgdescription($value)
	{
		$this->pkgdescription=$value;
	}

	function setNotes($value)
	{
		$this->notes=$value;
	}

	function setLicense($value)
	{
		$this->license=$value;
	}

	/**
	 * Main entrypoint of the task
	 */
	function main()
	{
		$pkg = new PEAR_PackageFileManager2();

		$e = $pkg->setOptions(array
		(
			'baseinstalldir'    => 'yii',
			'packagedirectory'  => $this->pkgdir,
			'filelistgenerator' => 'file',
			'simpleoutput'      => true,
			'ignore'            => array(),
			'roles' => array('*' => 'php'),
			)
		);

		// PEAR error checking
		if (PEAR::isError($e))
			die($e->getMessage());
		$pkg->setPackage($this->package);
		$pkg->setSummary($this->summary);
		$pkg->setDescription($this->pkgdescription);
		$pkg->setChannel($this->channel);

		$pkg->setReleaseStability($this->state);
		$pkg->setAPIStability($this->state);
		$pkg->setReleaseVersion($this->version);
		$pkg->setAPIVersion($this->version);

		$pkg->setLicense($this->license);
		$pkg->setNotes($this->notes);
		$pkg->setPackageType('php');
		$pkg->setPhpDep('5.1.0');
		$pkg->setPearinstallerDep('1.4.2');

		$pkg->addRelease();
		$pkg->addMaintainer('lead','qxue','Qiang Xue','qiang.xue@gmail.com');

		$test = $pkg->generateContents();

		$e = $pkg->writePackageFile();

		if (PEAR::isError($e))
			echo $e->getMessage();
	}
}