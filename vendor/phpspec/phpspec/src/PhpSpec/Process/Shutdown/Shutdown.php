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

namespace PhpSpec\Process\Shutdown;

final class Shutdown
{
    protected $actions;

    public function __construct()
    {
        $this->actions = array();
    }

    public function registerShutdown(): void
    {
        error_reporting(error_reporting() & ~E_ERROR);
        register_shutdown_function(array($this, 'runShutdown'));
    }

    public function registerAction(ShutdownAction $action): void
    {
        $this->actions[] = $action;
    }

    public function runShutdown(): void
    {
        $error = $this->getFatalError();

        foreach ($this->actions as $fatalErrorActions) {
            $fatalErrorActions->runAction($error);
        }
    }

    private function getFatalError()
    {
        $error = error_get_last();

        return (null !== $error) && (bool) (E_ERROR & $error['type']) ? $error : null;
    }
}
