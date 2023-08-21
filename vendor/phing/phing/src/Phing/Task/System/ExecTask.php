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

use InvalidArgumentException;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileUtils;
use Phing\Io\IOException;
use Phing\Phing;
use Phing\Project;
use Phing\Task;
use Phing\Task\System\Condition\OsCondition;
use Phing\Task\System\Element\LogLevelAware;
use Phing\Type\Commandline;
use Phing\Type\CommandlineArgument;
use Phing\Type\Environment;
use Phing\Type\EnvVariable;
use Phing\Type\Path;
use Phing\Util\StringHelper;

/**
 * Executes a command on the shell.
 *
 * @author  Andreas Aderhold <andi@binarycloud.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 * @author  Christian Weiske <cweiske@cweiske.de>
 */
class ExecTask extends Task
{
    use LogLevelAware;

    public const INVALID = PHP_INT_MAX;

    /**
     * Command to be executed.
     *
     * @var string
     */
    protected $realCommand;

    /**
     * Commandline managing object.
     *
     * @var Commandline
     */
    protected $commandline;

    /**
     * Working directory.
     *
     * @var File
     */
    protected $dir;

    protected $currdir;

    /**
     * Operating system.
     *
     * @var string
     */
    protected $os;

    /**
     * Whether to escape shell command using escapeshellcmd().
     *
     * @var bool
     */
    protected $escape = false;

    /**
     * Where to direct output.
     *
     * @var File
     */
    protected $output;

    /**
     * Whether to use PHP's passthru() function instead of exec().
     *
     * @var bool
     */
    protected $passthru = false;

    /**
     * Whether to log returned output as MSG_INFO instead of MSG_VERBOSE.
     *
     * @var bool
     */
    protected $logOutput = false;

    /**
     * Where to direct error output.
     *
     * @var File
     */
    protected $error;

    /**
     * If spawn is set then [unix] programs will redirect stdout and add '&'.
     *
     * @var bool
     */
    protected $spawn = false;

    /**
     * Property name to set with return value from exec call.
     *
     * @var string
     */
    protected $returnProperty;

    /**
     * Property name to set with output value from exec call.
     *
     * @var string
     */
    protected $outputProperty;

    /**
     * Whether to check the return code.
     *
     * @var bool
     */
    protected $checkreturn = false;

    private $exitValue = self::INVALID;

    private $osFamily;
    private $executable;
    private $resolveExecutable = false;
    private $searchPath = false;
    private $env;

    /**
     * @throws BuildException
     */
    public function __construct()
    {
        parent::__construct();
        $this->commandline = new Commandline();
        $this->env = new Environment();
    }

    /**
     * Main method: wraps execute() command.
     *
     * @throws BuildException
     */
    public function main()
    {
        if (!$this->isValidOs()) {
            return null;
        }

        // Suggest Task instead of executable
        if ($this->executable && ($hint = $this->findHint($this->executable))) {
            $this->log($hint, Project::MSG_VERBOSE);
        }

        try {
            $this->commandline->setExecutable($this->resolveExecutable($this->executable, $this->searchPath));
        } catch (IOException | \InvalidArgumentException $e) {
            throw new BuildException($e);
        }

        $this->prepare();
        $this->buildCommand();
        [$return, $output] = $this->executeCommand();
        $this->cleanup($return, $output);

        return $return;
    }

    /**
     * @param int $exitValue
     *
     * @return bool
     */
    public function isFailure($exitValue = null)
    {
        if (null === $exitValue) {
            $exitValue = $this->getExitValue();
        }

        return 0 !== $exitValue;
    }

    /**
     * Query the exit value of the process.
     *
     * @return int the exit value or self::INVALID if no exit value has
     *             been received
     */
    public function getExitValue(): int
    {
        return $this->exitValue;
    }

    /**
     * The executable to use.
     *
     * @param bool|string $value String or string-compatible (e.g. w/ __toString()).
     */
    public function setExecutable($value): void
    {
        if (is_bool($value)) {
            $value = true === $value ? 'true' : 'false';
        }
        $this->executable = $value;
        $this->commandline->setExecutable($value);
    }

