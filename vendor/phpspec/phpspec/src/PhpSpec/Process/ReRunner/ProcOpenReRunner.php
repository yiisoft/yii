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

final class ProcOpenReRunner extends PhpExecutableReRunner
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
            && (stripos(PHP_OS, "win") !== 0);
    }

    public function reRunSuite(): void
    {
        $args = $_SERVER['argv'];
        $command = $this->buildArgString() . escapeshellcmd($this->getExecutablePath()).' '.join(' ', array_map('escapeshellarg', $args)) . ' 2>&1';

        $desc = [
            0 => ['file', 'php://stdin', 'r'],
            1 => ['file', 'php://stdout', 'w'],
            2 => ['file', 'php://stderr', 'w'],
        ];
        $proc = proc_open( $command, $desc, $pipes );

        do {
            sleep(1);
            $status = proc_get_status($proc);
        } while ($status['running']);

        exit($status['exitcode']);
    }

    private function buildArgString() : string
    {
        $argstring = '';

        foreach ($this->executionContext->asEnv() as $key => $value) {
            $argstring .= $key . '=' . escapeshellarg($value) . ' ';
        }

        return $argstring;
    }
}
