<?php
/**
 * This file contains the CTestCase class.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CTestCase is the base class for all test case classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.test
 * @since 1.1
 */
abstract class CTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @internal
     */
    public static function assertObjectHasAttribute(string $attributeName, $object, string $message = ''): void
    {
        if (isset($object->$attributeName)) {
            self::assertTrue(true);
        } else {
            parent::assertObjectHasAttribute($attributeName, $object, $message);
        }
    }

    /**
     * @deprecated this method is removed from PhpUnit 9
     *
     * @return mixed
     */
    protected function readAttribute(object $object, string $propertyName)
    {
        $closure = function (string $propertyName) {
            return $this->{$propertyName};
        };

        return $closure->call($object, $propertyName);
    }

    /**
     * @deprecated this method is removed from PhpUnit 9
     *
     * @param mixed $expected
     */
    protected function assertAttributeEquals($expected, string $propertyName, object $object): void
    {
        $actual = $this->readAttribute($object, $propertyName);
        static::assertEquals($expected, $actual);
    }
}
