<?php
/**
 * CHttpSessionHandler class file.
 *
 * @link https://www.yiiframework.com/
 * @copyright 2008-2026 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

/**
 * CHttpSessionHandler adapts custom session storage on PHP 7.0.
 * PHP generates session IDs using the legacy session.hash_* settings.
 *
 * @package system.web
 * @since 1.1.32
 */
class CHttpSessionHandler extends CHttpSessionHandlerBase implements SessionHandlerInterface
{
}
