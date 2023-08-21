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

namespace PhpSpec\Runner;

use PhpSpec\Exception\Wrapper\CollaboratorException;
use PhpSpec\Formatter\Presenter\Presenter;
use PhpSpec\Wrapper\Collaborator;
use ReflectionFunctionAbstract;

class CollaboratorManager
{
    /**
     * @var Presenter
     */
    private $presenter;
    /**
     * @var Collaborator[]
     */
    private $collaborators = array();

    
    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    /**
     * @param object $collaborator
     */
    public function set(string $name, $collaborator): void
    {
        $this->collaborators[$name] = $collaborator;
    }

    
    public function has(string $name): bool
    {
        return isset($this->collaborators[$name]);
    }

    /**
     * @return object
     *
     * @throws \PhpSpec\Exception\Wrapper\CollaboratorException
     */
    public function get(string $name)
    {
        if (!$this->has($name)) {
            throw new CollaboratorException(
                sprintf('Collaborator %s not found.', $this->presenter->presentString($name))
            );
        }

        return $this->collaborators[$name];
    }

    
    public function getArgumentsFor(ReflectionFunctionAbstract $function): array
    {
        $parameters = array();
        foreach ($function->getParameters() as $parameter) {
            if ($this->has($parameter->getName())) {
                $parameters[] = $this->get($parameter->getName());
            } else {
                $parameters[] = null;
            }
        }

        return $parameters;
    }
}
