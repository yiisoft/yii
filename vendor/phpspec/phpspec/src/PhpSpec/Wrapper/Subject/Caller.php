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

namespace PhpSpec\Wrapper\Subject;

use PhpSpec\CodeAnalysis\AccessInspector;
use PhpSpec\Exception\ExceptionFactory;
use PhpSpec\Exception\Fracture\NamedConstructorNotFoundException;
use PhpSpec\Factory\ObjectFactory;
use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Util\DispatchTrait;
use PhpSpec\Wrapper\Subject;
use PhpSpec\Wrapper\Wrapper;
use PhpSpec\Wrapper\Unwrapper;
use PhpSpec\Event\MethodCallEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as Dispatcher;
use ReflectionClass;
use ReflectionException;

class Caller
{
    use DispatchTrait;

    /**
     * @var WrappedObject
     */
    private $wrappedObject;
    /**
     * @var ExampleNode
     */
    private $example;
    /**
     * @var Dispatcher
     */
    private $dispatcher;
    /**
     * @var Wrapper
     */
    private $wrapper;
    /**
     * @var ExceptionFactory
     */
    private $exceptionFactory;
    /**
     * @var AccessInspector
     */
    private $accessInspector;


    public function __construct(
        WrappedObject $wrappedObject,
        ExampleNode $example,
        Dispatcher $dispatcher,
        ExceptionFactory $exceptions,
        Wrapper $wrapper,
        AccessInspector $accessInspector
    ) {
        $this->wrappedObject    = $wrappedObject;
        $this->example          = $example;
        $this->dispatcher       = $dispatcher;
        $this->wrapper          = $wrapper;
        $this->exceptionFactory = $exceptions;
        $this->accessInspector  = $accessInspector;
    }

    /**
     * @throws \PhpSpec\Exception\Fracture\MethodNotFoundException
     * @throws \PhpSpec\Exception\Fracture\MethodNotVisibleException
     * @throws \PhpSpec\Exception\Wrapper\SubjectException
     */
    public function call(string $method, array $arguments = array()): Subject
    {
        if (!is_object($this->getWrappedObject())) {
            throw $this->callingMethodOnNonObject($method);
        }

        $subject   = $this->wrappedObject->getInstance();
        $unwrapper = new Unwrapper();
        $arguments = $unwrapper->unwrapAll($arguments);

        if ($this->isObjectMethodCallable($method)) {
            /** @var object $subject */
            return $this->invokeAndWrapMethodResult($subject, $method, $arguments);
        }

        throw $this->methodNotFound($method, $arguments);
    }

    /**
     * @param mixed $value
     * @throws \PhpSpec\Exception\Wrapper\SubjectException
     * @throws \PhpSpec\Exception\Fracture\PropertyNotFoundException
     */
    public function set(string $property, $value = null): void
    {
        if (null === $this->getWrappedObject()) {
            throw $this->settingPropertyOnNonObject($property);
        }

        $unwrapper = new Unwrapper();
        $value = $unwrapper->unwrapOne($value);

        if ($this->isObjectPropertyWritable($property)) {
            $this->getWrappedObject()->$property = $value;

            return;
        }

        throw $this->propertyNotFound($property);
    }

    /**
     * @return string|Subject
     *
     * @throws \PhpSpec\Exception\Fracture\PropertyNotFoundException
     * @throws \PhpSpec\Exception\Wrapper\SubjectException
     */
    public function get(string $property)
    {
        if ($this->lookingForConstants($property) && $this->constantDefined($property)) {
            $className = $this->wrappedObject->getClassName();
            /** @var string $className */
            return constant($className.'::'.$property);
        }

        if (null === $this->getWrappedObject()) {
            throw $this->accessingPropertyOnNonObject($property);
        }

        if ($this->isObjectPropertyReadable($property)) {
            return $this->wrap($this->getWrappedObject()->$property);
        }

        throw $this->propertyNotFound($property);
    }

    /**
     * @return ?object
     *
     * @throws \PhpSpec\Exception\Fracture\ClassNotFoundException
     */
    public function getWrappedObject()
    {
        if ($this->wrappedObject->isInstantiated()) {
            return $this->wrappedObject->getInstance();
        }

        $className = $this->wrappedObject->getClassName();

        if (!\is_string($className)) {
            return $this->wrappedObject->getInstance();
        }

        if (!class_exists($className)) {
            throw $this->classNotFound();
        }

        if (\is_object($this->wrappedObject->getInstance())) {
            $this->wrappedObject->setInstantiated(true);
            $instance = $this->wrappedObject->getInstance();
        } else {
            $instance = $this->instantiateWrappedObject();
            $this->wrappedObject->setInstance($instance);
            $this->wrappedObject->setInstantiated(true);
        }

        return $instance;
    }


    private function isObjectPropertyReadable(string $property): bool
    {
        $subject = $this->getWrappedObject();

        return \is_object($subject) && $this->accessInspector->isPropertyReadable($subject, $property);
    }


    private function isObjectPropertyWritable(string $property): bool
    {
        $subject = $this->getWrappedObject();

        return \is_object($subject) && $this->accessInspector->isPropertyWritable($subject, $property);
    }


    private function isObjectMethodCallable(string $method): bool
    {
        $subject = $this->getWrappedObject();

        if (!is_object($subject)) {
            return false;
        }

        return $this->accessInspector->isMethodCallable($subject, $method);
    }

