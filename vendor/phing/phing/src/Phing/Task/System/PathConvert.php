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
use Phing\Io\FileUtils;
use Phing\Io\IOException;
use Phing\Mapper\IdentityMapper;
use Phing\Project;
use Phing\Task;
use Phing\Type\DirSet;
use Phing\Type\FileList;
use Phing\Type\FileSet;
use Phing\Type\Mapper;
use Phing\Type\Path;
use Phing\Type\Reference;

/**
 * Converts path and classpath information to a specific target OS
 * format. The resulting formatted path is placed into the specified property.
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class PathConvert extends Task
{
    /**
     * Set if we're running on windows.
     */
    public $onWindows = false;

    public $from;
    public $to;
    // Members
    /**
     * Path to be converted.
     */
    private $path;
    /**
     * Reference to path/fileset to convert.
     *
     * @var Reference
     */
    private $refid;
    /**
     * The target OS type.
     */
    private $targetOS;
    /**
     * Set when targetOS is set to windows.
     */
    private $targetWindows = false;
    /**
     * Set if we should create a new property even if the result is empty.
     */
    private $setonempty = true;
    /**
     * The property to receive the conversion.
     */
    private $property;
    /**
     * Path prefix map.
     *
     * @var MapEntry[]
     */
    private $prefixMap = [];
    /**
     * User override on path sep char.
     */
    private $pathSep;
    /**
     * User override on directory sep char.
     */
    private $dirSep;
    private $mapper;
    private $preserveDuplicates = false;

    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->onWindows = 0 === strncasecmp(PHP_OS, 'WIN', 3);
    }

    /**
     * Create a nested PATH element.
     */
    public function createPath()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        if (null === $this->path) {
            $this->path = new Path($this->getProject());
        }

        return $this->path->createPath();
    }

    /**
     * Create a nested MAP element.
     *
     * @return MapEntry a Map to configure
     */
    public function createMap()
    {
        $entry = new MapEntry($this);

        $this->prefixMap[] = $entry;

        return $entry;
    }

    /**
     * Set targetos to a platform to one of
     * "windows", "unix", "netware", or "os/2"; required unless
     * unless pathsep and/or dirsep are specified.
     *
     * @param mixed $target
     */
    public function setTargetos($target)
    {
        $this->targetOS = $target;
        $this->targetWindows = 'unix' !== $this->targetOS;
    }

    /**
     * Set setonempty.
     *
     * If false, don't set the new property if the result is the empty string.
     *
     * @param bool $setonempty true or false
     */
    public function setSetonempty($setonempty)
    {
        $this->setonempty = $setonempty;
    }

    /**
     * The property into which the converted path will be placed.
     *
     * @param mixed $p
     */
    public function setProperty($p)
    {
        $this->property = $p;
    }

    /**
     * Adds a reference to a Path, FileSet, DirSet, or FileList defined
     * elsewhere.
     *
     * @throws BuildException
     */
    public function setRefid(Reference $r)
    {
        if (null !== $this->path) {
            throw $this->noChildrenAllowed();
        }

        $this->refid = $r;
    }

    /**
     * Set the default path separator string;
     * defaults to current JVM.
     *
     * @param string $sep path separator string
     */
    public function setPathSep($sep)
    {
        $this->pathSep = $sep;
    }

    /**
     * Set the default directory separator string.
     *
     * @param string $sep directory separator string
     */
    public function setDirSep($sep)
    {
        $this->dirSep = $sep;
    }

    /**
     * Has the refid attribute of this element been set?
     *
     * @return true if refid is valid
     */
    public function isReference()
    {
        return null !== $this->refid;
    }

    /**
     * Do the execution.
     *
     * @throws BuildException if something is invalid
     */
    public function main()
    {
        $savedPath = $this->path;
        $savedPathSep = $this->pathSep; // may be altered in validateSetup
        $savedDirSep = $this->dirSep; // may be altered in validateSetup

        try {
            // If we are a reference, create a Path from the reference
            if ($this->isReference()) {
                $this->path = new Path($this->getProject());
                $this->path = $this->path->createPath();

                $obj = $this->refid->getReferencedObject($this->getProject());

                if ($obj instanceof Path) {
                    $this->path->setRefid($this->refid);
                } elseif ($obj instanceof FileSet) {
                    $fs = $obj;

                    $this->path->addFileset($fs);
                } elseif ($obj instanceof DirSet) {
                    $ds = $obj;

                    $this->path->addDirset($ds);
                } elseif ($obj instanceof FileList) {
                    $fl = $obj;

                    $this->path->addFilelist($fl);
                } else {
                    throw new BuildException(
                        "'refid' does not refer to a "
                        . 'path, fileset, dirset, or '
                        . 'filelist.'
                    );
                }
            }

            $this->validateSetup(); // validate our setup

            // Currently, we deal with only two path formats: Unix and Windows
            // And Unix is everything that is not Windows
            // (with the exception for NetWare and OS/2 below)

            // for NetWare and OS/2, piggy-back on Windows, since here and
            // in the apply code, the same assumptions can be made as with
            // windows - that \\ is an OK separator, and do comparisons
            // case-insensitive.
            $fromDirSep = $this->onWindows ? '\\' : '/';

            $rslt = '';

            // Get the list of path components in canonical form
            $elems = $this->path->listPaths($this->isPreserveDuplicates());

            $mapperImpl = null === $this->mapper ? new IdentityMapper() : $this->mapper->getImplementation();
            foreach ($elems as &$elem) {
                $mapped = $mapperImpl->main($elem);
                for ($m = 0; null !== $mapped && $m < count($mapped); ++$m) {
                    $elem = $mapped[$m];
                }
            }
            unset($elem);
            foreach ($elems as $key => $elem) {
                $elem = $this->mapElement($elem); // Apply the path prefix map

                // Now convert the path and file separator characters from the
                // current os to the target os.

                if (0 !== $key) {
                    $rslt .= $this->pathSep;
                }

                $rslt .= str_replace($fromDirSep, $this->dirSep, $elem);
            }

            // Place the result into the specified property,
            // unless setonempty == false
            $value = $rslt;
            if ($this->setonempty) {
                $this->log(
                    'Set property ' . $this->property . ' = ' . $value,
                    Project::MSG_VERBOSE
                );
                $this->getProject()->setNewProperty($this->property, $value);
            } else {
                if ('' !== $rslt) {
                    $this->log(
                        'Set property ' . $this->property . ' = ' . $value,
                        Project::MSG_VERBOSE
                    );
                    $this->getProject()->setNewProperty($this->property, $value);
                }
            }
        } finally {
            $this->path = $savedPath;
            $this->dirSep = $savedDirSep;
            $this->pathSep = $savedPathSep;
        }
    }

    /**
     * @throws BuildException
     * @throws IOException
     */
    public function createMapper()
    {
        if (null !== $this->mapper) {
            throw new BuildException('Cannot define more than one mapper', $this->getLocation());
        }
        $this->mapper = new Mapper($this->project);

        return $this->mapper;
    }

    /**
     * Get the preserveDuplicates.
     */
    public function isPreserveDuplicates(): bool
    {
        return $this->preserveDuplicates;
    }

    public function setPreserveDuplicates(bool $preserveDuplicates): void
    {
        $this->preserveDuplicates = $preserveDuplicates;
    }

    /**
     * Apply the configured map to a path element. The map is used to convert
     * between Windows drive letters and Unix paths. If no map is configured,
     * then the input string is returned unchanged.
     *
     * @param string $elem The path element to apply the map to
     *
     * @return string Updated element
     */
    private function mapElement($elem)
    {
        $size = count($this->prefixMap);

        if (0 !== $size) {
            // Iterate over the map entries and apply each one.
            // Stop when one of the entries actually changes the element.

            foreach ($this->prefixMap as $entry) {
                $newElem = $entry->apply((string) $elem);

                // Note I'm using "!=" to see if we got a new object back from
                // the apply method.

                if ($newElem !== (string) $elem) {
                    $elem = $newElem;

                    break; // We applied one, so we're done
                }
            }
        }

        return $elem;
    }

    /**
     * Validate that all our parameters have been properly initialized.
     *
     * @throws BuildException if something is not setup properly
     */
    private function validateSetup()
    {
        if (null === $this->path) {
            throw new BuildException('You must specify a path to convert');
        }

        // Determine the separator strings.  The dirsep and pathsep attributes
        // override the targetOS settings.
        $dsep = FileUtils::getSeparator();
        $psep = FileUtils::getPathSeparator();

        if (null !== $this->targetOS) {
            $psep = $this->targetWindows ? ';' : ':';
            $dsep = $this->targetWindows ? '\\' : '/';
        }

        if (null !== $this->pathSep) {// override with pathsep=
            $psep = $this->pathSep;
        }

        if (null !== $this->dirSep) {// override with dirsep=
            $dsep = $this->dirSep;
        }

        $this->pathSep = $psep;
        $this->dirSep = $dsep;
    }

    /**
     * Creates an exception that indicates that this XML element must not have
     * child elements if the refid attribute is set.
     */
    private function noChildrenAllowed()
    {
        return new BuildException(
            'You must not specify nested <path> '
            . 'elements when using the refid attribute.'
        );
    }
}
