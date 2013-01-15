<?php
/**
 * YiiInitTask class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require_once 'phing/Task.php';
require_once 'phing/tasks/system/PropertyTask.php';

/**
 * YiiInitTask initializes a few property values.
 *
 * The following properties are created:
 * <pre>
 * <li>yii.version: the version number of Yii</li>
 * <li>yii.revision: the SVN revision number of Yii</li>
 * <li>yii.winbuild: whether this build is on Windows (true) or not (false)</li>
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package build.tasks
 * @since 1.0
 */
class YiiInitTask extends PropertyTask
{
	/**
	 * Execute lint check against PhingFile or a FileSet
	 */
	public function main()
	{
		$this->addProperty('yii.version',$this->getYiiVersion());
		$this->addProperty('yii.revision',$this->getYiiRevision());
		$this->addProperty('yii.winbuild', substr(PHP_OS, 0, 3) == 'WIN' ? 'true' : 'false');
		$this->addProperty('yii.release',$this->getYiiRelease());
		$this->addProperty('yii.date',date('M j, Y'));
	}

	/**
	 * @return string Yii version
	 */
	private function getYiiVersion()
	{
		$coreFile=dirname(__FILE__).'/../../framework/YiiBase.php';
		if(is_file($coreFile))
		{
			$contents=file_get_contents($coreFile);
			$matches=array();
			if(preg_match('/public static function getVersion.*?return \'(.*?)\'/ms',$contents,$matches)>0)
				return $matches[1];
		}
		return 'unknown';
	}

	private function getYiiRelease()
	{
		$changelog=dirname(__FILE__).'/../../CHANGELOG';
		if(preg_match('/Version ([\d.a-z]+) .*\d{4}\s/', file_get_contents($changelog), $matches)>0)
			return $matches[1];
		return '0.0.0';
	}

	/**
	 * @return string Yii GIT revision
	 */
	private function getYiiRevision()
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
