<?php

namespace JetBrains\PhpStorm;

use Attribute;

/**
 * The attribute specifies possible object field names and their types.
 *
 * If applied, an IDE will suggest the specified field names and infer the specified types.
 *
 * Example:
 * <pre>#[ObjectShape(["age" => "int", "name" => "string"])]</pre>
 *
 * This usage applied on an element effectively means that the object has 2 fields, the names are <code>"age"</code> and <code>"name"</code>, and the corresponding types are <code>"int"</code> and <code>"string"</code>.
 */
#[Attribute(Attribute::TARGET_FUNCTION|Attribute::TARGET_METHOD|Attribute::TARGET_PARAMETER|Attribute::TARGET_PROPERTY)]
class ObjectShape
{
    public function __construct(array $shape) {}
}
