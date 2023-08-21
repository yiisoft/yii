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

namespace PhpSpec\Exception\Example;

/**
 * Class ErrorException holds information about generic php errors
 */
class ErrorException extends ExampleException
{
    /**
     * @var array
     */
    private $levels = array(
        E_WARNING           => 'warning',
        E_NOTICE            => 'notice',
        E_USER_ERROR        => 'error',
        E_USER_WARNING      => 'warning',
        E_USER_NOTICE       => 'notice',
        E_STRICT            => 'notice',
        E_RECOVERABLE_ERROR => 'error',
    );

    /**
     * Initializes error handler exception.
     *
     * @param int $level      error level
     * @param string $message error message
     * @param string $file    error file
     * @param int $line       error line
     */
    public function __construct(int $level, string $message, string $file, int $line)
    {
        parent::__construct(sprintf(
            '%s: %s in %s line %d',
            $this->levels[$level] ?? $level,
            $message,
            $file,
            $line
        ));
    }
}
