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

namespace PhpSpec\Formatter\Presenter\Exception;

use PhpSpec\Formatter\Presenter\Value\ExceptionTypePresenter;
use PhpSpec\Formatter\Presenter\Value\ValuePresenter;

final class SimpleExceptionElementPresenter implements ExceptionElementPresenter
{
    /**
     * @var ExceptionTypePresenter
     */
    private $exceptionTypePresenter;

    /**
     * @var ValuePresenter
     */
    private $valuePresenter;

    
    public function __construct(ExceptionTypePresenter $exceptionTypePresenter, ValuePresenter $valuePresenter)
    {
        $this->exceptionTypePresenter = $exceptionTypePresenter;
        $this->valuePresenter = $valuePresenter;
    }

    
    public function presentExceptionThrownMessage(\Exception $exception): string
    {
        return sprintf(
            'Exception %s has been thrown.',
            $this->exceptionTypePresenter->present($exception)
        );
    }

    
    public function presentCodeLine(string $number, string $line): string
    {
        return sprintf('%s %s', $number, $line);
    }

    
    public function presentHighlight(string $line): string
    {
        return $line;
    }

    
    public function presentExceptionTraceHeader(string $header): string
    {
        return $header;
    }

    
    public function presentExceptionTraceMethod(string $class, string $type, string $method, array $args): string
    {
        return sprintf('   %s%s%s(%s)', $class, $type, $method, $this->presentExceptionTraceArguments($args));
    }

    
    public function presentExceptionTraceFunction(string $function, array $args): string
    {
        return sprintf('   %s(%s)', $function, $this->presentExceptionTraceArguments($args));
    }

    
    private function presentExceptionTraceArguments(array $args): string
    {
        return implode(', ', array_map(array($this->valuePresenter, 'presentValue'), $args));
    }
}
