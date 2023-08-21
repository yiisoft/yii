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

use PhpSpec\Exception\Example\FailureException;
use PhpSpec\Exception\Example\NotEqualException;
use PhpSpec\Formatter\Presenter\Presenter;

final class ApproximatelyMatcher extends BasicMatcher
{

    /**
     * @var array
     */
    private static $keywords = array(
        'beApproximately',
        'beEqualToApproximately',
        'equalApproximately',
        'returnApproximately'
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
        if (!\in_array($name, self::$keywords) || 2 != \count($arguments)) {
            return false;
        }
        if (is_object($subject) || is_object($arguments[0])) {
            return false;
        }

        if (is_array($subject) xor is_array($arguments[0])) {
            return false;
        }

        return true;
    }

    
    protected function matches($subject, array $arguments): bool
    {
        [$expected, $precision] = $arguments;

        if (!is_array($expected)) {
            $expected = [$expected];
            $subject = [$subject];
        }

        if (count($expected) !== count($subject)) {
            return false;
        }

        foreach ($expected as $k => $v) {
            if (abs($subject[$k] - ((float)$v)) > $precision) {
                return false;
            }
        }

        return true;
    }


    protected function getFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new NotEqualException(sprintf(
            'Expected an approximated value of %s, but got %s',
            $this->presenter->presentValue($arguments[0]),
            $this->presenter->presentValue($subject)
        ), $arguments[0], $subject);
    }

    protected function getNegativeFailureException(string $name, $subject, array $arguments): FailureException
    {
        return new FailureException(sprintf(
            'Did Not expect an approximated value of %s, but got %s',
            $this->presenter->presentValue($arguments[0]),
            $this->presenter->presentValue($subject)
        ));
    }


}
