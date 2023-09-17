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
use Phing\Io\File;
use Phing\Project;
use Phing\Task;
use Phing\Task\System\Element\LogLevelAware;
use Phing\Task\System\ExecTask;
use Phing\Type\Commandline;
use Phing\Type\Element\FileSetAware;

/**
 * A PHP code sniffer task. Checking the style of one or more PHP source files.
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class PhpCSTask extends Task
{
    use LogLevelAware;
    use FileSetAware;

    /**
     * The.
     *
     * @var array
     */
    protected $files = [];

    protected $formatters = [];

    /**
     * A php source code filename or directory.
     *
     * @var File
     */
    private $file;

    /** @var Commandline */
    private $cmd;

    /** @var bool */
    private $cache = false;

    /** @var bool */
    private $ignoreAnnotations = false;

    /** @var bool */
    private $checkreturn = false;

    /** @var string */
    private $standard = '';

    /** @var string */
    private $outfile = '';

    /** @var string */
    private $format = '';

    /** @var string */
    private $bin = 'phpcs';

    public function __construct()
    {
        $this->cmd = new Commandline();
        $this->logLevelName = 'info';
        parent::__construct();
    }

    public function getCommandline(): Commandline
    {
        return $this->cmd;
    }

    public function setCache(bool $cache): void
    {
        $this->cache = $cache;
    }

    public function setIgnoreAnnotations(bool $ignore): void
    {
        $this->ignoreAnnotations = $ignore;
    }

    public function setCheckreturn(bool $checkreturn): void
    {
        $this->checkreturn = $checkreturn;
    }

    public function setBin(string $bin): void
    {
        $this->bin = $bin;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
        $this->project->log("Format set to {$format}", Project::MSG_VERBOSE);
    }

    /**
     * Create object for nested formatter element.
     *
     * @return CodeSnifferFormatterElement
     */
    public function createFormatter()
    {
        $num = array_push(
            $this->formatters,
            new PhpCSTaskFormatterElement()
        );

        return $this->formatters[$num - 1];
    }

    public function setStandard(string $standard): void
    {
        $this->standard = $standard;
        $this->project->log("Standard set to {$standard}", Project::MSG_VERBOSE);
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    public function setOutfile(string $outfile): void
    {
        $this->outfile = $outfile;
        $this->project->log("Outfile set to {$outfile}", Project::MSG_VERBOSE);
    }

    public function main()
    {
        if (null === $this->file && 0 == count($this->filesets)) {
            throw new BuildException('Missing both attribute "file" and "fileset".');
        }
        if (null === $this->file) {
            // check filesets, and compile a list of files for phpcs to analyse
            foreach ($this->filesets as $fileset) {
                $files = $fileset->getIterator();
                foreach ($files as $file) {
                    $this->files[] = $file;
                }
            }
        }

        $toExecute = $this->getCommandline();

        $this->cache
            ? $toExecute->createArgument()->setValue('--cache')
            : $toExecute->createArgument()->setValue('--no-cache');

        if ($this->ignoreAnnotations) {
            $toExecute->createArgument()->setValue('--ignore-annotations');
        }
        if ('' !== $this->format) {
            $toExecute->createArgument()->setValue(' --report=' . $this->format);
        }
        if ('' !== $this->standard) {
            $toExecute->createArgument()->setValue(' --standard=' . $this->standard);
        }
        if ('' !== $this->outfile) {
            $toExecute->createArgument()->setValue(' --report-file=' . $this->outfile);
        }

        foreach ($this->formatters as $formatter) {
            $formatterReportFile = ($formatter->getUseFile() ? $formatter->getOutFile() : null);
            $formatterType = $formatter->getType();
            $this->project->log(
                "Generate report of type \"{$formatterType}\" with report written to {$formatterReportFile}",
                Project::MSG_VERBOSE
            );
            $toExecute->createArgument()->setValue(' --report-' . $formatterType . '=' . $formatterReportFile);
        }

        if (null !== $this->file) {
            $toExecute->createArgument()->setFile($this->file);
        } else {
            foreach ($this->files as $file) {
                $toExecute->createArgument()->setFile(new File($file));
            }
        }

        $exe = new ExecTask();
        $exe->setProject($this->getProject());
        $exe->setLocation($this->getLocation());
        $exe->setOwningTarget($this->target);
        $exe->setTaskName($this->getTaskName());
        $exe->setExecutable($this->bin);
        $exe->setCheckreturn($this->checkreturn);
        $exe->setLevel($this->logLevelName);
        $exe->setExecutable($toExecute->getExecutable());
        $exe->createArg()->setLine(implode(' ', $toExecute->getArguments()));
        $exe->main();
    }
}