    /**
     * Whether to use escapeshellcmd() to escape command.
     *
     * @param bool $escape If the command shall be escaped or not
     */
    public function setEscape(bool $escape): void
    {
        $this->escape = $escape;
    }

    /**
     * Specify the working directory for executing this command.
     *
     * @param File $dir Working directory
     */
    public function setDir(File $dir): void
    {
        $this->dir = $dir;
    }

    /**
     * Specify OS (or multiple OS) that must match in order to execute this command.
     *
     * @param string $os Operating system string (e.g. "Linux")
     */
    public function setOs($os): void
    {
        $this->os = (string) $os;
    }

    /**
     * List of operating systems on which the command may be executed.
     */
    public function getOs(): string
    {
        return $this->os;
    }

    /**
     * Restrict this execution to a single OS Family.
     *
     * @param string $osFamily the family to restrict to
     */
    public function setOsFamily($osFamily): void
    {
        $this->osFamily = strtolower($osFamily);
    }

    /**
     * Restrict this execution to a single OS Family.
     */
    public function getOsFamily()
    {
        return $this->osFamily;
    }

    /**
     * File to which output should be written.
     *
     * @param File $f Output log file
     */
    public function setOutput(File $f): void
    {
        $this->output = $f;
    }

    /**
     * File to which error output should be written.
     *
     * @param File $f Error log file
     */
    public function setError(File $f): void
    {
        $this->error = $f;
    }

    /**
     * Whether to use PHP's passthru() function instead of exec().
     *
     * @param bool $passthru If passthru shall be used
     */
    public function setPassthru($passthru): void
    {
        $this->passthru = $passthru;
    }

    /**
     * Whether to log returned output as MSG_INFO instead of MSG_VERBOSE.
     *
     * @param bool $logOutput If output shall be logged visibly
     */
    public function setLogoutput($logOutput): void
    {
        $this->logOutput = $logOutput;
    }

    /**
     * Whether to suppress all output and run in the background.
     *
     * @param bool $spawn If the command is to be run in the background
     */
    public function setSpawn($spawn): void
    {
        $this->spawn = $spawn;
    }

    /**
     * Whether to check the return code.
     *
     * @param bool $checkreturn If the return code shall be checked
     */
    public function setCheckreturn($checkreturn): void
    {
        $this->checkreturn = $checkreturn;
    }

    /**
     * The name of property to set to return value from exec() call.
     *
     * @param string $prop Property name
     */
    public function setReturnProperty($prop): void
    {
        $this->returnProperty = $prop;
    }

    /**
     * The name of property to set to output value from exec() call.
     *
     * @param string $prop Property name
     */
    public function setOutputProperty($prop): void
    {
        $this->outputProperty = $prop;
    }

    /**
     * Add an environment variable to the launched process.
     *
     * @param EnvVariable $var new environment variable
     */
    public function addEnv(EnvVariable $var)
    {
        $this->env->addVariable($var);
    }

    /**
     * Creates a nested <arg> tag.
     *
     * @return CommandlineArgument Argument object
     */
    public function createArg()
    {
        return $this->commandline->createArgument();
    }

    /**
     * Set whether to attempt to resolve the executable to a file.
     *
     * @param bool $resolveExecutable if true, attempt to resolve the
     *                                path of the executable
     */
    public function setResolveExecutable($resolveExecutable): void
    {
        $this->resolveExecutable = $resolveExecutable;
    }

    /**
     * Set whether to search nested, then
     * system PATH environment variables for the executable.
     *
     * @param bool $searchPath if true, search PATHs
     */
    public function setSearchPath($searchPath): void
    {
        $this->searchPath = $searchPath;
    }

    /**
     * Indicates whether to attempt to resolve the executable to a
     * file.
     *
     * @return bool the resolveExecutable flag
     */
    public function getResolveExecutable(): bool
    {
        return $this->resolveExecutable;
    }

