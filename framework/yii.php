<?php
/**
 * Yee bootstrap file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yeeframework.com/
 * @copyright 2008-2013 Yee Software LLC
 * @license http://www.yeeframework.com/license/
 * @package system
 * @since 1.0
 */

if(!class_exists('YeeBase', false))
	require(dirname(__FILE__).'/YeeBase.php');

/**
 * Yee is a helper class serving common framework functionalities.
 *
 * It encapsulates {@link YeeBase} which provides the actual implementation.
 * By writing your own Yee class, you can customize some functionalities of YeeBase.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system
 * @since 1.0
 */
class Yee extends YeeBase
{
}
