<?php

namespace MongoDB\BSON;

/**
 * @link https://www.php.net/manual/en/class.mongodb-bson-regexinterface.php
 * This interface is implemented by MongoDB\BSON\Regex but may also be used for type-hinting and userland classes.
 */
interface RegexInterface
{
    /**
     * @link https://www.php.net/manual/en/mongodb-bson-regexinterface.getflags.php
     * @return string Returns the RegexInterface's flags.
     */
    public function getFlags();

    /**
     * @link https://www.php.net/manual/en/mongodb-bson-regexinterface.getpattern.php
     * @return string Returns the RegexInterface's pattern.
     */
    public function getPattern();

    /**
     * Returns the string representation of this RegexInterface
     * @link https://www.php.net/manual/en/mongodb-bson-regexinterface.tostring.php
     * @return string
     */
    public function __toString();
}
