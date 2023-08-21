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

namespace PhpSpec\Exception\Fracture;

/**
 * Class MethodInvocationException holds information about method invocation
 * exceptions
 */
abstract class MethodInvocationException extends FractureException
{
    
    private $subject;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @param string $method
     */
    public function __construct(string $message, $subject, $method, array $arguments = array())
    {
        parent::__construct($message);

        $this->subject   = $subject;
        $this->method    = $method;
        $this->arguments = $arguments;
    }

    
    public function getSubject()
    {
        return $this->subject;
    }

    
    public function getMethodName(): string
    {
        return $this->method;
    }

    
    public function getArguments(): array
    {
        return $this->arguments;
    }
}
