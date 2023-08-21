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
use Phing\Phing;
use Phing\Project;
use Phing\Task;

/**
 * Alters the default excludes for the <strong>entire</strong> build.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class DefaultExcludes extends Task
{
    /**
     * @var string
     */
    private $add = '';

    /**
     * @var string
     */
    private $remove = '';

    /**
     * @var bool
     */
    private $defaultrequested = false;

    /**
     * @var bool
     */
    private $echo = false;

    /**
     * by default, messages are always displayed.
     *
     * @var int
     */
    private $logLevel = Project::MSG_WARN;

    /**
     * Does the work.
     *
     * @throws BuildException if something goes wrong with the build
     */
    public function main()
    {
        if (!$this->defaultrequested && '' === $this->add && '' === $this->remove && !$this->echo) {
            throw new BuildException(
                '<defaultexcludes> task must set at least one attribute (echo="false")'
                . " doesn't count since that is the default"
            );
        }
        if ($this->defaultrequested) {
            DirectoryScanner::resetDefaultExcludes();
        }
        if ('' !== $this->add) {
            DirectoryScanner::addDefaultExclude($this->add);
        }
        if ('' !== $this->remove) {
            DirectoryScanner::removeDefaultExclude($this->remove);
        }
        if ($this->echo) {
            $lineSep = Phing::getProperty('line.separator');
            $message = 'Current Default Excludes:';
            $message .= $lineSep;
            $excludes = DirectoryScanner::getDefaultExcludes();
            $message .= '  ';
            $message .= implode($lineSep . '  ', $excludes);
            $this->log($message, $this->logLevel);
        }
    }

    /**
     * go back to standard default patterns.
     *
     * @param bool $def if true go back to default patterns
     */
    public function setDefault($def)
    {
        $this->defaultrequested = $def;
    }

    /**
     * Pattern to add to the default excludes.
     *
     * @param string $add sets the value for the pattern to exclude
     */
    public function setAdd($add)
    {
        $this->add = $add;
    }

    /**
     * Pattern to remove from the default excludes.
     *
     * @param string $remove sets the value for the pattern that
     *                       should no longer be excluded
     */
    public function setRemove($remove)
    {
        $this->remove = $remove;
    }

    /**
     * If true, echo the default excludes.
     *
     * @param bool $echo whether or not to echo the contents of
     *                   the default excludes
     */
    public function setEcho($echo)
    {
        $this->echo = $echo;
    }
}
