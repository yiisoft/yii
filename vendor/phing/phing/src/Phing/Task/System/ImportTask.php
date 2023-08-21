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
use Phing\Io\File;
use Phing\Io\FileSystem;
use Phing\Parser\ProjectConfigurator;
use Phing\Parser\XmlContext;
use Phing\Project;
use Phing\Task;
use Phing\Type\Element\ResourceAware;
use Phing\Type\FileSet;

/**
 * Imports another build file into the current project.
 *
 * Targets and properties of the imported file can be overrridden
 * by targets and properties of the same name declared in the importing file.
 *
 * The imported file will have a new synthetic property of
 * "phing.file.<projectname>" declared which gives the full path to the
 * imported file. Additionally each target in the imported file will be
 * declared twice: once with the normal name and once with "<projectname>."
 * prepended. The "<projectname>.<targetname>" synthetic targets allow the
 * importing file a mechanism to call the imported files targets as
 * dependencies or via the <phing> or <phingcall> task mechanisms.
 *
 * @author  Bryan Davis <bpd@keynetics.com>
 */
class ImportTask extends Task
{
    use ResourceAware;

    /**
     * @var FileSystem
     */
    protected $fs;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var bool
     */
    protected $optional = false;

    /**
     * @var string
     */
    private $targetPrefix;

    /**
     * @var string
     */
    private $prefixSeparator = '.';

    /**
     * Initialize task.
     */
    public function init()
    {
        $this->fs = FileSystem::getFileSystem();
    }

    /**
     * Set the file to import.
     *
     * @param string $f Path to file
     */
    public function setFile($f)
    {
        $this->file = $f;
    }

    /**
     * The prefix to use when prefixing the imported target names.
     */
    public function setAs(string $prefix)
    {
        $this->targetPrefix = $prefix;
    }

    /**
     * The separator to use between prefix and target name, default is
     * ".".
     */
    public function setPrefixSeparator(string $s)
    {
        $this->prefixSeparator = $s;
    }

    /**
     * Is this include optional?
     *
     * @param bool $opt If true, do not stop the build if the file does not
     *                  exist
     */
    public function setOptional($opt)
    {
        $this->optional = $opt;
    }

    /**
     * Parse a Phing build file and copy the properties, tasks, data types and
     * targets it defines into the current project.
     *
     * @throws BuildException
     */
    public function main()
    {
        if (null === $this->file && 0 === count($this->filesets)) {
            throw new BuildException(
                'import requires file attribute or at least one nested fileset'
            );
        }
        if (null === $this->getOwningTarget() || '' !== $this->getOwningTarget()->getName()) {
            throw new BuildException('import only allowed as a top-level task');
        }
        if (null === $this->getLocation() || null === $this->getLocation()->getFileName()) {
            throw new BuildException('Unable to get location of import task');
        }

        // Single file.
        if (null !== $this->file) {
            $file = new File($this->file);
            if (!$file->isAbsolute()) {
                $file = new File($this->project->getBasedir(), $this->file);
            }
            if (!$file->exists()) {
                $msg = "Unable to find build file: {$file->getPath()}";
                if ($this->optional) {
                    $this->log($msg . '... skipped');

                    return;
                }

                throw new BuildException($msg);
            }
            $this->importFile($file);
        }

        // Filesets.
        $total_files = 0;
        $total_dirs = 0;
        /** @var FileSet $fs */
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($this->project);
            $fromDir = $fs->getDir($this->project);

            $srcFiles = $ds->getIncludedFiles();
            $srcDirs = $ds->getIncludedDirectories();

            $filecount = count($srcFiles);
            $total_files += $filecount;
            for ($j = 0; $j < $filecount; ++$j) {
                $this->importFile(new File($fromDir, $srcFiles[$j]));
            }

            $dircount = count($srcDirs);
            $total_dirs += $dircount;
            for ($j = 0; $j < $dircount; ++$j) {
                $this->importFile(new File($fromDir, $srcDirs[$j]));
            }
        }
    }

    /**
     * Parse a Phing build file and copy the properties, tasks, data types and
     * targets it defines into the current project.
     *
     * @throws BuildException
     */
    protected function importFile(File $file)
    {
        /** @var XmlContext $ctx */
        $ctx = $this->project->getReference(ProjectConfigurator::PARSING_CONTEXT_REFERENCE);
        $cfg = $ctx->getConfigurator();
        // Import xml file into current project scope
        // Since this is delayed until after the importing file has been
        // processed, the properties and targets of this new file may not take
        // effect if they have alreday been defined in the outer scope.
        $this->log(
            "Importing file {$file->getAbsolutePath()} from "
            . $this->getLocation()->getFileName(),
            Project::MSG_VERBOSE
        );
        ProjectConfigurator::configureProject($this->project, $file);
    }

    /**
     * Whether the task is in include (as opposed to import) mode.
     *
     * <p>In include mode included targets are only known by their
     * prefixed names and their depends lists get rewritten so that
     * all dependencies get the prefix as well.</p>
     *
     * <p>In import mode imported targets are known by an adorned as
     * well as a prefixed name and the unadorned target may be
     * overwritten in the importing build file.  The depends list of
     * the imported targets is not modified at all.</p>
     */
    protected function isInIncludeMode(): bool
    {
        return 'include' === $this->getTaskType();
    }
}
