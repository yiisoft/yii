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

namespace Phing\Task\Ext;

use Phing\Exception\BuildException;
use Phing\Project;
use Phing\Task;
use Phing\Type\Commandline;

/**
 * Symfony Console Task.
 *
 * @author  nuno costa <nuno@francodacosta.com>
 * @license GPL
 */
class SymfonyConsoleTask extends Task
{
    /**
     * @var SymfonyConsoleArg[] a collection of Arg objects
     */
    private $args = [];

    /**
     * @var string the Symfony console command to execute
     */
    private $command;

    /**
     * @var string path to symfony console application
     */
    private $console = 'bin/console';

    /**
     * @var string property to be set
     */
    private $propertyName;

    /**
     * Whether to check the return code.
     *
     * @var bool
     */
    private $checkreturn = false;

    /**
     * Is the symfony cli debug mode set? (true by default).
     *
     * @var bool
     */
    private $debug = true;

    /**
     * @var bool
     */
    private $silent = false;

    /**
     * sets the symfony console command to execute.
     *
     * @param string $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * return the symfony console command to execute.
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * sets the path to symfony console application.
     *
     * @param string $console
     */
    public function setConsole($console)
    {
        $this->console = $console;
    }

    /**
     * returns the path to symfony console application.
     *
     * @return string
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * Set the name of the property to store the application output in.
     *
     * @param  $property
     */
    public function setPropertyName($property)
    {
        $this->propertyName = $property;
    }

    /**
     * Whether to check the return code.
     *
     * @param bool $checkreturn If the return code shall be checked
     */
    public function setCheckreturn(bool $checkreturn)
    {
        $this->checkreturn = $checkreturn;
    }

    /**
     * Whether to set the symfony cli debug mode.
     *
     * @param bool $debug If the symfony cli debug mode is set
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * Get if the symfony cli debug mode is set.
     *
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }

    public function setSilent(bool $flag)
    {
        $this->silent = $flag;
    }

    public function getSilent()
    {
        return $this->silent;
    }

    /**
     * appends an arg tag to the arguments stack.
     *
     * @return SymfonyConsoleArg Argument object
     */
    public function createArg()
    {
        $num = array_push($this->args, new SymfonyConsoleArg());

        return $this->args[$num - 1];
    }

    /**
     * return the argumments passed to this task.
     *
     * @return array of Arg()
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Gets the command string to be executed.
     *
     * @return string
     */
    public function getCmdString()
    {
        // Add no-debug arg if it isn't already present
        if (!$this->debug && !$this->isNoDebugArgPresent()) {
            $this->createArg()->setName('no-debug');
        }
        $cmd = [
            Commandline::quoteArgument($this->console),
            $this->command,
            implode(' ', $this->args),
        ];

        return implode(' ', $cmd);
    }

    /**
     * executes the synfony console application.
     */
    public function main()
    {
        $cmd = $this->getCmdString();

        $this->silent ?: $this->log("executing {$cmd}");
        $return = null;
        $output = [];
        exec($cmd, $output, $return);

        $lines = implode("\r\n", $output);

        $this->silent ?: $this->log($lines, Project::MSG_INFO);

        if (null != $this->propertyName) {
            $this->project->setProperty($this->propertyName, $lines);
        }

        if (0 != $return && $this->checkreturn) {
            $this->log('Task exited with code: ' . $return, Project::MSG_ERR);

            throw new BuildException('SymfonyConsole execution failed');
        }
    }

    /**
     * Check if the no-debug option was added via args.
     *
     * @return bool
     */
    private function isNoDebugArgPresent()
    {
        foreach ($this->args as $arg) {
            if ('no-debug' == $arg->getName()) {
                return true;
            }
        }

        return false;
    }
}
