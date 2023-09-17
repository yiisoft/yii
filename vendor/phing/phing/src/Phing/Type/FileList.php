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

use ArrayIterator;
use Exception;
use IteratorAggregate;
use Phing\Exception\BuildException;
use Phing\Io\BufferedReader;
use Phing\Io\File;
use Phing\Io\FileReader;
use Phing\Io\IOException;
use Phing\Project;
use ReturnTypeWillChange;

/**
 * FileList represents an explicitly named list of files. FileLists
 * are useful when you want to capture a list of files regardless of
 * whether they currently exist.
 *
 * <filelist
 *    id="docfiles"
 *   dir="${phing.docs.dir}"
 *   files="chapters/Installation.html,chapters/Setup.html"/>
 *
 * OR
 *
 * <filelist
 *         dir="${doc.src.dir}"
 *         listfile="${phing.docs.dir}/PhingGuide.book"/>
 *
 * (or a mixture of files="" and listfile="" can be used)
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class FileList extends DataType implements IteratorAggregate
{
    // public for "cloning" purposes

    /**
     * Array containing all filenames.
     */
    public $filenames = [];

    /**
     * Base directory for this file list.
     */
    public $dir;

    /**
     * @var File that contains a list of files (one per line)
     */
    public $listfile;

    /**
     * Construct a new FileList.
     *
     * @param FileList $filelist
     */
    public function __construct($filelist = null)
    {
        parent::__construct();

        if (null !== $filelist) {
            $this->dir = $filelist->dir;
            $this->filenames = $filelist->filenames;
            $this->listfile = $filelist->listfile;
        }
    }

    #[ReturnTypeWillChange]
    public function getIterator()
    {
        return new ArrayIterator($this->getFiles($this->getProject()));
    }

    /**
     * Makes this instance in effect a reference to another FileList
     * instance.
     *
     * @throws BuildException
     */
    public function setRefid(Reference $r)
    {
        if (null !== $this->dir || 0 !== count($this->filenames)) {
            throw $this->tooManyAttributes();
        }
        parent::setRefid($r);
    }

    /**
     * Base directory for files in list.
     *
     * @throws IOException
     * @throws \InvalidArgumentException
     */
    public function setDir(File $dir)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->dir = $dir;
    }

    /**
     * Get the basedir for files in list.
     *
     * @throws BuildException
     *
     * @return File
     */
    public function getDir(Project $p)
    {
        if ($this->isReference()) {
            $ref = $this->getRef($p);

            return $ref->getDir($p);
        }

        return $this->dir;
    }

    /**
     * Set the array of files in list.
     *
     * @param array $filenames
     *
     * @throws BuildException
     */
    public function setFiles($filenames)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if (!empty($filenames)) {
            $tok = strtok($filenames, ", \t\n\r");
            while (false !== $tok) {
                $fname = trim($tok);
                if ('' !== $fname) {
                    $this->filenames[] = $tok;
                }
                $tok = strtok(", \t\n\r");
            }
        }
    }

    /**
     * Sets a source "list" file that contains filenames to add -- one per line.
     *
     * @param string $file
     *
     * @throws IOException
     * @throws \InvalidArgumentException
     */
    public function setListFile($file)
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if (!($file instanceof File)) {
            $file = new File($file);
        }
        $this->listfile = $file;
    }

    /**
     * Get the source "list" file that contains file names.
     *
     * @return File
     */
    public function getListFile(Project $p)
    {
        if ($this->isReference()) {
            $ref = $this->getRef($p);

            return $ref->getListFile($p);
        }

        return $this->listfile;
    }

    /**
     * Returns the list of files represented by this FileList.
     *
     * @throws IOException
     * @throws BuildException
     *
     * @return array
     */
    public function getFiles(Project $p)
    {
        if ($this->isReference()) {
            $ret = $this->getRef($p);

            return $ret->getFiles($p);
        }

        if (null === $this->dir) {
            throw new BuildException('No directory specified for filelist.');
        }

        if (null !== $this->listfile) {
            $this->readListFile($p);
        }

        if (empty($this->filenames)) {
            throw new BuildException('No files specified for filelist.');
        }

        return $this->filenames;
    }

    /**
     * Performs the check for circular references and returns the
     * referenced FileSet.
     *
     * @throws BuildException
     *
     * @return FileList
     */
    public function getRef(Project $p)
    {
        return $this->getCheckedRef(__CLASS__, $this->getDataTypeName());
    }

    /**
     * Reads file names from a file and adds them to the files array.
     *
     * @throws BuildException
     * @throws IOException
     */
    private function readListFile(Project $p)
    {
        $listReader = null;

        try {
            // Get a FileReader
            $listReader = new BufferedReader(new FileReader($this->listfile));

            $line = $listReader->readLine();
            while (null !== $line) {
                if (!empty($line)) {
                    $line = $p->replaceProperties($line);
                    $this->filenames[] = trim($line);
                }
                $line = $listReader->readLine();
            }
        } catch (Exception $e) {
            if ($listReader) {
                $listReader->close();
            }

            throw new BuildException(
                'An error occurred while reading from list file ' . $this->listfile->__toString() . ': ' . $e->getMessage()
            );
        }

        $listReader->close();
    }
}
