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

use PhpSpec\Formatter\FatalPresenter;
use PhpSpec\Message\CurrentExampleTracker;

final class UpdateConsoleAction implements ShutdownAction
{
    /**
     * @var CurrentExampleTracker
     */
    private $currentExample;

    /**
     * @var FatalPresenter
     */
    private $currentExampleWriter;

    public function __construct(CurrentExampleTracker $currentExample, FatalPresenter $currentExampleWriter)
    {
        $this->currentExample = $currentExample;
        $this->currentExampleWriter = $currentExampleWriter;
    }

    public function runAction($error): void
    {
        $this->currentExampleWriter->displayFatal($this->currentExample, $error);
    }
}
