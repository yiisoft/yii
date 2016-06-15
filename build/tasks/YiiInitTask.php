<?php
/**
 * YeeInitTask class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yeeframework.com/
 * @copyright 2008-2013 Yee Software LLC
 * @license http://www.yeeframework.com/license/
 */

require_once 'phing/Task.php';
require_once 'phing/tasks/system/PropertyTask.php';

/**
 * YeeInitTask initializes a few property values.
 *
 * The following properties are created:
 * <pre>
 * <li>yee.version: the version number of Yee</li>
 * <li>yee.revision: the SVN revision number of Yee</li>
 * <li>yee.winbuild: whether this build is on Windows (true) or not (false)</li>
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package build.tasks
 * @since 1.0
 */
class YeeInitTask extends PropertyTask
{
	/**
	 * Execute lint check against PhingFile or a FileSet
	 */
	public function main()
	{
		$this->addProperty('yee.version',$this->getYeeVersion());
		$this->addProperty('yee.revision',$this->getYeeRevision());
		$this->addProperty('yee.winbuild', substr(PHP_OS, 0, 3) == 'WIN' ? 'true' : 'false');
		$this->addProperty('yee.release',$this->getYeeRelease());
		$this->addProperty('yee.date',date('M j, Y'));
	}

	/**
	 * @return string Yee version
	 */
	private function getYeeVersion()
	{
		$coreFile=dirname(__FILE__).'/../../framework/YeeBase.php';
		if(is_file($coreFile))
		{
			$contents=file_get_contents($coreFile);
			$matches=array();
			if(preg_match('/public static function getVersion.*?return \'(.*?)\'/ms',$contents,$matches)>0)
				return $matches[1];
		}
		return 'unknown';
	}

	private function getYeeRelease()
	{
		$changelog=dirname(__FILE__).'/../../CHANGELOG';
		if(preg_match('/Version ([\d.a-z]+) .*\d{4}\s/', file_get_contents($changelog), $matches)>0)
			return $matches[1];
		return '0.0.0';
	}

	/**
	 * @return string Yee GIT revision
	 */
	private function getYeeRevision()
	{
		$gitFile=dirname(__FILE__).'/../../.git/HEAD';
		if(is_file($gitFile))
		{
			$contents=file_get_contents($gitFile);
			return substr($contents, 0, 6);
		}
		else
			return 'unknown';
	}
}
