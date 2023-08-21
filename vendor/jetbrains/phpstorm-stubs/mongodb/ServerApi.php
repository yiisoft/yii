<?php

namespace MongoDB\Driver;

final class ServerApi implements \MongoDB\BSON\Serializable, \Serializable
{
    public const V1 = 1;

    final public function __construct(string $version, ?bool $strict = false, ?bool $deprecationErrors = false) {}

    public static function __set_state(array $properties) {}

    final public function unserialize(string $data): void {}

    final public function serialize(): string {}

    final public function bsonSerialize(): array|object {}
}
