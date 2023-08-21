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

namespace PhpSpec\Process\ReRunner;

use PhpSpec\Process\Context\ExecutionContext;
use Symfony\Component\Process\PhpExecutableFinder;

final class WindowsPassthruReRunner extends PhpExecutableReRunner
{
    /**
     * @var ExecutionContext
     */
    private $executionContext;

    /**
     * @return static
     */
    public static function withExecutionContext(PhpExecutableFinder $phpExecutableFinder, ExecutionContext $executionContext)
    {
        $reRunner = new static($phpExecutableFinder);
        $reRunner->executionContext = $executionContext;

        return $reRunner;
    }

    public function isSupported(): bool
    {
        return (php_sapi_name() == 'cli')
            && $this->getExecutablePath()
            && function_exists('passthru')
            && (stripos(PHP_OS, "win") === 0);
    }

    public function reRunSuite(): void
    {
        $args = $_SERVER['argv'];
        $command = $this->buildArgString() . escapeshellarg($this->getExecutablePath()) . ' ' . join(' ', array_map('escapeshellarg', $args));

        passthru($command, $exitCode);
        exit($exitCode);
    }

    private function buildArgString() : string
    {
        $argstring = '';

        foreach ($this->executionContext->asEnv() as $key => $value) {
            $argstring .= 'SET ' . $key . '=' . $value . ' && ';
        }

        return $argstring;
    }
}
