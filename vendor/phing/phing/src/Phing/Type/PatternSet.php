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
use Phing\Io\BufferedReader;
use Phing\Io\File;
use Phing\Io\FileReader;
use Phing\Io\IOException;
use Phing\Project;
use Phing\Util\StringHelper;

/**
 * The patternset storage component. Carries all necessary data and methods
 * for the patternset stuff.
 *
 * @author  Andreas Aderhold, andi@binarycloud.com
 */
class PatternSet extends DataType
{
    private $includeList = [];
    private $excludeList = [];
    private $includesFileList = [];
    private $excludesFileList = [];

    public function __toString(): string
    {
        return sprintf(
            'patternSet{ includes: %s  excludes: %s }',
            !empty($this->includeList) ? implode(',', $this->includeList) : 'empty',
            !empty($this->excludeList) ? implode(',', $this->excludeList) : 'empty'
        );
    }

    /**
     * Makes this instance in effect a reference to another PatternSet
     * instance.
     * You must not set another attribute or nest elements inside
     * this element if you make it a reference.
     *
     * @throws BuildException
     */
    public function setRefid(Reference $r)
    {
        if (!empty($this->includeList) || !empty($this->excludeList)) {
            throw $this->tooManyAttributes();
        }
        parent::setRefid($r);
    }