    /**
     * Prepares the command building and execution, i.e.
     * changes to the specified directory.
     *
     * @throws BuildException
     */
    protected function prepare()
    {
        if (null === $this->dir) {
            $this->dir = $this->getProject()->getBasedir();
        }

        if (null === $this->commandline->getExecutable()) {
            throw new BuildException(
                'ExecTask: Please provide "executable"'
            );
        }

        // expand any symbolic links first
        try {
            if (!$this->dir->getCanonicalFile()->exists()) {
                throw new BuildException(
                    "The directory '" . (string) $this->dir . "' does not exist"
                );
            }
            if (!$this->dir->getCanonicalFile()->isDirectory()) {
                throw new BuildException(
                    "'" . (string) $this->dir . "' is not a directory"
                );
            }
        } catch (IOException $e) {
            throw new BuildException(
                "'" . (string) $this->dir . "' is not a readable directory"
            );
        }
        $this->currdir = getcwd();
        @chdir($this->dir->getPath());

        $this->commandline->setEscape($this->escape);
    }

    /**
     * Builds the full command to execute and stores it in $command.
     *
     * @throws BuildException
     *
     * @uses   $command
     */
    protected function buildCommand()
    {
        if (null !== $this->error) {
            $this->realCommand .= ' 2> ' . escapeshellarg($this->error->getPath());
            $this->log(
                'Writing error output to: ' . $this->error->getPath(),
                $this->logLevel
            );
        }

        if (null !== $this->output) {
            $this->realCommand .= ' 1> ' . escapeshellarg($this->output->getPath());
            $this->log(
                'Writing standard output to: ' . $this->output->getPath(),
                $this->logLevel
            );
        } elseif ($this->spawn) {
            $this->realCommand .= ' 1>/dev/null';
            $this->log('Sending output to /dev/null', $this->logLevel);
        }

        // If neither output nor error are being written to file
        // then we'll redirect error to stdout so that we can dump
        // it to screen below.

        if (null === $this->output && null === $this->error && false === $this->passthru) {
            $this->realCommand .= ' 2>&1';
        }

        // we ignore the spawn bool for windows
        if ($this->spawn) {
            $this->realCommand .= ' &';
        }

        $envString = '';
        $environment = $this->env->getVariables();
        if (null !== $environment) {
            foreach ($environment as $variable) {
                if ($this->isPath($variable)) {
                    continue;
                }
                $this->log('Setting environment variable: ' . $variable, Project::MSG_VERBOSE);
                if (OsCondition::isOS(OsCondition::FAMILY_WINDOWS)) {
                    $envString .= 'set ' . $variable . '& ';
                } else {
                    $envString .= 'export ' . $variable . '; ';
                }
            }
        }

        $this->realCommand = $envString . $this->commandline . $this->realCommand;
    }

    /**
     * Executes the command and returns return code and output.
     *
     * @throws BuildException
     *
     * @return array array(return code, array with output)
     */
    protected function executeCommand()
    {
        $cmdl = $this->realCommand;

        $this->log('Executing command: ' . $cmdl, $this->logLevel);

        $output = [];
        $return = null;

        if ($this->passthru) {
            passthru($cmdl, $return);
        } else {
            exec($cmdl, $output, $return);
        }

        return [$return, $output];
    }

    /**
     * Runs all tasks after command execution:
     * - change working directory back
     * - log output
     * - verify return value.
     *
     * @param int   $return Return code
     * @param array $output Array with command output
     *
     * @throws BuildException
     */
    protected function cleanup($return, $output): void
    {
        if (null !== $this->dir) {
            @chdir($this->currdir);
        }

        $outloglevel = $this->logOutput ? Project::MSG_INFO : Project::MSG_VERBOSE;
        foreach ($output as $line) {
            $this->log($line, $outloglevel);
        }

        $this->maybeSetReturnPropertyValue($return);

        if ($this->outputProperty) {
            $this->project->setProperty(
                $this->outputProperty,
                implode("\n", $output)
            );
        }

        $this->setExitValue($return);

        if (0 !== $return) {
            if ($this->checkreturn) {
                throw new BuildException($this->getTaskType() . ' returned: ' . $return, $this->getLocation());
            }
            $this->log('Result: ' . $return, Project::MSG_ERR);
        }
    }

