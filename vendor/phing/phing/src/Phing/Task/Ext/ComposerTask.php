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
use Phing\Io\FileSystem;
use Phing\Io\IOException;
use Phing\Project;
use Phing\Task;
use Phing\Type\Commandline;
use Phing\Type\CommandlineArgument;
use SplFileInfo;

/**
 * Composer Task.
 *
 * Run composer straight from phing
 *
 * @author  nuno costa <nuno@francodacosta.com>
 * @license MIT
 */
class ComposerTask extends Task
{
    /**
     * Path to php interpreter.
     *
     * @var string
     */
    private $php = '';

    /**
     * Composer command to execute.
     *
     * @var string
     */
    private $command;

    /**
     * Commandline object.
     *
     * @var Commandline
     */
    private $commandLine;

    /**
     * Path to Composer application.
     *
     * @var string
     */
    private $composer = 'composer.phar';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->commandLine = new Commandline();
    }

    /**
     * Initialize the interpreter with the Phing property php.interpreter.
     */
    public function init()
    {
        $this->setPhp($this->project->getProperty('php.interpreter'));
    }

    /**
     * Sets the path to php executable.
     *
     * @param string $php
     */
    public function setPhp($php)
    {
        $this->php = $php;
    }

    /**
     * Gets the path to php executable.
     *
     * @return string
     */
    public function getPhp()
    {
        return $this->php;
    }

    /**
     * Sets the Composer command to execute.
     *
     * @param string $command
     */
    public function setCommand($command)
    {
        $this->command = $command;
    }

    /**
     * Return the Composer command to execute.
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Sets the path to Composer application.
     *
     * @param string $console
     */
    public function setComposer($console)
    {
        $this->composer = $console;
    }

    /**
     * Returns the path to Composer application.
     *
     * If the filepath is non existent, try to find it on the system.
     *
     * @throws IOException
     *
     * @return string
     */
    public function getComposer()
    {
        $composerFile = new SplFileInfo($this->composer);
        if (false === $composerFile->isFile()) {
            $message = sprintf('Composer binary not found at "%s"', $composerFile);
            $this->log($message, Project::MSG_WARN);
            $composerLocation = FileSystem::getFileSystem()->which('composer');
            if (!empty($composerLocation)) {
                $message = sprintf('Composer binary found at "%s", updating location', $composerLocation[0]);
                $this->log($message, Project::MSG_INFO);
                $this->setComposer($composerLocation);
            }
        }

        return $this->composer;
    }

    /**
     * Creates a nested arg task.
     *
     * @return CommandlineArgument
     */
    public function createArg()
    {
        return $this->commandLine->createArgument();
    }

    /**
     * Executes the Composer task.
     *
     * @throws IOException
     */
    public function main()
    {
        $commandLine = $this->prepareCommandLine();
        $this->log('Executing ' . $commandLine);
        passthru($commandLine, $returnCode);

        if ($returnCode > 0) {
            throw new BuildException('Composer execution failed');
        }
    }

    /**
     * Prepares the command string to be executed.
     *
     * @throws IOException
     *
     * @return string
     */
    private function prepareCommandLine()
    {
        $this->commandLine->setExecutable($this->getPhp());
        $command = $this->getCommand();
        if (empty($command)) {
            throw new BuildException('"command" attribute is required');
        }
        //We are un-shifting arguments to the beginning of the command line because arguments should be at the end
        $this->commandLine->createArgument(true)->setValue($command);
        $this->commandLine->createArgument(true)->setValue($this->getComposer());
        $commandLine = (string) $this->commandLine;
        //Creating new Commandline instance. It allows to handle subsequent calls correctly
        $this->commandLine = new Commandline();

        return $commandLine;
    }
}
