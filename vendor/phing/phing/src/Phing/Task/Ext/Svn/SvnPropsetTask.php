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
class SvnPropsetTask extends SvnBaseTask
{
    private $svnPropertyName;
    private $svnPropertyValue;

    /**
     * Sets the name of the property to use
     *
     * @param $svnPropertyName
     */
    public function setSvnPropertyName($svnPropertyName)
    {
        $this->svnPropertyName = $svnPropertyName;
    }

    /**
     * Returns the name of the property to use
     */
    public function getSvnPropertyName()
    {
        return $this->svnPropertyName;
    }

    /**
     * Sets the name of the property to use
     *
     * @param $svnPropertyValue
     */
    public function setSvnPropertyValue($svnPropertyValue)
    {
        $this->svnPropertyValue = $svnPropertyValue;
    }

    /**
     * Returns the name of the property to use
     */
    public function getSvnPropertyValue()
    {
        return $this->svnPropertyValue;
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        $this->setup('propset');

        $this->log("Set svn property for '" . $this->getToDir() . "'");

        $output = $this->run([$this->getSvnPropertyName(), $this->getSvnPropertyValue(), $this->getToDir()]);
    }
}
