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
use Phing\Project;
use Phing\Type\FileSet;

/**
 * Changes the attributes of a file or all files inside specified directories.
 * Right now it has effect only under Windows. Each of the 4 possible
 * permissions has its own attribute, matching the arguments for the `attrib`
 * command.
 *
 * Example:
 * ```
 *    <attrib file="${input}" readonly="true" hidden="true" verbose="true"/>
 * ```
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class AttribTask extends ApplyTask
{
    private static $ATTR_READONLY = 'R';
    private static $ATTR_ARCHIVE = 'A';
    private static $ATTR_SYSTEM = 'S';
    private static $ATTR_HIDDEN = 'H';
    private static $SET = '+';
    private static $UNSET = '-';

    private $attr = false;

    public function init()
    {
        parent::init();
        parent::setExecutable('attrib');
        parent::setParallel(false);
    }

    /**
     * @throws BuildException
     */
    public function main()
    {
        $this->checkConfiguration();
        parent::main();
    }

    /**
     * @param bool $b
     */
    public function setVerbose($b)
    {
        $this->loglevel = Project::MSG_VERBOSE;
    }

    /**
     * A file to be attribed.
     *
     * @param File $src a file
     */
    public function setFile(File $src)
    {
        $fs = new FileSet();
        $fs->setFile($src);
        $this->addFileSet($fs);
    }

    /**
     * Set the ReadOnly file attribute.
     *
     * @param bool $value
     */
    public function setReadonly($value)
    {
        $this->addArg($value, self::$ATTR_READONLY);
    }

    /**
     * Set the Archive file attribute.
     *
     * @param bool $value
     */
    public function setArchive($value)
    {
        $this->addArg($value, self::$ATTR_ARCHIVE);
    }

    /**
     * Set the System file attribute.
     *
     * @param bool $value
     */
    public function setSystem($value)
    {
        $this->addArg($value, self::$ATTR_SYSTEM);
    }

    /**
     * Set the Hidden file attribute.
     *
     * @param bool $value
     */
    public function setHidden($value)
    {
        $this->addArg($value, self::$ATTR_HIDDEN);
    }

    /**
     * Set the executable.
     * This is not allowed, and it always throws a BuildException.
     *
     * @param mixed $e
     *
     * @throws BuildException
     */
    public function setExecutable($e): void
    {
        throw new BuildException(
            $this->getTaskType() . ' doesn\'t support the executable attribute',
            $this->getLocation()
        );
    }

    /**
     * Add source file.
     * This is not allowed, and it always throws a BuildException.
     *
     * @param bool $b ignored
     *
     * @throws BuildException
     */
    public function setAddsourcefile(bool $b)
    {
        throw new BuildException(
            $this->getTaskType()
            . ' doesn\'t support the addsourcefile attribute',
            $this->getLocation()
        );
    }

    /**
     * Set max parallel.
     * This is not allowed, and it always throws a BuildException.
     *
     * @param int $max ignored
     *
     * @throws BuildException
     */
    public function setMaxParallel($max)
    {
        throw new BuildException(
            $this->getTaskType()
            . ' doesn\'t support the maxparallel attribute',
            $this->getLocation()
        );
    }

    /**
     * Set parallel.
     * This is not allowed, and it always throws a BuildException.
     *
     * @param bool $parallel ignored
     *
     * @throws BuildException
     */
    public function setParallel(bool $parallel)
    {
        throw new BuildException(
            $this->getTaskType()
            . ' doesn\'t support the parallel attribute',
            $this->getLocation()
        );
    }

    /**
     * Check the attributes.
     *
     * @throws BuildException
     */
    protected function checkConfiguration()
    {
        if (!$this->hasAttr()) {
            throw new BuildException(
                'Missing attribute parameter',
                $this->getLocation()
            );
        }
    }

    private static function getSignString($attr)
    {
        return $attr ? self::$SET : self::$UNSET;
    }

    private function addArg($sign, $attribute)
    {
        $this->createArg()->setValue(self::getSignString($sign) . $attribute);
        $this->attr = true;
    }

    /**
     * @return bool
     */
    private function hasAttr()
    {
        return $this->attr;
    }
}
