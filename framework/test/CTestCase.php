<?php
/**
 * This file contains the CTestCase class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

require_once('PHPUnit/Util/Filesystem.php'); // workaround for PHPUnit <= 3.6.11
require_once('PHPUnit/Autoload.php');
spl_autoload_unregister('phpunit_autoload');
Yii::registerAutoloader('phpunit_autoload');

/**
 * CTestCase is the base class for all test case classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.test
 * @since 1.1
 */
abstract class CTestCase extends PHPUnit_Framework_TestCase
{
}