    /**
     * Set the exit value.
     *
     * @param int $value exit value of the process
     */
    protected function setExitValue($value): void
    {
        $this->exitValue = $value;
    }

    protected function maybeSetReturnPropertyValue(int $return)
    {
        if ($this->returnProperty) {
            $this->getProject()->setNewProperty($this->returnProperty, $return);
        }
    }

    /**
     * Is this the OS the user wanted?
     *
     * @return bool.
     *               <ul>
     *               <li>
     *               <li><code>true</code> if the os and osfamily attributes are null.</li>
     *               <li><code>true</code> if osfamily is set, and the os family and must match
     *               that of the current OS, according to the logic of
     *               {@link Os#isOs(String, String, String, String)}, and the result of the
     *               <code>os</code> attribute must also evaluate true.
     *               </li>
     *               <li>
     *               <code>true</code> if os is set, and the system.property os.name
     *               is found in the os attribute,</li>
     *               <li><code>false</code> otherwise.</li>
     *               </ul>
     */
    protected function isValidOs(): bool
    {
        //hand osfamily off to OsCondition class, if set
        if (null !== $this->osFamily && !OsCondition::isFamily($this->osFamily)) {
            return false;
        }
        //the Exec OS check is different from Os.isOs(), which
        //probes for a specific OS. Instead it searches the os field
        //for the current os.name
        $myos = Phing::getProperty('os.name');
        $this->log('Current OS is ' . $myos, Project::MSG_VERBOSE);
        if ((null !== $this->os) && (false === strpos($this->os, $myos))) {
            // this command will be executed only on the specified OS
            $this->log(
                'This OS, ' . $myos
                . ' was not found in the specified list of valid OSes: ' . $this->os,
                Project::MSG_VERBOSE
            );

            return false;
        }

        return true;
    }

    /**
     * The method attempts to figure out where the executable is so that we can feed
     * the full path. We first try basedir, then the exec dir, and then
     * fallback to the straight executable name (i.e. on the path).
     *
     * @param string $exec           the name of the executable
     * @param bool   $mustSearchPath if true, the executable will be looked up in
     *                               the PATH environment and the absolute path
     *                               is returned
     *
     * @throws BuildException
     * @throws IOException
     *
     * @return string the executable as a full path if it can be determined
     */
    protected function resolveExecutable($exec, $mustSearchPath): ?string
    {
        if (!$this->resolveExecutable) {
            return $exec;
        }
        // try to find the executable
        $executableFile = $this->getProject()->resolveFile($exec);
        if ($executableFile->exists()) {
            return $executableFile->getAbsolutePath();
        }
        // now try to resolve against the dir if given
        if (null !== $this->dir) {
            $executableFile = (new FileUtils())->resolveFile($this->dir, $exec);
            if ($executableFile->exists()) {
                return $executableFile->getAbsolutePath();
            }
        }
        // couldn't find it - must be on path
        if ($mustSearchPath) {
            $p = null;
            $environment = $this->env->getVariables();
            if (null !== $environment) {
                foreach ($environment as $env) {
                    if ($this->isPath($env)) {
                        $p = new Path($this->getProject(), $this->getPath($env));

                        break;
                    }
                }
            }
            if (null === $p) {
                $p = new Path($this->getProject(), getenv('path'));
            }
            if (null !== $p) {
                $dirs = $p->listPaths();
                foreach ($dirs as $dir) {
                    $executableFile = (new FileUtils())->resolveFile(new File($dir), $exec);
                    if ($executableFile->exists()) {
                        return $executableFile->getAbsolutePath();
                    }
                }
            }
        }

        return $exec;
    }

    private function isPath($line)
    {
        return StringHelper::startsWith('PATH=', $line) || StringHelper::startsWith('Path=', $line);
    }

