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

namespace PhpSpec\Runner\Maintainer;

use PhpSpec\Loader\Node\ExampleNode;
use PhpSpec\Specification;
use PhpSpec\Runner\MatcherManager;
use PhpSpec\Runner\CollaboratorManager;
use PhpSpec\Exception\Example as ExampleException;

final class ErrorMaintainer implements Maintainer
{
    /**
     * @var int
     */
    private $errorLevel;
    /**
     * @var null|callable
     */
    private $errorHandler;


    public function __construct(int $errorLevel)
    {
        $this->errorLevel = $errorLevel;
    }


    public function supports(ExampleNode $example): bool
    {
        return true;
    }


    public function prepare(
        ExampleNode $example,
        Specification $context,
        MatcherManager $matchers,
        CollaboratorManager $collaborators
    ): void {
        $this->errorHandler = set_error_handler(array($this, 'errorHandler'), $this->errorLevel);
    }


    public function teardown(
        ExampleNode $example,
        Specification $context,
        MatcherManager $matchers,
        CollaboratorManager $collaborators
    ): void {
        if (null !== $this->errorHandler) {
            set_error_handler($this->errorHandler);
        }
    }


    public function getPriority(): int
    {
        return 999;
    }

    /**
     * Custom error handler.
     *
     * This method used as custom error handler when step is running.
     *
     * @see set_error_handler()
     *
     * @throws ExampleException\ErrorException
     */
    final public function errorHandler(int $level, string $message, string $file, int $line): bool
    {
        $regex = '/^Argument (\d)+ passed to (?:(?P<class>[\w\\\]+)::)?(\w+)\(\)' .
            ' must (?:be an instance of|implement interface) ([\w\\\]+),(?: instance of)? ([\w\\\]+) given/';

        if (E_RECOVERABLE_ERROR === $level && preg_match($regex, $message, $matches)) {
            $class = $matches['class'];

            if (\in_array('PhpSpec\Specification', class_implements($class))) {
                return true;
            }
        }

        // error reporting turned off or more likely suppressed with error control operator "@"
        if (0 === (error_reporting() & $level)) {
            return false;
        }

        throw new ExampleException\ErrorException($level, $message, $file, $line);
    }
}
