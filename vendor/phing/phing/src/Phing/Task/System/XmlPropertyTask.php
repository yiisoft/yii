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
use Phing\Io\IOException;
use Phing\Io\XmlFileParser;
use Phing\Project;
use Phing\Util\Properties;

/**
 * Task for setting properties from an XML file in buildfiles.
 *
 * @author  Jonathan Bond-Caron <jbondc@openmv.com>
 *
 * @since   2.4.0
 * @see    http://ant.apache.org/manual/CoreTasks/xmlproperty.html
 */
class XmlPropertyTask extends PropertyTask
{
    private $keepRoot = true;
    private $collapseAttr = false;
    private $delimiter = ',';

    /**
     * Keep the xml root tag as the first value in the property name.
     */
    public function setKeepRoot(bool $yesNo)
    {
        $this->keepRoot = $yesNo;
    }

    /**
     * @return bool
     */
    public function getKeepRoot()
    {
        return $this->keepRoot;
    }

    /**
     * Treat attributes as nested elements.
     */
    public function setCollapseAttributes(bool $yesNo)
    {
        $this->collapseAttr = $yesNo;
    }

    /**
     * @return bool
     */
    public function getCollapseAttributes()
    {
        return $this->collapseAttr;
    }

    /**
     * Delimiter for splitting multiple values.
     *
     * @param string $d
     */
    public function setDelimiter($d)
    {
        $this->delimiter = $d;
    }

    /**
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * set the property in the project to the value.
     * if the task was give a file or env attribute
     * here is where it is loaded.
     */
    public function main()
    {
        if (null === $this->file) {
            throw new BuildException('You must specify file to load properties from', $this->getLocation());
        }

        $props = $this->loadFile($this->file);
        $this->addProperties($props);
    }

    /**
     * load properties from an XML file.
     *
     * @throws BuildException
     *
     * @return Properties
     */
    protected function loadFile(File $file)
    {
        $this->log('Loading ' . $file->getAbsolutePath(), Project::MSG_INFO);

        try { // try to load file
            if ($file->exists()) {
                $parser = new XmlFileParser();
                $parser->setCollapseAttr($this->collapseAttr);
                $parser->setKeepRoot($this->keepRoot);
                $parser->setDelimiter($this->delimiter);

                $properties = $parser->parseFile($file);

                return new Properties($properties);
            }

            if ($this->getRequired()) {
                throw new BuildException('Could not load required properties file.');
            }

            $this->log(
                'Unable to find property file: ' . $file->getAbsolutePath() . '... skipped',
                Project::MSG_WARN
            );
        } catch (IOException $ioe) {
            throw new BuildException('Could not load properties from file.', $ioe);
        }

        return null;
    }
}
