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

namespace PhpSpec\Matcher;

use PhpSpec\Formatter\Presenter\Presenter;
use PhpSpec\Exception\Example\FailureException;

final class TypeMatcher extends BasicMatcher
{
    /**
     * @var array
     */
    private static $keywords = array(
        'beAnInstanceOf',
        'returnAnInstanceOf',
        'haveType',
        'implement'
    );
    /**
     * @var Presenter
     */
    private $presenter;

    
    public function __construct(Presenter $presenter)
    {
        $this->presenter = $presenter;
    }

    
    public function supports(string $name, $subject, array $arguments): bool
    {
        return \in_array($name, self::$keywords)
            && 1 == \count($arguments)
        ;
    }

    
    protected function matches($subject, array $arguments): bool
    {
        return (null !== $subject) && ($subject instanceof $arguments[0]);
    }

    
    protected function getFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            'Expected an instance of %s, but got %s.',
            $this->presenter->presentString($arguments[0]),
            $this->presenter->presentValue($subject)
        ));
    }

    
    protected function getNegativeFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            'Did not expect instance of %s, but got %s.',
            $this->presenter->presentString($arguments[0]),
            $this->presenter->presentValue($subject)
        ));
    }
}
