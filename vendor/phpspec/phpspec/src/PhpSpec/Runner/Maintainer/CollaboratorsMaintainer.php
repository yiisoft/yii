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

namespace PhpSpec\Runner\Maintainer;

use PhpSpec\CodeAnalysis\DisallowedNonObjectTypehintException;
use PhpSpec\CodeAnalysis\DisallowedUnionTypehintException;
use PhpSpec\Exception\Fracture\CollaboratorNotFoundException;
use PhpSpec\Exception\Wrapper\InvalidCollaboratorTypeException;
use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Loader\Transformer\TypeHintIndex;
use PhpSpec\Specification;
use PhpSpec\Runner\MatcherManager;
use PhpSpec\Runner\CollaboratorManager;
use PhpSpec\Wrapper\Collaborator;
use PhpSpec\Wrapper\Unwrapper;
use Prophecy\Exception\Doubler\ClassNotFoundException;
use Prophecy\Prophet;
use ReflectionNamedType;

final class CollaboratorsMaintainer implements Maintainer
{
    /**
     * @var string
     */
    private static $docex = '#@param *([^ ]*) *\$([^ ]*)#';
    /**
     * @var Unwrapper
     */
    private $unwrapper;
    /**
     * @var Prophet
     */
    private $prophet;

    /**
     * @var TypeHintIndex
     */
    private $typeHintIndex;

    
    public function __construct(Unwrapper $unwrapper, TypeHintIndex $typeHintIndex)
    {
        $this->unwrapper = $unwrapper;
        $this->typeHintIndex = $typeHintIndex;
    }

    
    public function supports(ExampleNode $example): bool
    {
        return true;
    }

    
    public function prepare(
        ExampleNode $example,
        Specification $context,
        MatcherManager $matchers,
        CollaboratorManager $collaborators
    ): void {
        $this->prophet = new Prophet(null, $this->unwrapper, null);

        $classRefl = $example->getSpecification()->getClassReflection();

        if ($classRefl->hasMethod('let')) {
            $this->generateCollaborators($collaborators, $classRefl->getMethod('let'), $classRefl);
        }

        $this->generateCollaborators($collaborators, $example->getFunctionReflection(), $classRefl);
    }

    
    public function teardown(
        ExampleNode $example,
        Specification $context,
        MatcherManager $matchers,
        CollaboratorManager $collaborators
    ): void {
        $this->prophet->checkPredictions();
    }

    
    public function getPriority(): int
    {
        return 50;
    }

    
    private function generateCollaborators(CollaboratorManager $collaborators, \ReflectionFunctionAbstract $function, \ReflectionClass $classRefl): void
    {
        foreach ($function->getParameters() as $parameter) {

            $collaborator = $this->getOrCreateCollaborator($collaborators, $parameter->getName());
            try {
                if ($this->isUnsupportedTypeHinting($parameter)) {
                    throw new InvalidCollaboratorTypeException($parameter, $function);
                }
                if ($indexedClass = $this->getParameterTypeFromIndex($classRefl, $parameter)) {
                    $collaborator->beADoubleOf($indexedClass);
                }
            }
            catch (ClassNotFoundException $e) {
                $this->throwCollaboratorNotFound($e, null, $e->getClassname());
            }
            catch (DisallowedUnionTypehintException $e) {
                throw new InvalidCollaboratorTypeException($parameter, $function, $e->getMessage(), 'Use a specific type');
            }
            catch (DisallowedNonObjectTypehintException $e) {
                throw new InvalidCollaboratorTypeException($parameter, $function);
            }
        }
    }

    private function isUnsupportedTypeHinting(\ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();

        if (null === $type) {
            return false;
        }

        return !$type instanceof ReflectionNamedType || in_array($type->getName(), ['array', 'callable'], true);
    }

    
    private function getOrCreateCollaborator(CollaboratorManager $collaborators, string $name): Collaborator
    {
        if (!$collaborators->has($name)) {
            $collaborator = new Collaborator($this->prophet->prophesize());
            $collaborators->set($name, $collaborator);
        }

        return $collaborators->get($name);
    }

    /**
     * @param string $className
     *
     * @throws CollaboratorNotFoundException
     */
    private function throwCollaboratorNotFound(\Exception $e, \ReflectionParameter $parameter = null, string $className = null): void
    {
        throw new CollaboratorNotFoundException(
            sprintf('Collaborator does not exist '),
            0, $e,
            $parameter,
            $className
        );
    }

    
    private function getParameterTypeFromIndex(\ReflectionClass $classRefl, \ReflectionParameter $parameter): ?string
    {
        return $this->typeHintIndex->lookup(
            $classRefl->getName(),
            $parameter->getDeclaringFunction()->getName(),
            '$' . $parameter->getName()
        );
    }
}
