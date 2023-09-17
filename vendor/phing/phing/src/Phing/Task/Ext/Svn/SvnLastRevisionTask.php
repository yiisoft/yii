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

namespace Phing\Task\Ext\Svn;

use Phing\Exception\BuildException;

/**
 * Stores the number of the last revision of a workingcopy in a property
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.svn
 * @see     VersionControl_SVN
 * @since   2.1.0
 */
class SvnLastRevisionTask extends SvnBaseTask
{
    private $propertyName = "svn.lastrevision";
    private $lastChanged = false;

    /**
     * Sets the name of the property to use
     *
     * @param string $propertyName
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Returns the name of the property to use
     *
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Sets whether to retrieve the last changed revision
     *
     * @param bool $lastChanged
     */
    public function setLastChanged(bool $lastChanged)
    {
        $this->lastChanged = $lastChanged;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->setup('info');

        if ($this->oldVersion) {
            $output = $this->run(['--xml']);

            if (!($xmlObj = @simplexml_load_string($output))) {
                throw new BuildException("Failed to parse the output of 'svn info --xml'.");
            }

            if ($this->lastChanged) {
                $found = (int) $xmlObj->entry->commit['revision'];
            } else {
                $found = (int) $xmlObj->entry['revision'];
            }
        } else {
            $output = $this->run();

            if (empty($output) || !isset($output['entry'][0])) {
                throw new BuildException("Failed to parse the output of 'svn info'.");
            }

            if ($this->lastChanged) {
                $found = $output['entry'][0]['commit']['revision'];
            } else {
                $found = $output['entry'][0]['revision'];
            }
        }

        $this->project->setProperty($this->getPropertyName(), $found);
    }
}
