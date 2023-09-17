<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

declare(strict_types=1);

namespace Phing\Task\Ext\Analyzer\Phpstan\CommandBuilder;

use Phing\Exception\BuildException;
use Phing\Task\Ext\Analyzer\Phpstan\PHPStanTask;

abstract class PHPStanCommandBuilder
{
    private const ARG_HELP = '--help';
    private const ARG_QUIET = '--quiet';
    private const ARG_VERSION = '--version';
    private const ARG_ANSI = '--ansi';
    private const ARG_NO_ANSI = '--no-ansi';
    private const ARG_NO_INTERACTION = '--no-interaction';
    private const ARG_VERBOSE = '--verbose';

    public function build(PHPStanTask $task): void
    {
        $this->validate($task);

        $cmd = $task->getCommandline();
        $cmd->setExecutable($task->getExecutable());
        $cmd->createArgument()->setValue($task->getCommand());
        if ($task->isHelp()) {
            $cmd->createArgument()->setValue(self::ARG_HELP);
        }
        if ($task->isQuiet()) {
            $cmd->createArgument()->setValue(self::ARG_QUIET);
        }
        if ($task->isVersion()) {
            $cmd->createArgument()->setValue(self::ARG_VERSION);
        }
        if ($task->isAnsi()) {
            $cmd->createArgument()->setValue(self::ARG_ANSI);
        }
        if ($task->isNoAnsi()) {
            $cmd->createArgument()->setValue(self::ARG_NO_ANSI);
        }
        if ($task->isNoInteraction()) {
            $cmd->createArgument()->setValue(self::ARG_NO_INTERACTION);
        }
        if ($task->isVerbose()) {
            $cmd->createArgument()->setValue(self::ARG_VERBOSE);
        }
    }

    private function validate(PHPStanTask $task): void
    {
        if (empty($task->getExecutable())) {
            throw new BuildException('executable not set');
        }
    }
}
