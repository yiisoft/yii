<?php

namespace MongoDB\BSON;

/**
 * Interface JavascriptInterface
 *
 * @link https://secure.php.net/manual/en/class.mongodb-bson-javascriptinterface.php
 * @since 1.3.0
 */
interface JavascriptInterface
{
    /**
     * Returns the JavascriptInterface's code
     * @return string
     * @link https://secure.php.net/manual/en/mongodb-bson-javascriptinterface.getcode.php
     * @since 1.3.0
     */
    public function getCode();

    /**
     * Returns the JavascriptInterface's scope document
     * @return object|null
     * @link https://secure.php.net/manual/en/mongodb-bson-javascriptinterface.getscope.php
     * @since 1.3.0
     */
    public function getScope();

    /**
     * Returns the JavascriptInterface's code
     * @return string
     * @link https://secure.php.net/manual/en/mongodb-bson-javascriptinterface.tostring.php
     * @since 1.3.0
     */
    public function __toString();
}
