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


interface ExceptionElementPresenter
{
    
    public function presentExceptionThrownMessage(\Exception $exception): string;

    
    public function presentCodeLine(string $number, string $line): string;

    
    public function presentHighlight(string $line): string;

    
    public function presentExceptionTraceHeader(string $header): string;

    
    public function presentExceptionTraceMethod(string $class, string $type, string $method, array $args): string;

    
    public function presentExceptionTraceFunction(string $function, array $args): string;
}