    /**
     * Add a name entry on the include list.
     *
     * @throws BuildException
     *
     * @return PatternSetNameEntry Reference to object
     */
    public function createInclude()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->addPatternToList($this->includeList);
    }

    /**
     * Add a name entry on the include files list.
     *
     * @throws BuildException
     *
     * @return PatternSetNameEntry Reference to object
     */
    public function createIncludesFile()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->addPatternToList($this->includesFileList);
    }

    /**
     * Add a name entry on the exclude list.
     *
     * @throws BuildException
     *
     * @return PatternSetNameEntry Reference to object
     */
    public function createExclude()
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->addPatternToList($this->excludeList);
    }

    /**
     * add a name entry on the exclude files list.
     *
     * @throws BuildException
     *
     * @return PatternSetNameEntry Reference to object
     */
    public function createExcludesFile(): PatternSetNameEntry
    {
        if ($this->isReference()) {
            throw $this->noChildrenAllowed();
        }

        return $this->addPatternToList($this->excludesFileList);
    }

    /**
     * Sets the set of include patterns. Patterns may be separated by a comma
     * or a space.
     *
     * @param null|string $includes the string containing the include patterns
     *
     * @throws BuildException
     */
    public function setIncludes(?string $includes): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if (null !== $includes && '' !== $includes) {
            $tok = strtok($includes, ', ');
            while (false !== $tok) {
                $o = $this->createInclude();
                $o->setName($tok);
                $tok = strtok(', ');
            }
        }
    }

    /**
     * Sets the set of exclude patterns. Patterns may be separated by a comma
     * or a space.
     *
     * @param null|string $excludes the string containing the exclude patterns
     *
     * @throws BuildException
     */
    public function setExcludes(?string $excludes): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        if (null !== $excludes && '' !== $excludes) {
            $tok = strtok($excludes, ', ');
            while (false !== $tok) {
                $o = $this->createExclude();
                $o->setName($tok);
                $tok = strtok(', ');
            }
        }
    }

    /**
     * Sets the name of the file containing the includes patterns.
     *
     * @param File $includesFile file to fetch the include patterns from
     *
     * @throws BuildException
     */
    public function setIncludesFile(File $includesFile): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->createIncludesFile()->setName($includesFile->getPath());
    }

    /**
     * Sets the name of the file containing the excludes patterns.
     *
     * @param File $excludesFile file to fetch the exclude patterns from
     *
     * @throws BuildException
     */
    public function setExcludesFile(File $excludesFile): void
    {
        if ($this->isReference()) {
            throw $this->tooManyAttributes();
        }
        $this->createExcludesFile()->setName($excludesFile->getPath());
    }

    /**
     * Adds the patterns of the other instance to this set.
     *
     * @throws IOException
     */
    public function append(PatternSet $other, Project $p): void
    {
        if ($this->isReference()) {
            throw new BuildException('Cannot append to a reference');
        }

        $incl = $other->getIncludePatterns($p);
        if (null !== $incl) {
            foreach ($incl as $incl_name) {
                $this->createInclude()->setName($incl_name);
            }
        }

        $excl = $other->getExcludePatterns($p);
        if (null !== $excl) {
            foreach ($excl as $excl_name) {
                $this->createExclude()->setName($excl_name);
            }
        }
    }

    /**
     * Returns the filtered include patterns.
     *
     * @throws IOException
     *
     * @return array
     */
    public function getIncludePatterns(Project $p): ?array
    {
        if ($this->isReference()) {
            $o = $this->getRef($p);

            if ($o instanceof self) {
                return $o->getIncludePatterns($p);
            }

            return null;
        }

        $this->readFiles($p);

        return $this->makeArray($this->includeList, $p);
    }

    /**
     * Returns the filtered exclude patterns.
     *
     * @throws IOException
     *
     * @return array
     */
    public function getExcludePatterns(Project $p): ?array
    {
        if ($this->isReference()) {
            $o = $this->getRef($p);

            if ($o instanceof self) {
                return $o->getExcludePatterns($p);
            }

            return null;
        }

        $this->readFiles($p);

        return $this->makeArray($this->excludeList, $p);
    }

    /**
     * helper for FileSet.
     *
     * @return bool
     */
    public function hasPatterns()
    {
        return (bool) count($this->includesFileList) > 0 || count($this->excludesFileList) > 0
            || count($this->includeList) > 0 || count($this->excludeList) > 0;
    }

    /**
     * Performs the check for circular references and returns the
     * referenced PatternSet.
     *
     * @return Reference
     */
    public function getRef(Project $p)
    {
        $dataTypeName = StringHelper::substring(__CLASS__, strrpos(__CLASS__, '\\') + 1);

        return $this->getCheckedRef(__CLASS__, $dataTypeName);
    }

    /**
     * add a name entry to the given list.
     *
     * @param array $list List onto which the nameentry should be added
     *
     * @return PatternSetNameEntry Reference to the created PsetNameEntry instance
     */
    private function addPatternToList(array &$list): PatternSetNameEntry
    {
        $num = array_push($list, new PatternSetNameEntry());

        return $list[$num - 1];
    }

    /**
     * Reads path matching patterns from a file and adds them to the
     * includes or excludes list.
     *
     * @throws IOException
     */
    private function readPatterns(File $patternfile, array &$patternlist, Project $p): void
    {
        $patternReader = null;

        try {
            // Get a FileReader
            $patternReader = new BufferedReader(new FileReader($patternfile));

            // Create one NameEntry in the appropriate pattern list for each
            // line in the file.
            $line = $patternReader->readLine();
            while (null !== $line) {
                if (!empty($line)) {
                    $line = $p->replaceProperties($line);
                    $this->addPatternToList($patternlist)->setName($line);
                }
                $line = $patternReader->readLine();
            }
        } catch (IOException $ioe) {
            $msg = 'An error occurred while reading from pattern file: ' . $patternfile->__toString();
            if ($patternReader) {
                $patternReader->close();
            }

            throw new BuildException($msg, $ioe);
        }

        $patternReader->close();
    }

    /**
     * Convert a array of PatternSetNameEntry elements into an array of Strings.
     *
     * @return array
     */
    private function makeArray(array $list, Project $p): ?array
    {
        if (0 === count($list)) {
            return null;
        }

        $tmpNames = [];
        foreach ($list as $ne) {
            $pattern = (string) $ne->evalName($p);
            if (null !== $pattern && '' !== $pattern) {
                $tmpNames[] = $pattern;
            }
        }

        return $tmpNames;
    }

    /**
     * Read includesfile or excludesfile if not already done so.
     *
     * @throws IOException
     */
    private function readFiles(Project $p): void
    {
        if (!empty($this->includesFileList)) {
            foreach ($this->includesFileList as $ne) {
                $fileName = (string) $ne->evalName($p);
                if (null !== $fileName) {
                    $inclFile = $p->resolveFile($fileName);
                    if (!$inclFile->exists()) {
                        throw new BuildException('Includesfile ' . $inclFile->getAbsolutePath() . ' not found.');
                    }
                    $this->readPatterns($inclFile, $this->includeList, $p);
                }
            }
            $this->includesFileList = [];
        }

        if (!empty($this->excludesFileList)) {
            foreach ($this->excludesFileList as $ne) {
                $fileName = (string) $ne->evalName($p);
                if (null !== $fileName) {
                    $exclFile = $p->resolveFile($fileName);
                    if (!$exclFile->exists()) {
                        throw new BuildException('Excludesfile ' . $exclFile->getAbsolutePath() . ' not found.');
                    }
                    $this->readPatterns($exclFile, $this->excludeList, $p);
                }
            }
            $this->excludesFileList = [];
        }
    }
}
