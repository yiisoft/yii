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

namespace Phing\Type\Selector;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Mapper\IdentityMapper;
use Phing\Type\Mapper;

/**
 * Selector that filters files based on whether they are newer than
 * a matching file in another directory tree. It can contain a mapper
 * element, so isn't available as an ExtendSelector (since those
 * parameters can't hold other elements).
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Bruce Atherton <bruce@callenish.com> (Ant)
 */
class DependSelector extends BaseSelector
{
    private $targetdir;
    private $mapperElement;
    private $map;
    private $granularity = 0;

    /**
     * @return string
     */
    public function __toString()
    {
        $buf = '{dependselector targetdir: ';
        if (null === $this->targetdir) {
            $buf .= 'NOT YET SET';
        } else {
            $buf .= $this->targetdir->getName();
        }
        $buf .= ' granularity: ';
        $buf .= $this->granularity;
        if (null !== $this->map) {
            $buf .= ' mapper: ';
            $buf .= (string) $this->map;
        } elseif (null !== $this->mapperElement) {
            $buf .= ' mapper: ';
            $buf .= (string) $this->mapperElement;
        }
        $buf .= '}';

        return $buf;
    }

    /**
     * The name of the file or directory which is checked for out-of-date
     * files.
     *
     * @param File $targetdir the directory to scan looking for files
     */
    public function setTargetdir(File $targetdir)
    {
        $this->targetdir = $targetdir;
    }

    /**
     * Sets the number of milliseconds leeway we will give before we consider
     * a file out of date.
     *
     * @param int $granularity
     */
    public function setGranularity($granularity)
    {
        $this->granularity = (int) $granularity;
    }

    /**
     * Defines the FileNameMapper to use (nested mapper element).
     *
     * @throws BuildException
     */
    public function createMapper()
    {
        if (null !== $this->mapperElement) {
            throw new BuildException('Cannot define more than one mapper');
        }
        $this->mapperElement = new Mapper($this->project);

        return $this->mapperElement;
    }

    /**
     * Checks to make sure all settings are kosher. In this case, it
     * means that the dest attribute has been set and we have a mapper.
     */
    public function verifySettings()
    {
        if (null === $this->targetdir) {
            $this->setError('The targetdir attribute is required.');
        }
        if (null === $this->mapperElement) {
            $this->map = new IdentityMapper();
        } else {
            $this->map = $this->mapperElement->getImplementation();
        }
        if (null === $this->map) {
            $this->setError('Could not set <mapper> element.');
        }
    }

    /**
     * The heart of the matter. This is where the selector gets to decide
     * on the inclusion of a file in a particular fileset.
     *
     * @param File   $basedir  base directory the scan is being done from
     * @param string $filename the name of the file to check
     * @param File   $file     a PhingFile object the selector can use
     *
     * @throws BuildException
     *
     * @return bool whether the file should be selected or not
     */
    public function isSelected(File $basedir, $filename, File $file)
    {
        $this->validate();

        // Determine file whose out-of-dateness is to be checked
        $destfiles = $this->map->main($filename);

        // If filename does not match the To attribute of the mapper
        // then filter it out of the files we are considering
        if (null === $destfiles) {
            return false;
        }
        // Sanity check
        if (1 !== count($destfiles) || null === $destfiles[0]) {
            throw new BuildException('Invalid destination file results for ' . $this->targetdir . ' with filename ' . $filename);
        }
        $destname = $destfiles[0];
        $destfile = new File($this->targetdir, $destname);

        return SelectorUtils::isOutOfDate($file, $destfile, $this->granularity);
    }
}
