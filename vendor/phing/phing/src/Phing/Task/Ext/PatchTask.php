<?php

/**
 *  Patches a file by applying a 'diff' file to it.
 *
 *  Requires "patch" to be on the execution path.
 *
 *  Based on Apache Ant PatchTask:
 *
 *  Licensed to the Apache Software Foundation (ASF) under one or more
 *  contributor license agreements.  See the NOTICE file distributed with
 *  this work for additional information regarding copyright ownership.
 *  The ASF licenses this file to You under the Apache License, Version 2.0
 *  (the "License"); you may not use this file except in compliance with
 *  the License.  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Phing\Task\Ext;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\IOException;
use Phing\Project;
use Phing\Task;
use Phing\Task\System\ExecTask;
use Phing\Type\Commandline;

/**
 * Patches a file by applying a 'diff' file to it.
 *
 * Requires "patch" to be on the execution path.
 */
class PatchTask extends Task
{
    private static $PATCH = 'patch';

    /**
     * File to be patched.
     *
     * @var File
     */
    private $originalFile;

    /**
     * @var File
     */
    private $directory;

    /**
     * Halt on error return value from patch invocation.
     *
     * @var bool
     */
    private $failOnError = false;

    /**
     * @var Commandline
     */
    private $cmd;

    /**
     * @var bool
     */
    private $havePatchFile = false;

    public function __construct()
    {
        $this->cmd = new Commandline();
        parent::__construct();
    }

    /**
     * The file containing the diff output.
     *
     * Required.
     *
     * @param File $file File containing the diff output
     *
     * @throws BuildException if $file not exists
     */
    public function setPatchFile(File $file)
    {
        if (!$file->exists()) {
            throw new BuildException('patchfile ' . $file . " doesn't exist", $this->getLocation());
        }
        $this->cmd->createArgument()->setValue('-i');
        $this->cmd->createArgument()->setFile($file);
        $this->havePatchFile = true;
    }

    /**
     * flag to create backups; optional, default=false.
     *
     * @param bool $backups if true create backups
     */
    public function setBackups($backups)
    {
        if ($backups) {
            $this->cmd->createArgument()->setValue('-b');
        }
    }

    /**
     * flag to ignore whitespace differences; default=false.
     *
     * @param bool $ignore if true ignore whitespace differences
     */
    public function setIgnorewhitespace($ignore)
    {
        if ($ignore) {
            $this->cmd->createArgument()->setValue('-l');
        }
    }

    /**
     * The file to patch.
     *
     * Optional if it can be inferred from the diff file.
     *
     * @param File $file File to patch
     */
    public function setOriginalFile(File $file)
    {
        $this->originalFile = $file;
    }

    /**
     * The name of a file to send the output to, instead of patching
     * the file(s) in place.
     *
     * Optional.
     *
     * @param File $file File to send the output to
     */
    public function setDestFile(File $file)
    {
        $this->cmd->createArgument()->setValue('-o');
        $this->cmd->createArgument()->setFile($file);
    }

    /**
     * Strip the smallest prefix containing <i>num</i> leading slashes
     * from filenames.
     *
     * patch's <i>--strip</i> option.
     *
     * @param int $num number of lines to strip
     *
     * @throws BuildException if num is < 0, or other errors
     */
    public function setStrip($num)
    {
        if ($num < 0) {
            throw new BuildException('strip has to be >= 0');
        }

        $this->cmd->createArgument()->setValue("--strip {$num}");
    }

    /**
     * Work silently unless an error occurs.
     *
     * Optional, default - false
     *
     * @param bool $flag If true suppress set the -s option on the patch command
     */
    public function setQuiet($flag)
    {
        if ($flag) {
            $this->cmd->createArgument()->setValue('-s');
        }
    }

    /**
     * Assume patch was created with old and new files swapped.
     *
     * Optional, default - false
     *
     * @param bool $flag If true set the -R option on the patch command
     */
    public function setReverse($flag)
    {
        if ($flag) {
            $this->cmd->createArgument('-R');
        }
    }

    /**
     * The directory to run the patch command in.
     *
     * Defaults to the project's base directory.
     *
     * @param File $directory Directory to run the patch command in
     */
    public function setDir(File $directory)
    {
        $this->directory = $directory;
    }

    /**
     * Ignore patches that seem to be reversed or already applied.
     *
     * @param bool $flag If true set the -N (--forward) option
     */
    public function setForward($flag)
    {
        if ($flag) {
            $this->cmd->createArgument()->setValue('-N');
        }
    }

    /**
     * Set the maximum fuzz factor.
     *
     * Defaults to 0
     *
     * @param string $value Value of a fuzz factor
     */
    public function setFuzz($value)
    {
        $this->cmd->createArgument()->setValue("-F {$value}");
    }

    /**
     * If true, stop the build process if the patch command
     * exits with an error status.
     *
     * The default is "false"
     *
     * @param bool $value "true" if it should halt, otherwise "false"
     */
    public function setFailOnError($value)
    {
        $this->failOnError = $value;
    }

    public function setHaltOnFailure(string $value)
    {
        $this->failOnError = $value;
    }

    /**
     * Main task method.
     *
     * @throws BuildException when it all goes a bit pear shaped
     */
    public function main()
    {
        if (!$this->havePatchFile) {
            throw new BuildException('patchfile argument is required', $this->getLocation());
        }

        $toExecute = $this->cmd;
        $toExecute->setExecutable(self::$PATCH);

        if (null !== $this->originalFile) {
            $toExecute->createArgument()->setFile($this->originalFile);
        }

        $exe = new ExecTask();
        foreach ($toExecute->getArguments() as $part) {
            $exe->createArg()->setValue($part);
        }
        $exe->setLevel('info');
        $exe->setExecutable($toExecute->getExecutable());

        try {
            if (null === $this->directory) {
                $exe->setDir($this->getProject()->getBasedir());
            } else {
                if (!$this->directory->isDirectory()) {
                    throw new BuildException($this->directory . ' is not a directory.', $this->getLocation());
                }
                $exe->setDir($this->directory);
            }

            $this->log($toExecute->describeCommand(), Project::MSG_VERBOSE);

            $returnCode = $exe->main();
            if ($exe->isFailure($returnCode)) {
                $msg = "'" . self::$PATCH . "' failed with exit code " . $returnCode;
                if ($this->failOnError) {
                    throw new BuildException($msg);
                }
                $this->log($msg, Project::MSG_ERR);
            }
        } catch (IOException $e) {
            if ($this->failOnError) {
                throw new BuildException($e, $this->getLocation());
            }
            $this->log($e->getMessage(), Project::MSG_ERR);
        }
    }
}
