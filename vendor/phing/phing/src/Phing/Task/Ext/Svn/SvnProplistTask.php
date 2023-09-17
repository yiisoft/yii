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
 * List all properties on files, dirs, or revisions from the working copy
 */
class SvnProplistTask extends SvnBaseTask
{
    private $propertyName = "svn.proplist";
    private $recursive = false;

    /**
     * Sets the name of the property to use
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = $propertyName;
    }

    /**
     * Returns the name of the property to use
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Sets the name of the property to use
     */
    public function setRecursive($recursive)
    {
        $this->recursive = $recursive;
    }

    /**
     * Returns the name of the property to use
     */
    public function getRecursive()
    {
        return $this->recursive;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->setup('proplist');

        $this->log("List all properties on files, dirs, or revisions from '" . $this->getWorkingCopy() . "'");

        $output = $this->run([$this->getWorkingCopy()], ['recursive' => $this->getRecursive()]);

        $this->project->setProperty($this->getPropertyName(), $output);
    }
}