    private function getPath($value)
    {
        if (is_string($value)) {
            return StringHelper::substring($value, strlen('PATH='));
        }

        if (is_array($value)) {
            $p = $value['PATH'];

            return $p ?? $value['Path'];
        }

        throw new InvalidArgumentException('$value should be of type array or string.');
    }

    /**
     * Give a Task as an alternative to executable
     */
    public function findHint(string $executable): ?string
    {
        switch ($executable) {
            case '/usr/bin/mkdir':
            case 'mkdir':
                $hint = 'Consider using MkdirTask https://www.phing.info/guide/chunkhtml/MkdirTask.html';
                break;
            case '/usr/bin/touch':
            case 'touch':
                $hint = 'Consider using TouchTask https://www.phing.info/guide/chunkhtml/TouchTask.html';
                break;
            case '/usr/bin/truncate':
            case 'truncate':
                $hint = 'Consider using TruncateTask https://www.phing.info/guide/chunkhtml/TruncateTask.html';
                break;
            case '/usr/bin/xsltproc':
            case 'xsltproc':
                $hint = 'Consider using XsltTask https://www.phing.info/guide/chunkhtml/XsltTask.html';
                break;
            case '/usr/bin/chmod':
            case 'chmod':
                $hint = 'Consider using ChmodTask https://www.phing.info/guide/chunkhtml/ChmodTask.html';
                break;
            case '/usr/bin/chown':
            case 'chown':
                $hint = 'Consider using ChownTask https://www.phing.info/guide/chunkhtml/ChownTask.html';
                break;
            case '/usr/bin/mv':
            case 'mv':
                $hint = 'Consider using MoveTask https://www.phing.info/guide/chunkhtml/MoveTask.html';
                break;
            case 'sed':
                $hint = 'Consider using ReplaceTokens filter https://www.phing.info/guide/chunkhtml/ReplaceTokens.html';
                break;
            case '/usr/bin/rmdir':
            case 'rmdir':
            case '/usr/bin/rm':
            case 'rm':
            case '/usr/bin/unlink':
            case 'unlink':
                $hint = 'Consider using DeleteTask https://www.phing.info/guide/chunkhtml/DeleteTask.html';
                break;
            case '/usr/bin/sleep':
            case 'sleep':
                $hint = 'Consider using SleepTask https://www.phing.info/guide/chunkhtml/SleepTask.html';
                break;
            case '/usr/local/bin':
            case 'ln':
                $hint = 'Consider using SymlinkTask https://www.phing.info/guide/chunkhtml/SymlinkTask.html';
                break;
            case '/usr/bin/wget':
            case 'wget':
                $hint = 'Consider using HttpGetTask https://www.phing.info/guide/chunkhtml/HttpGetTask.html';
                break;
            case '/usr/bin/curl':
            case 'curl':
                $hint = 'Consider using HttpRequestTask https://www.phing.info/guide/chunkhtml/HttpRequestTask.html';
                break;
            case '/usr/bin/xdg-open':
            case 'xdg-open':
            case 'wslview':
            case 'open':
            case 'start':
                $hint = 'Consider using OpenTask https://www.phing.info/guide/chunkhtml/OpenTask.html';
                break;
            case '/usr/bin/zip':
            case 'zip':
                $hint = 'Consider using ZipTask https://www.phing.info/guide/chunkhtml/ZipTask.html';
                break;
            case '/usr/bin/unzip':
            case 'unzip':
                $hint = 'Consider using UnzipTask https://www.phing.info/guide/chunkhtml/UnzipTask.html';
                break;
            case '/usr/bin/tar':
            case 'tar':
                $hint = 'Consider using TarTask https://www.phing.info/guide/chunkhtml/TarTask.html';
                break;
            case '/usr/bin/echo':
            case 'echo':
                $hint = 'Consider using EchoTask https://www.phing.info/guide/chunkhtml/EchoTask.html';
                break;
            default:
                $hint = null;
                break;
        }

        return $hint;
    }
}