    /**
     * @return object
     */
    private function instantiateWrappedObject()
    {
        if ($this->wrappedObject->getFactoryMethod()) {
            return $this->newInstanceWithFactoryMethod();
        }
        /** @psalm-suppress PossiblyNullArgument, ArgumentTypeCoercion */
        $reflection = new ReflectionClass($this->wrappedObject->getClassName());

        if (\count($this->wrappedObject->getArguments())) {
            return $this->newInstanceWithArguments($reflection);
        }
        /** @psalm-suppress InvalidMethodCall */
        return $reflection->newInstance();
    }

    /**
     * @param object $subject
     * @param string $method
     */
    private function invokeAndWrapMethodResult($subject, $method, array $arguments = array()): Subject
    {
        $this->dispatch(
            $this->dispatcher,
            new MethodCallEvent($this->example, $subject, $method, $arguments),
            'beforeMethodCall'
        );

        $returnValue = \call_user_func_array(array($subject, $method), $arguments);

        $this->dispatch(
            $this->dispatcher,
            new MethodCallEvent($this->example, $subject, $method, $arguments),
            'afterMethodCall'
        );

        return $this->wrap($returnValue);
    }

    /**
     * @param mixed $value
     */
    private function wrap($value): Subject
    {
        return $this->wrapper->wrap($value);
    }

    /**
     * @return object
     *
     * @throws \PhpSpec\Exception\Fracture\MethodNotFoundException
     * @throws \PhpSpec\Exception\Fracture\MethodNotVisibleException
     * @throws \Exception
     * @throws \ReflectionException
     */
    private function newInstanceWithArguments(ReflectionClass $reflection)
    {
        try {
            return $reflection->newInstanceArgs($this->wrappedObject->getArguments());
        } catch (ReflectionException $e) {
            if ($this->detectMissingConstructorMessage($e)) {
                throw $this->methodNotFound(
                    '__construct',
                    $this->wrappedObject->getArguments()
                );
            }
            throw $e;
        }
    }

    /**
     * @throws \PhpSpec\Exception\Fracture\MethodNotFoundException
     * @throws \PhpSpec\Exception\Fracture\FactoryDoesNotReturnObjectException
     * @return object
     */
    private function newInstanceWithFactoryMethod()
    {
        $method = $this->wrappedObject->getFactoryMethod();
        $className = $this->wrappedObject->getClassName();
        /** @var string $className */
        if (!\is_array($method)) {
            /** @psalm-suppress ArgumentTypeCoercion */
            if (\is_string($method) && !method_exists($className, $method)) {
                throw $this->namedConstructorNotFound(
                    $method,
                    $this->wrappedObject->getArguments()
                );
            }
        }
        /** @var callable $method */
        return (new ObjectFactory())->instantiateFromCallable(
            $method,
            $this->wrappedObject->getArguments()
        );
    }


    private function detectMissingConstructorMessage(ReflectionException $exception): bool
    {
        return strpos(
            $exception->getMessage(),
            'does not have a constructor'
        ) !== 0;
    }


    private function classNotFound(): \PhpSpec\Exception\Fracture\ClassNotFoundException
    {
        $className = $this->wrappedObject->getClassName();
        /** @var string $className */
        return $this->exceptionFactory->classNotFound($className);
    }

    private function namedConstructorNotFound(string $method, array $arguments = array()) : NamedConstructorNotFoundException
    {
        $className = $this->wrappedObject->getClassName();
        /** @var string $className */
        return $this->exceptionFactory->namedConstructorNotFound($className, $method, $arguments);
    }

    /**
     * @param mixed $method
     *
     * @return \PhpSpec\Exception\Fracture\MethodNotFoundException|\PhpSpec\Exception\Fracture\MethodNotVisibleException
     */
    private function methodNotFound($method, array $arguments = array())
    {
        $className = $this->wrappedObject->getClassName();
        /** @var string $className */
        if (!method_exists($className, $method)) {
            return $this->exceptionFactory->methodNotFound($className, $method, $arguments);
        }

        return $this->exceptionFactory->methodNotVisible($className, $method, $arguments);
    }


    private function propertyNotFound(string $property): \PhpSpec\Exception\Fracture\PropertyNotFoundException
    {
        return $this->exceptionFactory->propertyNotFound($this->getWrappedObject(), $property);
    }


    private function callingMethodOnNonObject(string $method): \PhpSpec\Exception\Wrapper\SubjectException
    {
        return $this->exceptionFactory->callingMethodOnNonObject($method);
    }


    private function settingPropertyOnNonObject(string $property): \PhpSpec\Exception\Wrapper\SubjectException
    {
        return $this->exceptionFactory->settingPropertyOnNonObject($property);
    }


    private function accessingPropertyOnNonObject(string $property): \PhpSpec\Exception\Wrapper\SubjectException
    {
        return $this->exceptionFactory->gettingPropertyOnNonObject($property);
    }


    private function lookingForConstants(string $property): bool
    {
        return null !== $this->wrappedObject->getClassName() &&
            $property === strtoupper($property);
    }


    public function constantDefined(string $property): bool
    {
        $className = $this->wrappedObject->getClassName();
        /** @var string $className */
        return \defined($className.'::'.$property);
    }
}
