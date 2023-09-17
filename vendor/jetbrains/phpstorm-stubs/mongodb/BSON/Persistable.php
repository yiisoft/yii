<?php

namespace MongoDB\BSON;

/**
 * Classes may implement this interface to take advantage of automatic ODM (object document mapping) behavior in the driver.
 * @link https://php.net/manual/en/class.mongodb-bson-persistable.php
 */
interface Persistable extends Unserializable, Serializable {}
