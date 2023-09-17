<?php

namespace MongoDB\BSON;

use MongoDB\Driver\Exception\InvalidArgumentException;
use MongoDB\Driver\Exception\UnexpectedValueException;

/**
 * Converts a BSON string to its Canonical Extended JSON representation.
 * The canonical format prefers type fidelity at the expense of concise output and is most suited for producing
 * output that can be converted back to BSON without any loss of type information
 * (e.g. numeric types will remain differentiated).
 * @link https://www.php.net/manual/en/function.mongodb.bson-tocanonicalextendedjson.php
 * @param string $bson BSON value to be converted
 * @return string The converted JSON value
 * @throws UnexpectedValueException
 */
function toCanonicalExtendedJSON($bson) {}

/**
 * Converts a BSON string to its » Relaxed Extended JSON representation.
 * The relaxed format prefers use of JSON type primitives at the expense of type fidelity and is most suited for
 * producing output that can be easily consumed by web APIs and humans.
 * @link https://www.php.net/manual/en/function.mongodb.bson-torelaxedextendedjson.php
 * @param string $bson BSON value to be converted
 * @return string The converted JSON value
 * @throws UnexpectedValueException
 */
function toRelaxedExtendedJSON($bson) {}

/**
 * Returns the BSON representation of a JSON value
 * Converts an extended JSON string to its BSON representation.
 * @link https://php.net/manual/en/function.mongodb.bson-fromjson.php
 * @param string $json JSON value to be converted.
 * @return string The serialized BSON document as a binary string.
 * @throws UnexpectedValueException if the JSON value cannot be converted to BSON (e.g. due to a syntax error).
 */
function fromJSON($json) {}

/**
 * Returns the BSON representation of a PHP value
 * Serializes a PHP array or object (e.g. document) to its BSON representation. The returned binary string will describe a BSON document.
 * @link https://php.net/manual/en/function.mongodb.bson-fromphp.php
 * @param array|object $value PHP value to be serialized.
 * @return string The serialized BSON document as a binary string
 * @throws UnexpectedValueException if the PHP value cannot be converted to BSON.
 */
function fromPHP($value) {}

/**
 * Returns the JSON representation of a BSON value
 * Converts a BSON string to its extended JSON representation.
 * @link https://php.net/manual/en/function.mongodb.bson-tojson.php
 * @param string $bson BSON value to be converted
 * @return string The converted JSON value.
 * @see https://docs.mongodb.org/manual/reference/mongodb-extended-json/
 * @throws UnexpectedValueException if the input did not contain exactly one BSON document
 */
function toJSON($bson) {}

/**
 * Returns the PHP representation of a BSON value
 * Unserializes a BSON document (i.e. binary string) to its PHP representation.
 * The typeMap parameter may be used to control the PHP types used for converting BSON arrays and documents (both root and embedded).
 * @link https://php.net/manual/en/function.mongodb.bson-tophp.php
 * @param string $bson BSON value to be unserialized.
 * @param array $typemap
 * @return object The unserialized PHP value
 * @throws UnexpectedValueException if the input did not contain exactly one BSON document.
 * @throws InvalidArgumentException if a class in the type map cannot be instantiated or does not implement MongoDB\BSON\Unserializable.
 */
function toPHP($bson, array $typemap = []) {}
