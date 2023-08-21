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

namespace PhpSpec;

use PhpSpec\Matcher\MatchersProvider;
use PhpSpec\Wrapper\ObjectWrapper;
use PhpSpec\Wrapper\SubjectContainer;
use PhpSpec\Wrapper\Subject;
use ArrayAccess;

/**
 * The object behaviour is the default base class for specification.
 *
 * Most specs will extend this class directly.
 *
 * Its responsibility is to proxy method calls to PhpSpec caller which will
 * wrap the results into PhpSpec subjects. This results will then be able to
 * be matched against expectations.
 *
 * @method void beConstructedWith(...$arguments)
 * @method void beConstructedThrough($factoryMethod, array $constructorArguments = array())
 * @method void beAnInstanceOf($class)
 * @method void shouldHaveType($type)
 * @method void shouldNotHaveType($type)
 * @method void shouldBeAnInstanceOf($type)
 * @method void shouldNotBeAnInstanceOf($type)
 * @method void shouldImplement($interface)
 * @method void shouldNotImplement($interface)
 * @method void shouldIterateAs($iterable)
 * @method void shouldYield($iterable)
 * @method void shouldNotIterateAs($iterable)
 * @method void shouldNotYield($iterable)
 * @method void shouldIterateLike($iterable)
 * @method void shouldYieldLike($iterable)
 * @method void shouldNotIterateLike($iterable)
 * @method void shouldNotYieldLike($iterable)
 * @method void shouldStartIteratingAs($iterable)
 * @method void shouldStartYielding($iterable)
 * @method void shouldNotStartIteratingAs($iterable)
 * @method void shouldNotStartYielding($iterable)
 * @method Subject\Expectation\DuringCall shouldThrow($exception = null)
 * @method Subject\Expectation\DuringCall shouldNotThrow($exception = null)
 * @method Subject\Expectation\DuringCall shouldTrigger($level = null, $message = null)
 * @method void shouldHaveCount($count)
 * @method void shouldNotHaveCount($count)
 * @method void shouldContain($element)
 * @method void shouldNotContain($element)
 * @method void shouldHaveKeyWithValue($key, $value)
 * @method void shouldNotHaveKeyWithValue($key, $value)
 * @method void shouldHaveKey($key)
 * @method void shouldNotHaveKey($key)
 *
 * @template TKey
 * @template TValue
 * @template-implements ArrayAccess<TKey,TValue>
 */
abstract class ObjectBehavior implements
    ArrayAccess,
    MatchersProvider,
    SubjectContainer,
    ObjectWrapper,
    Specification
{
    /**
     * @var Subject
     */
    protected $object;

    /**
     * Override this method to provide your own inline matchers
     *
     * @link http://phpspec.net/cookbook/matchers.html Matchers cookbook
     *
     * @return array a list of inline matchers
     */
    public function getMatchers(): array
    {
        return array();
    }

    /**
     * Used by { @link PhpSpec\Runner\Maintainer\SubjectMaintainer::prepare() }
     * to prepare the subject with all the needed collaborators for proxying
     * object behaviour
     */
    public function setSpecificationSubject(Subject $subject): void
    {
        $this->object = $subject;
    }

    /**
     * Gets the unwrapped proxied object from PhpSpec subject
     *
     * @return ?object
     */
    public function getWrappedObject()
    {
        return $this->object->getWrappedObject();
    }

    /**
     * Checks if a key exists in case object implements ArrayAccess
     *
     * @psalm-param TKey $key
     * @param int|string $key
     */
    public function offsetExists($key): bool
    {
        return $this->object->offsetExists($key);
    }

    /**
     * Gets the value in a particular position in the ArrayAccess object
     *
     * @psalm-param TKey $key
     * @param int|string $key
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    public function offsetGet($key): Subject
    {
        return $this->object->offsetGet($key);
    }

    /**
     * Sets the value in a particular position in the ArrayAccess object
     *
     * @psalm-param TKey $key
     * @param int|string $key
     * @param mixed $value
     * @psalm-suppress InvalidAttribute
     */
    #[\ReturnTypeWillChange]
    public function offsetSet($key, $value)
    {
        $this->object->offsetSet($key, $value);
    }

    /**
     * Unsets a position in the ArrayAccess object
     *
     * @psalm-param TKey $key
     * @param int|string $key
     * @psalm-suppress InvalidAttribute
     */
    #[\ReturnTypeWillChange]
    public function offsetUnset($key)
    {
        $this->object->offsetUnset($key);
    }

    /**
     * Proxies all calls to the PhpSpec subject
     */
    public function __call(string $method, array $arguments = array())
    {
        return \call_user_func_array(array($this->object, $method), $arguments);
    }

    /**
     * Proxies setting to the PhpSpec subject
     *
     * @param mixed $value
     */
    public function __set(string $property, $value)
    {
        $this->object->$property = $value;
    }

    /**
     * Proxies getting to the PhpSpec subject
     */
    public function __get(string $property)
    {
        return $this->object->$property;
    }

    /**
     * Proxies functor calls to PhpSpec subject
     */
    public function __invoke()
    {
        return \call_user_func_array(array($this->object, '__invoke'), \func_get_args());
    }
}
