<?php
/**
 * CGettextFile class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CGettextFile is the base class for representing a Gettext message file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.i18n.gettext
 * @since 1.0
 */
abstract class CGettextFile extends CComponent
{
	/**
	 * Loads messages from a file.
	 * @param string file path
	 * @return array message translations (source message => translated message)
	 */
	abstract public function load($file);
	/**
	 * Saves messages to a file.
	 * @param string file path
	 * @param array message translations (source message => translated message)
	 */
	abstract public function save($file,$messages);
}
