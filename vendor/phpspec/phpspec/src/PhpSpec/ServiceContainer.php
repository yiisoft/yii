<?php

namespace PhpSpec;

/**
 * The Service Container is a lightweight container based on Pimple to handle
 * object creation of PhpSpec services.
 */
interface ServiceContainer
{
    /**
     * Sets a param in the container
     */
    public function setParam(string $id, $value): void;

    /**
     * Gets a param from the container or a default value.
     */
    public function getParam(string $id, $default = null);

    /**
     * Sets a object to be used as a service
     *
     * @param object $service
     *
     * @throws \InvalidArgumentException if service is not an object
     */
    public function set(string $id, $service, array $tags = []): void;

    /**
     * Sets a factory for the service creation. The same service will
     * be returned every time
     *
     *
     * @throws \InvalidArgumentException if service is not a callable
     */
    public function define(string $id, callable $definition, array $tags = []): void;

    /**
     * Retrieves a service from the container
     *
     *
     * @return object
     *
     * @throws \InvalidArgumentException if service is not defined
     */
    public function get(string $id);

    /**
     * Determines whether a service is defined
     */
    public function has(string $id): bool;

    /**
     * Removes a service from the container
     *
     *
     * @throws \InvalidArgumentException if service is not defined
     */
    public function remove(string $id): void;

    /**
     * Finds all services tagged with a particular string
     */
    public function getByTag(string $tag): array;
}
