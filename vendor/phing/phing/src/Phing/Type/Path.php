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

namespace Phing\Type;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\IOException;
use Phing\Project;
use Phing\Util\PathTokenizer;

/**
 * This object represents a path as used by include_path or PATH
 * environment variable.
 *
 * This class has been adopted from the Java Ant equivalent.  The ability have
 * path structures in Phing is important; however, because of how PHP classes interact
 * the ability to specify CLASSPATHs makes less sense than Java.Rather than providing
 * CLASSPATH for any tasks that take classes as parameters, perhaps a better
 * solution in PHP is to have an IncludePath task, which prepends paths to PHP's include_path
 * INI variable. This gets around the problem that simply using a path to load the initial
 * PHP class is not enough (in most cases the loaded class may assume that it is on the global
 * PHP include_path, and will try to load dependent classes accordingly).  The other option is
 * to provide a way for this class to add paths to the include path, if desired -- or to create
 * an IncludePath subclass.  Once added, though, when would a path be removed from the include path?
 *
 * <p>
 * <code>
 * &lt;sometask&gt;<br>
 * &nbsp;&nbsp;&lt;somepath&gt;<br>
 * &nbsp;&nbsp;&nbsp;&nbsp;&lt;pathelement location="/path/to/file" /&gt;<br>
 * &nbsp;&nbsp;&nbsp;&nbsp;&lt;pathelement path="/path/to/class2;/path/to/class3" /&gt;<br>
 * &nbsp;&nbsp;&nbsp;&nbsp;&lt;pathelement location="/path/to/file3" /&gt;<br>
 * &nbsp;&nbsp;&lt;/somepath&gt;<br>
 * &lt;/sometask&gt;<br>
 * </code>
 * <p>
 * The object implemention <code>sometask</code> must provide a method called
 * <code>createSomepath</code> which returns an instance of <code>Path</code>.
 * Nested path definitions are handled by the Path object and must be labeled
 * <code>pathelement</code>.<p>
 *
 * The path element takes a parameter <code>path</code> which will be parsed
 * and split into single elements. It will usually be used
 * to define a path from an environment variable.
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Thomas.Haas@softwired-inc.com (Ant)
 * @author  Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 */
class Path extends DataType
{
    private $elements = [];

    /**
     * Constructor for internally instantiated objects sets project.
     *
     * @param Project $project
     * @param string  $path    (for use by IntrospectionHelper)
     */
    public function __construct($project = null, $path = null)
    {
        parent::__construct();
        if (null !== $project) {
            $this->setProject($project);
        }
        if (null !== $path) {
            $this->createPathElement()->setPath($path);
        }
    }

    /**
     * Returns a textual representation of the path, which can be used as
     * CLASSPATH or PATH environment variable definition.
     *
     * @return string a textual representation of the path
     */
    public function __toString()
    {
        $list = $this->listPaths();

        // empty path return empty string
        if (empty($list)) {
            return '';
        }

        return implode(PATH_SEPARATOR, $list);
    }

