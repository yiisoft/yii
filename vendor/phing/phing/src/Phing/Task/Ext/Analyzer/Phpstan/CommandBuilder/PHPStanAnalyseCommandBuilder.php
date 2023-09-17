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

use Phing\Task\Ext\Analyzer\Phpstan\PHPStanTask;

class PHPStanAnalyseCommandBuilder extends PHPStanCommandBuilder
{
    public function build(PHPStanTask $task): void
    {
        parent::build($task);

        $cmd = $task->getCommandline();

        if (!empty($task->getConfiguration())) {
            $cmd->createArgument()->setValue('--configuration=' . $task->getConfiguration());
        }
        if (!empty($task->getLevel())) {
            $cmd->createArgument()->setValue('--level=' . $task->getLevel());
        }
        if ($task->isNoProgress()) {
            $cmd->createArgument()->setValue('--no-progress');
        }
        if ($task->isDebug()) {
            $cmd->createArgument()->setValue('--debug');
        }
        if (!empty($task->getAutoloadFile())) {
            $cmd->createArgument()->setValue('--autoload-file=' . $task->getAutoloadFile());
        }
        if (!empty($task->getErrorFormat())) {
            $cmd->createArgument()->setValue('--error-format=' . $task->getErrorFormat());
        }
        if (!empty($task->getMemoryLimit())) {
            $cmd->createArgument()->setValue('--memory-limit=' . $task->getMemoryLimit());
        }
        if (!empty($task->getPaths())) {
            $cmd->createArgument()->setValue($task->getPaths());
        }
        if (count($task->getFileSets()) > 0) {
            foreach ($task->getFileSets() as $fs) {
                foreach ($fs as $file) {
                    $cmd->createArgument()->setValue($file);
                }
            }
        }
    }
}
