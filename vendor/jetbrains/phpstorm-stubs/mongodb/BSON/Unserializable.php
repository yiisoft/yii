<?php

namespace MongoDB\BSON;

/**
 * Classes that implement this interface may be specified in a type map for unserializing BSON arrays and documents (both root and embedded).
 * @link https://php.net/manual/en/class.mongodb-bson-unserializable.php
 */
interface Unserializable extends Type
{
    /**
     * Constructs the object from a BSON array or document
     * Called during unserialization of the object from BSON.
     * The properties of the BSON array or document will be passed to the method as an array.
     * @link https://php.net/manual/en/mongodb-bson-unserializable.bsonunserialize.php
     * @param array $data Properties within the BSON array or document.
     */
    public function bsonUnserialize(array $data);
}
