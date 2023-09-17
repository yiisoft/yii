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

namespace Phing\Task\System;

use Phing\Exception\BuildException;
use Phing\Task;
use Phing\TaskContainer;

/**
 * Relentless is an Ant task that will relentlessly execute other tasks,
 * ignoring any failures until all tasks have completed. If any of the executed
 * tasks fail, then Relentless will fail; otherwise it will succeed.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class Relentless extends Task implements TaskContainer
{
    /**
     * We keep the list of tasks we will execute here.
     */
    private $taskList = [];

    /**
     * Flag indicating how much output to generate.
     */
    private $terse = false;

    /**
     * This method will be called when it is time to execute the task.
     *
     * @throws BuildException
     */
    public function main()
    {
        $failCount = 0;
        $taskNo = 0;
        if (0 === count($this->taskList)) {
            throw new BuildException('No tasks specified for <relentless>.');
        }
        $this->log('Relentlessly executing: ' . $this->getDescription());
        foreach ($this->taskList as $t) {
            ++$taskNo;
            $desc = $t->getDescription();
            if (null === $desc) {
                $desc = 'task ' . $taskNo;
            }
            if (!$this->terse) {
                $this->log('Executing: ' . $desc);
            }

            try {
                $t->perform();
            } catch (BuildException $x) {
                $this->log('Task ' . $desc . ' failed: ' . $x->getMessage());
                ++$failCount;
            }
        }
        if ($failCount > 0) {
            throw new BuildException(
                'Relentless execution: ' . $failCount . ' of ' . count($this->taskList) . ' tasks failed.'
            );
        }

        $this->log('All tasks completed successfully.');
    }

    /**
     * Ant will call this to inform us of nested tasks.
     */
    public function addTask(Task $task)
    {
        $this->taskList[] = $task;
    }

    /**
     * Set this to true to reduce the amount of output generated.
     *
     * @param mixed $terse
     */
    public function setTerse($terse)
    {
        $this->terse = $terse;
    }

    /**
     * Retrieve the terse property, indicating how much output we will generate.
     */
    public function isTerse()
    {
        return $this->terse;
    }
}
