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

use PhpSpec\Console\ConsoleIO;
use PhpSpec\Process\ReRunner;

final class OptionalReRunner implements ReRunner
{
    /**
     * @var ConsoleIO
     */
    private $io;
    /**
     * @var ReRunner
     */
    private $decoratedRerunner;

    
    public function __construct(ReRunner $decoratedRerunner, ConsoleIO $io)
    {
        $this->io = $io;
        $this->decoratedRerunner = $decoratedRerunner;
    }

    public function reRunSuite(): void
    {
        if ($this->io->isRerunEnabled()) {
            $this->decoratedRerunner->reRunSuite();
        }
    }
}
