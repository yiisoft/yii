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
use Phing\Io\DirectoryScanner;
use Phing\Io\File;
use Phing\Io\FileSystem;
use Phing\Io\IOException;
use Phing\Mapper\FileNameMapper;
use Phing\Project;
use Phing\Task;
use Phing\Type\Element\ResourceAware;
use Phing\Type\Mapper;
use Phing\Type\Path;

/**
 * <foreach> task.
 *
 * Task definition for the foreach task.  This task takes a list with
 * delimited values, and executes a target with set param.
 *
 * Usage:
 * <foreach list="values" target="targ" param="name" delimiter="|" />
 *
 * Attributes:
 * list      --> The list of values to process, with the delimiter character,
 *               indicated by the "delimiter" attribute, separating each value.
 * target    --> The target to call for each token, passing the token as the
 *               parameter with the name indicated by the "param" attribute.
 * param     --> The name of the parameter to pass the tokens in as to the
 *               target.
 * delimiter --> The delimiter string that separates the values in the "list"
 *               parameter.  The default is ",".
 *
 * @author  Jason Hines <jason@greenhell.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class ForeachTask extends Task
{
    use ResourceAware;

    /**
     * Delimter-separated list of values to process.
     */
    private $list;

    /**
     * Name of parameter to pass to callee.
     */
    private $param;

    /**
     * @var PropertyTask[]
     */
    private $params = [];

    /**
     * Name of absolute path parameter to pass to callee.
     */
    private $absparam;

    /**
     * Delimiter that separates items in $list.
     */
    private $delimiter = ',';

    /**
     * PhingCallTask that will be invoked w/ calleeTarget.
     *
     * @var PhingCallTask
     */
    private $callee;

    /**
     * @var Mapper
     */
    private $mapperElement;

    /**
     * Target to execute.
     *
     * @var string
     */
    private $calleeTarget;

    /**
     * Total number of files processed.
     *
     * @var int
     */
    private $total_files = 0;

    /**
     * Total number of directories processed.
     *
     * @var int
     */
    private $total_dirs = 0;

    /**
     * @var bool
     */
    private $trim = false;

    /**
     * @var bool
     */
    private $inheritAll = false;

    /**
     * @var bool
     */
    private $inheritRefs = false;

    /**
     * @var Path
     */
    private $currPath;

    /**
     * @var PhingReference[]
     */
    private $references = [];

    /**
     * @var string
     */
    private $index = 'index';

    /**
     * This method does the work.
     *
     * @throws BuildException
     */
    public function main()
    {
        if (
            null === $this->list
            && null === $this->currPath
            && 0 === count($this->dirsets)
            && 0 === count($this->filesets)
            && 0 === count($this->filelists)
        ) {
            throw new BuildException(
                'Need either list, path, nested dirset, nested fileset or nested filelist to iterate through'
            );
        }
        if (null === $this->param) {
            throw new BuildException('You must supply a property name to set on each iteration in param');
        }
        if (null === $this->calleeTarget) {
            throw new BuildException('You must supply a target to perform');
        }

        $callee = $this->createCallTarget();
        $mapper = null;
        $total_entries = 0;

        if (null !== $this->mapperElement) {
            $mapper = $this->mapperElement->getImplementation();
        }

        if (null !== $this->list) {
            $arr = explode($this->delimiter, $this->list);

            foreach ($arr as $index => $value) {
                if ($this->trim) {
                    $value = trim($value);
                }
                $premapped = '';
                if (null !== $mapper) {
                    $premapped = $value;
                    $value = $mapper->main($value);
                    if (null === $value) {
                        continue;
                    }
                    $value = array_shift($value);
                }
                $this->log(
                    "Setting param '{$this->param}' to value '{$value}'" . ($premapped ? " (mapped from '{$premapped}')" : ''),
                    Project::MSG_VERBOSE
                );
                $prop = $callee->createProperty();
                $prop->setName($this->param);
                $prop->setValue($value);
                $prop = $callee->createProperty();
                $prop->setName($this->index);
                $prop->setValue($index);
                $callee->main();
                ++$total_entries;
            }
        }

        if (null !== $this->currPath) {
            $pathElements = $this->currPath->listPaths();
            foreach ($pathElements as $pathElement) {
                $ds = new DirectoryScanner();
                $ds->setBasedir($pathElement);
                $ds->scan();
                $this->process($callee, new File($pathElement), $ds->getIncludedFiles(), []);
            }
        }

        // filelists
        foreach ($this->filelists as $fl) {
            $srcFiles = $fl->getFiles($this->project);

            $this->process($callee, $fl->getDir($this->project), $srcFiles, []);
        }

        // filesets
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($this->project);
            $srcFiles = $ds->getIncludedFiles();
            $srcDirs = $ds->getIncludedDirectories();

            $this->process($callee, $fs->getDir($this->project), $srcFiles, $srcDirs);
        }

        foreach ($this->dirsets as $dirset) {
            $ds = $dirset->getDirectoryScanner($this->project);
            $srcDirs = $ds->getIncludedDirectories();

            $this->process($callee, $dirset->getDir($this->project), [], $srcDirs);
        }

        if (null === $this->list) {
            $this->log(
                "Processed {$this->total_dirs} directories and {$this->total_files} files",
                Project::MSG_VERBOSE
            );
        } else {
            $this->log(
                "Processed {$total_entries} entr" . ($total_entries > 1 ? 'ies' : 'y') . ' in list',
                Project::MSG_VERBOSE
            );
        }
    }

    public function setTrim(string $trim)
    {
        $this->trim = $trim;
    }

    public function setList(string $list)
    {
        $this->list = $list;
    }

    public function setTarget(string $target)
    {
        $this->calleeTarget = $target;
    }

    public function addParam(PropertyTask $param)
    {
        $this->params[] = $param;
    }

    /**
     * Corresponds to <code>&lt;phingcall&gt;</code>'s nested
     * <code>&lt;reference&gt;</code> element.
     */
    public function addReference(PhingReference $r)
    {
        $this->references[] = $r;
    }

    public function setAbsparam(string $absparam)
    {
        $this->absparam = $absparam;
    }

    public function setDelimiter(string $delimiter)
    {
        $this->delimiter = $delimiter;
    }

    public function setIndex($index)
    {
        $this->index = $index;
    }

    public function createPath()
    {
        if (null === $this->currPath) {
            $this->currPath = new Path($this->getProject());
        }

        return $this->currPath;
    }

    /**
     * Nested creator, creates one Mapper for this task.
     *
     * @throws BuildException
     *
     * @return object The created Mapper type object
     */
    public function createMapper()
    {
        if (null !== $this->mapperElement) {
            throw new BuildException('Cannot define more than one mapper', $this->getLocation());
        }
        $this->mapperElement = new Mapper($this->project);

        return $this->mapperElement;
    }

    /**
     * @return PropertyTask
     */
    public function createProperty()
    {
        return $this->callee->createProperty();
    }

    /**
     * @return PropertyTask
     */
    public function createParam()
    {
        return $this->callee->createProperty();
    }

    /**
     * @param string $param
     */
    public function setParam($param)
    {
        $this->param = $param;
    }

    /**
     * Corresponds to <code>&lt;antcall&gt;</code>'s <code>inheritall</code>
     * attribute.
     *
     * @param mixed $b
     */
    public function setInheritall($b)
    {
        $this->inheritAll = $b;
    }

    /**
     * Corresponds to <code>&lt;antcall&gt;</code>'s <code>inheritrefs</code>
     * attribute.
     *
     * @param mixed $b
     */
    public function setInheritrefs($b)
    {
        $this->inheritRefs = $b;
    }

    /**
     * Processes a list of files & directories.
     *
     * @param array $srcFiles
     * @param array $srcDirs
     */
    protected function process(PhingCallTask $callee, File $fromDir, $srcFiles, $srcDirs)
    {
        $mapper = null;

        if (null !== $this->mapperElement) {
            $mapper = $this->mapperElement->getImplementation();
        }

        $filecount = count($srcFiles);
        $this->total_files += $filecount;

        $this->processResources($filecount, $srcFiles, $callee, $fromDir, $mapper);

        $dircount = count($srcDirs);
        $this->total_dirs += $dircount;

        $this->processResources($dircount, $srcDirs, $callee, $fromDir, $mapper);
    }

    /**
     * @param string         $fromDir
     * @param FileNameMapper $mapper
     *
     * @throws IOException
     */
    private function processResources(int $rescount, array $srcRes, PhingCallTask $callee, $fromDir, $mapper)
    {
        for ($j = 0; $j < $rescount; ++$j) {
            $value = $srcRes[$j];
            $premapped = '';

            if ($this->absparam) {
                $prop = $callee->createProperty();
                $prop->setName($this->absparam);
                $prop->setValue($fromDir . FileSystem::getFileSystem()->getSeparator() . $value);
            }

            if (null !== $mapper) {
                $premapped = $value;
                $value = $mapper->main($value);
                if (null === $value) {
                    continue;
                }
                $value = array_shift($value);
            }

            if ($this->param) {
                $this->log(
                    "Setting param '{$this->param}' to value '{$value}'" . ($premapped ? " (mapped from '{$premapped}')" : ''),
                    Project::MSG_VERBOSE
                );
                $prop = $callee->createProperty();
                $prop->setName($this->param);
                $prop->setValue($value);
            }

            $callee->main();
        }
    }

    private function createCallTarget()
    {
        /**
         * @var PhingCallTask $ct
         */
        $ct = $this->getProject()->createTask('phingcall');
        $ct->setOwningTarget($this->getOwningTarget());
        $ct->setTaskName($this->getTaskName());
        $ct->setLocation($this->getLocation());
        $ct->init();
        $ct->setTarget($this->calleeTarget);
        $ct->setInheritAll($this->inheritAll);
        $ct->setInheritRefs($this->inheritRefs);
        foreach ($this->params as $param) {
            $toSet = $ct->createParam();
            $toSet->setName($param->getName());
            if (null !== $param->getValue()) {
                $toSet->setValue($param->getValue());
            }

            if (null != $param->getFile()) {
                $toSet->setFile($param->getFile());
            }
            if (null != $param->getPrefix()) {
                $toSet->setPrefix($param->getPrefix());
            }
            if (null != $param->getRefid()) {
                $toSet->setRefid($param->getRefid());
            }
            if (null != $param->getEnvironment()) {
                $toSet->setEnvironment($param->getEnvironment());
            }
        }

        foreach ($this->references as $ref) {
            $ct->addReference($ref);
        }

        return $ct;
    }
}
