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
use Phing\Io\SourceFileScanner;
use Phing\Mapper\MergeMapper;
use Phing\Project;
use Phing\Task;
use Phing\Task\System\Condition\Condition;
use Phing\Type\Element\FileListAware;
use Phing\Type\Element\FileSetAware;
use Phing\Type\Mapper;

/**
 * Sets the given property if the specified target has a timestamp
 * greater than all of the source files.
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  William Ferguson <williamf@mincom.com> (Ant)
 * @author  Hiroaki Nakamura <hnakamur@mc.neweb.ne.jp> (Ant)
 * @author  Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 */
class UpToDateTask extends Task implements Condition
{
    use FileListAware;
    use FileSetAware;

    protected $mapperElement;

    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $value;

    /**
     * @var File
     */
    private $sourceFile;

    /**
     * @var File
     */
    private $targetFile;

    /**
     * The property to set if the target file is more up-to-date than
     * (each of) the source file(s).
     *
     * @param string $property the name of the property to set if Target is up-to-date
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * Get property name.
     *
     * @return string property the name of the property to set if Target is up-to-date
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * The value to set the named property to if the target file is more
     * up-to-date than (each of) the source file(s). Defaults to 'true'.
     *
     * @param mixed $value the value to set the property to if Target is up-to-date
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * The file which must be more up-to-date than (each of) the source file(s)
     * if the property is to be set.
     *
     * @param File|string $file the file we are checking against
     */
    public function setTargetFile($file)
    {
        if (is_string($file)) {
            $file = new File($file);
        }
        $this->targetFile = $file;
    }

    /**
     * The file that must be older than the target file
     * if the property is to be set.
     *
     * @param File|string $file the file we are checking against the target file
     */
    public function setSrcfile($file)
    {
        if (is_string($file)) {
            $file = new File($file);
        }
        $this->sourceFile = $file;
    }

    /**
     * Defines the FileNameMapper to use (nested mapper element).
     */
    public function createMapper()
    {
        if (null !== $this->mapperElement) {
            throw new BuildException(
                'Cannot define more than one mapper',
                $this->getLocation()
            );
        }
        $this->mapperElement = new Mapper($this->getProject());

        return $this->mapperElement;
    }

    /**
     * Evaluate (all) target and source file(s) to
     * see if the target(s) is/are up-to-date.
     *
     * @throws BuildException
     *
     * @return bool
     */
    public function evaluate()
    {
        if (0 == count($this->filesets) && 0 == count($this->filelists) && null === $this->sourceFile) {
            throw new BuildException(
                'At least one srcfile or a nested '
                . '<fileset> or <filelist> element must be set.'
            );
        }

        if ((count($this->filesets) > 0 || count($this->filelists) > 0) && null !== $this->sourceFile) {
            throw new BuildException(
                'Cannot specify both the srcfile '
                . 'attribute and a nested <fileset> '
                . 'or <filelist> element.'
            );
        }

        if (null === $this->targetFile && null === $this->mapperElement) {
            throw new BuildException(
                'The targetfile attribute or a nested '
                . 'mapper element must be set.'
            );
        }

        // if the target file is not there, then it can't be up-to-date
        if (null !== $this->targetFile && !$this->targetFile->exists()) {
            return false;
        }

        // if the source file isn't there, throw an exception
        if (null !== $this->sourceFile && !$this->sourceFile->exists()) {
            throw new BuildException(
                $this->sourceFile->getAbsolutePath()
                . ' not found.'
            );
        }

        $upToDate = true;
        for ($i = 0, $size = count($this->filesets); $i < $size && $upToDate; ++$i) {
            $fs = $this->filesets[$i];
            $ds = $fs->getDirectoryScanner($this->project);
            $upToDate = $upToDate && $this->scanDir(
                $fs->getDir($this->project),
                $ds->getIncludedFiles()
            );
        }

        for ($i = 0, $size = count($this->filelists); $i < $size && $upToDate; ++$i) {
            $fl = $this->filelists[$i];
            $srcFiles = $fl->getFiles($this->project);
            $upToDate = $upToDate && $this->scanDir(
                $fl->getDir($this->project),
                $srcFiles
            );
        }

        if (null !== $this->sourceFile) {
            if (null === $this->mapperElement) {
                $upToDate = $upToDate
                    && ($this->targetFile->lastModified() >= $this->sourceFile->lastModified());
            } else {
                $sfs = new SourceFileScanner($this);
                $files = [$this->sourceFile->getAbsolutePath()];
                $upToDate = $upToDate
                    && 0 === count(
                        $sfs->restrict(
                            $files,
                            null,
                            null,
                            $this->mapperElement->getImplementation()
                        )
                    );
            }
        }

        return $upToDate;
    }

    /**
     * Sets property to true if target file(s) have a more recent timestamp
     * than (each of) the corresponding source file(s).
     *
     * @throws BuildException
     */
    public function main()
    {
        if (null === $this->property) {
            throw new BuildException(
                'property attribute is required.',
                $this->getLocation()
            );
        }
        $upToDate = $this->evaluate();
        if ($upToDate) {
            /**
             * @var PropertyTask
             */
            $property = $this->project->createTask('property');
            $property->setName($this->getProperty());
            $property->setValue($this->getValue());
            $property->setOverride(true);
            $property->main(); // execute

            if (null === $this->mapperElement) {
                $this->log(
                    'File "' . $this->targetFile->getAbsolutePath()
                    . '" is up-to-date.',
                    Project::MSG_VERBOSE
                );
            } else {
                $this->log(
                    'All target files are up-to-date.',
                    Project::MSG_VERBOSE
                );
            }
        }
    }

    /**
     * @param array $files
     *
     * @return bool
     */
    protected function scanDir(File $srcDir, $files)
    {
        $sfs = new SourceFileScanner($this);
        $mapper = null;
        $dir = $srcDir;
        if (null === $this->mapperElement) {
            $mm = new MergeMapper();
            $mm->setTo($this->targetFile->getAbsolutePath());
            $mapper = $mm;
            $dir = null;
        } else {
            $mapper = $this->mapperElement->getImplementation();
        }

        return 0 === count($sfs->restrict($files, $srcDir, $dir, $mapper));
    }

    /**
     * Returns the value, or "true" if a specific value wasn't provided.
     */
    private function getValue()
    {
        return $this->value ?? 'true';
    }
}
