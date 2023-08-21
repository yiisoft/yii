<?php

/*
 * This file is part of PhpSpec, A php toolset to drive emergent
 * design by specification.
 *
 * (c) Marcello Duarte <marcello.duarte@gmail.com>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpSpec\ServiceContainer;

use InvalidArgumentException;
use PhpSpec\ServiceContainer;

/**
 * The Service Container is a lightweight container based on Pimple to handle
 * object creation of PhpSpec services.
 */
final class IndexedServiceContainer implements ServiceContainer
{
    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $definitions = [];

    /**
     * @var array
     */
    private $services = [];

    /**
     * @var array
     */
    private $tags = [];

    /**
     * @var array
     */
    private $configurators = [];

    /**
     * Sets a param in the container
     */
    public function setParam(string $id, $value): void
    {
        $this->parameters[$id] = $value;
    }

    /**
     * Gets a param from the container or a default value.
     */
    public function getParam(string $id, $default = null)
    {
        return $this->parameters[$id] ?? $default;
    }

    /**
     * Sets a object to be used as a service
     *
     * @param object $service
     *
     * @throws \InvalidArgumentException if service is not an object
     */
    public function set(string $id, $service, array $tags = []): void
    {
        if (!\is_object($service)) {
            throw new InvalidArgumentException(sprintf(
                'Service should be an object, but %s given.',
                \gettype($service)
            ));
        }

        $this->services[$id] = $service;
        unset($this->definitions[$id]);

        $this->indexTags($id, $tags);
    }

    /**
     * Sets a factory for the service creation. The same service will
     * be returned every time
     */
    public function define(string $id, callable $definition, array $tags = []): void
    {
        $this->definitions[$id] = $definition;
        unset($this->services[$id]);

        $this->indexTags($id, $tags);
    }

    /**
     * Retrieves a service from the container
     *
     *
     * @return object
     *
     * @throws \InvalidArgumentException if service is not defined
     */
    public function get(string $id)
    {
        if (!array_key_exists($id, $this->services)) {
            if (!array_key_exists($id, $this->definitions)) {
                throw new InvalidArgumentException(sprintf('Service "%s" is not defined.', $id));
            }

            $this->services[$id] = \call_user_func($this->definitions[$id], $this);
        }

        return $this->services[$id];
    }

    /**
     * Determines whether a service is defined
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->definitions) || array_key_exists($id, $this->services) ;
    }

    /**
     * Removes a service from the container
     *
     *
     * @throws \InvalidArgumentException if service is not defined
     */
    public function remove(string $id): void
    {
        if (!$this->has($id)) {
            throw new InvalidArgumentException(sprintf('Service "%s" is not defined.', $id));
        }

        unset($this->services[$id], $this->definitions[$id]);
    }

    /**
     * Adds a service or service definition to the index
     */
    private function indexTags(string $id, array $tags): void
    {
        foreach ($tags as $tag) {
            $this->tags[$tag][] = $id;
        }
    }

    /**
     * Finds all services tagged with a particular string
     */
    public function getByTag(string $tag): array
    {
        return array_map([$this, 'get'], $this->tags[$tag] ?? []);
    }

    /**
     * Adds a configurator, that can configure many services in one value
     *
     * @internal
     *
     * @throws \InvalidArgumentException if configurator is not a value
     */
    public function addConfigurator(callable $configurator): void
    {
        $this->configurators[] = $configurator;
    }

    /**
     * Loop through all configurators and invoke them
     *
     * @internal
     */
    public function configure(): void
    {
        foreach ($this->configurators as $configurator) {
            \call_user_func($configurator, $this);
        }
    }
}
