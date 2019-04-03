<?php

/**
 * CEnumerable is the base class for all enumerable types.
 *
 * To define an enumerable type, extend CEnumberable and define string constants.
 * Each constant represents an enumerable value.
 * The constant name must be the same as the constant value.
 * For example,
 * <pre>
 * class TextAlign extends CEnumerable
 * {
 *     const Left='Left';
 *     const Right='Right';
 * }
 * </pre>
 * Then, one can use the enumerable values such as TextAlign::Left and
 * TextAlign::Right.
 *
 * @author  Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since   1.0
 */
class CEnumerable
{
}