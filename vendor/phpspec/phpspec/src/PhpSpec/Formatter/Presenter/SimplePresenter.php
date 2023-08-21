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

namespace PhpSpec\Formatter\Presenter;

use PhpSpec\Formatter\Presenter\Exception\ExceptionPresenter;
use PhpSpec\Formatter\Presenter\Value\ValuePresenter;

final class SimplePresenter implements Presenter
{
    /**
     * @var ValuePresenter
     */
    private $valuePresenter;

    /**
     * @var ExceptionPresenter
     */
    private $exceptionPresenter;

    
    public function __construct(ValuePresenter $valuePresenter, ExceptionPresenter $exceptionPresenter)
    {
        $this->valuePresenter = $valuePresenter;
        $this->exceptionPresenter = $exceptionPresenter;
    }

    
    public function presentValue($value): string
    {
        return $this->valuePresenter->presentValue($value);
    }

    
    public function presentException(\Exception $exception, bool $verbose = false): string
    {
        return $this->exceptionPresenter->presentException($exception, $verbose);
    }

    
    public function presentString(string $string): string
    {
        return $string;
    }
}