    /**
     * Adds a element definition to the path.
     *
     * @param File $location the location of the element to add (must not be
     *                       <code>null</code> nor empty
     *
     * @throws BuildException
     */
    public function setDir(File $location)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->createPathElement()->setDir($location);
    }

    /**
     * Parses a path definition and creates single PathElements.
     *
     * @param string $path the path definition
     *
     * @throws BuildException
     */
    public function setPath($path)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->createPathElement()->setPath($path);
    }

    /**
     * Makes this instance in effect a reference to another Path instance.
     *
     * <p>You must not set another attribute or nest elements inside
     * this element if you make it a reference.</p>
     *
     * @throws BuildException
     */
    public function setRefid(Reference $r)
    {
        if (!empty($this->elements)) {
            throw $this->tooManyAttributes();
        }
        $this->elements[] = $r;
        parent::setRefid($r);
    }

    /**
     * Creates the nested <code>&lt;pathelement&gt;</code> element.
     *
     * @throws BuildException
     *
     * @return PathElement
     */
    public function createPathElement()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        $pe = new PathElement($this);
        $this->elements[] = $pe;

        return $pe;
    }

    /**
     * Adds a nested <code>&lt;filelist&gt;</code> element.
     *
     * @throws BuildException
     */
    public function addFilelist(FileList $fl)
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        $this->elements[] = $fl;
        $this->checked = false;
    }

    /**
     * Adds a nested <code>&lt;fileset&gt;</code> element.
     *
     * @throws BuildException
     */
    public function addFileset(FileSet $fs)
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        $this->elements[] = $fs;
        $this->checked = false;
    }

    /**
     * Adds a nested <code>&lt;dirset&gt;</code> element.
     *
     * @throws BuildException
     */
    public function addDirset(DirSet $dset)
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        $this->elements[] = $dset;
        $this->checked = false;
    }

    /**
     * Creates a nested <code>&lt;path&gt;</code> element.
     *
     * @throws BuildException
     *
     * @return Path
     */
    public function createPath()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }
        $p = new Path($this->project);
        $this->elements[] = $p;
        $this->checked = false;

        return $p;
    }

    /**
     * Append the contents of the other Path instance to this.
     *
     * @throws BuildException
     */
    public function append(Path $other)
    {
        if (null === $other) {
            return;
        }
        $l = $other->listPaths();
        foreach ($l as $path) {
            if (!in_array($path, $this->elements, true)) {
                $this->elements[] = $path;
            }
        }
    }

    /**
     * Adds the components on the given path which exist to this
     * Path. Components that don't exist, aren't added.
     *
     * @param path $source - Source path whose components are examined for existence
     */
    public function addExisting(Path $source)
    {
        $list = $source->listPaths();
        foreach ($list as $el) {
            $f = null;
            if (null !== $this->project) {
                $f = $this->project->resolveFile($el);
            } else {
                $f = new File($el);
            }

            if ($f->exists()) {
                $this->setDir($f);
            } else {
                $this->log(
                    'dropping ' . $f->__toString() . " from path as it doesn't exist",
                    Project::MSG_VERBOSE
                );
            }
        }
    }

    /**
     * Returns all path elements defined by this and nested path objects.
     *
     * @param bool $preserveDuplicates
     *
     * @throws IOException
     * @throws \InvalidArgumentException
     *
     * @return array list of path elements
     */
    public function listPaths($preserveDuplicates = false)
    {
        if (!$this->checked) {
            // make sure we don't have a circular reference here
            $stk = [];
            $stk[] = $this;
            $this->dieOnCircularReference($stk, $this->project);
        }

        $result = [];
        for ($i = 0, $elSize = count($this->elements); $i < $elSize; ++$i) {
            $o = $this->elements[$i];
            if ($o instanceof Reference) {
                $refId = $o->getRefId();
                $o = $o->getReferencedObject($this->project);
                // we only support references to paths right now
                if (!($o instanceof Path)) {
                    $msg = $refId . " doesn't denote a path";

                    throw new BuildException($msg);
                }
            }

            if (is_string($o)) {
                $result[] = $o;
            } elseif ($o instanceof PathElement) {
                $parts = $o->getParts();
                if (null === $parts) {
                    throw new BuildException(
                        'You must either set location or'
                        . ' path on <pathelement>'
                    );
                }
                foreach ($parts as $part) {
                    $result[] = $part;
                }
            } elseif ($o instanceof Path) {
                $p = $o;
                if (null === $p->getProject()) {
                    $p->setProject($this->getProject());
                }
                $parts = $p->listPaths();
                foreach ($parts as $part) {
                    $result[] = $part;
                }
            } elseif ($o instanceof DirSet) {
                $dset = $o;
                $ds = $dset->getDirectoryScanner($this->project);
                $dirstrs = $ds->getIncludedDirectories();
                $dir = $dset->getDir($this->project);
                foreach ($dirstrs as $dstr) {
                    $d = new File($dir, $dstr);
                    $result[] = $d->getAbsolutePath();
                }
            } elseif ($o instanceof FileSet) {
                $fs = $o;
                $ds = $fs->getDirectoryScanner($this->getProject());
                $filestrs = $ds->getIncludedFiles();
                $dir = $fs->getDir($this->getProject());
                foreach ($filestrs as $fstr) {
                    $d = new File($dir, $fstr);
                    $result[] = $d->getAbsolutePath();
                }
            } elseif ($o instanceof FileList) {
                $fl = $o;
                $dirstrs = $fl->getFiles($this->project);
                $dir = $fl->getDir($this->project);
                foreach ($dirstrs as $dstr) {
                    $d = new File($dir, $dstr);
                    $result[] = $d->getAbsolutePath();
                }
            }
        }

        return $preserveDuplicates ? $result : array_unique($result);
    }

    /**
     * Splits a PATH (with : or ; as separators) into its parts.
     *
     * @param string $source
     *
     * @return array
     */
    public static function translatePath(Project $project, $source)
    {
        if (null == $source) {
            return [];
        }

        $result = [];
        $tok = new PathTokenizer($source);
        while ($tok->hasMoreTokens()) {
            $pathElement = $tok->nextToken();

            try {
                $element = self::resolveFile($project, $pathElement);
                for ($i = 0, $_i = strlen($element); $i < $_i; ++$i) {
                    self::translateFileSep($element, $i);
                }
                $result[] = $element;
            } catch (BuildException $e) {
                $project->log(
                    'Dropping path element ' . $pathElement
                    . ' as it is not valid relative to the project',
                    Project::MSG_VERBOSE
                );
            }
        }

        return $result;
    }

    /**
     * Returns its argument with all file separator characters
     * replaced so that they match the local OS conventions.
     *
     * @param string $source
     *
     * @return string
     */
    public static function translateFile($source)
    {
        if (null == $source) {
            return '';
        }

        $result = $source;
        for ($i = 0, $_i = strlen($source); $i < $_i; ++$i) {
            self::translateFileSep($result, $i);
        }

        return $result;
    }

    /**
     * How many parts does this Path instance consist of.
     * DEV NOTE: expensive call! list is generated, counted, and then
     * discarded.
     *
     * @return int
     */
    public function size()
    {
        return count($this->listPaths());
    }

    /**
     * Overrides the version of DataType to recurse on all DataType
     * child elements that may have been added.
     *
     * @param array   $stk
     * @param Project $p
     *
     * @throws BuildException
     */
    public function dieOnCircularReference(&$stk, Project $p = null)
    {
        if ($this->checked) {
            return;
        }

        // elements can contain strings, FileSets, Reference, etc.
        foreach ($this->elements as $o) {
            if ($o instanceof Reference) {
                $o = $o->getReferencedObject($p);
            }

            if ($o instanceof DataType) {
                if (in_array($o, $stk, true)) {
                    throw $this->circularReference();
                }

                $stk[] = $o;
                $o->dieOnCircularReference($stk, $p);
                array_pop($stk);
            }
        }

        $this->checked = true;
    }

    /**
     * Translates all occurrences of / or \ to correct separator of the
     * current platform and returns whether it had to do any
     * replacements.
     *
     * @param string $buffer
     * @param int    $pos
     *
     * @return bool
     */
    protected static function translateFileSep(&$buffer, $pos)
    {
        if ('/' == $buffer[$pos] || '\\' == $buffer[$pos]) {
            $buffer[$pos] = DIRECTORY_SEPARATOR;

            return true;
        }

        return false;
    }

    /**
     * Resolve a filename with Project's help - if we know one that is.
     *
     * <p>Assume the filename is absolute if project is null.</p>
     *
     * @param string $relativeName
     *
     * @return string
     */
    private static function resolveFile(Project $project, $relativeName)
    {
        if (null !== $project) {
            $f = $project->resolveFile($relativeName);

            return $f->getAbsolutePath();
        }

        return $relativeName;
    }
}
