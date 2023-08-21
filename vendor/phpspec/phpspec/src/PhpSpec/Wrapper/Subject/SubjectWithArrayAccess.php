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

use PhpSpec\Wrapper\Unwrapper;
use PhpSpec\Formatter\Presenter\Presenter;
use PhpSpec\Exception\Wrapper\SubjectException;
use PhpSpec\Exception\Fracture\InterfaceNotImplementedException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SubjectWithArrayAccess
{
    /**
     * @var Caller
     */
    private $caller;
    /**
     * @var Presenter
     */
    private $presenter;
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    
    public function __construct(
        Caller $caller,
        Presenter $presenter,
        EventDispatcherInterface $dispatcher
    ) {
        $this->caller     = $caller;
        $this->presenter  = $presenter;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @param int|string $key
     */
    public function offsetExists($key): bool
    {
        $unwrapper = new Unwrapper();
        $subject = $this->caller->getWrappedObject();
        $key     = $unwrapper->unwrapOne($key);

        $this->checkIfSubjectImplementsArrayAccess($subject);

        /** @var \ArrayAccess|array $subject */
        return isset($subject[$key]);
    }

    /**
     * @param int|string $key
     */
    public function offsetGet($key)
    {
        $unwrapper = new Unwrapper();
        $subject = $this->caller->getWrappedObject();
        $key     = $unwrapper->unwrapOne($key);

        $this->checkIfSubjectImplementsArrayAccess($subject);

        /** @var \ArrayAccess|array $subject */
        return $subject[$key];
    }

    /**
     * @param int|string $key
     */
    public function offsetSet($key, $value): void
    {
        $unwrapper = new Unwrapper();
        $subject = $this->caller->getWrappedObject();
        $key     = $unwrapper->unwrapOne($key);
        $value   = $unwrapper->unwrapOne($value);

        $this->checkIfSubjectImplementsArrayAccess($subject);

        /** @var \ArrayAccess|array $subject */
        $subject[$key] = $value;
    }

    /**
     * @param int|string $key
     */
    public function offsetUnset($key): void
    {
        $unwrapper = new Unwrapper();
        $subject = $this->caller->getWrappedObject();
        $key     = $unwrapper->unwrapOne($key);

        $this->checkIfSubjectImplementsArrayAccess($subject);

        /** @var \ArrayAccess|array $subject */
        unset($subject[$key]);
    }

    /**
     * @throws \PhpSpec\Exception\Wrapper\SubjectException
     * @throws \PhpSpec\Exception\Fracture\InterfaceNotImplementedException
     */
    private function checkIfSubjectImplementsArrayAccess($subject): void
    {
        if (\is_object($subject) && !($subject instanceof \ArrayAccess)) {
            throw $this->interfaceNotImplemented();
        } elseif (!($subject instanceof \ArrayAccess) && !\is_array($subject)) {
            throw $this->cantUseAsArray($subject);
        }
    }

    
    private function interfaceNotImplemented(): InterfaceNotImplementedException
    {
        return new InterfaceNotImplementedException(
            sprintf(
                '%s does not implement %s interface, but should.',
                $this->presenter->presentValue($this->caller->getWrappedObject()),
                $this->presenter->presentString('ArrayAccess')
            ),
            $this->caller->getWrappedObject(),
            'ArrayAccess'
        );
    }

    
    private function cantUseAsArray($subject): SubjectException
    {
        return new SubjectException(sprintf(
            'Can not use %s as array.',
            $this->presenter->presentValue($subject)
        ));
    }
}
