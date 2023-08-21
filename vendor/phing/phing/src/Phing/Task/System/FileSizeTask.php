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
use Phing\Task;
use Phing\Util\SizeHelper;

/**
 * FileSizeTask.
 *
 * Returns the size of a file
 *
 * @author  Johan Persson <johan162@gmail.com>
 */
class FileSizeTask extends Task
{
    /**
     * Property for File.
     *
     * @var File file
     */
    private $file;

    /**
     * Property where the file size will be stored.
     *
     * @var string
     */
    private $propertyName = 'filesize';

    /**
     * Return size in this unit.
     *
     * @var string
     */
    private $unit = SizeHelper::B;

    /**
     * Which file to calculate the file size of.
     */
    public function setFile(File $file)
    {
        if (!$file->canRead()) {
            throw new BuildException(sprintf('Input file does not exist or is not readable: %s', $file->getName()));
        }
        $this->file = $file;
    }

    /**
     * Set the name of the property to store the file size.
     */
    public function setPropertyName(string $property)
    {
        if (empty($property)) {
            throw new BuildException('Property name cannot be empty');
        }
        $this->propertyName = $property;
    }

    public function setUnit(string $unit)
    {
        $this->unit = $unit;
    }

    /**
     * Main-Method for the Task.
     *
     * @throws BuildException
     */
    public function main()
    {
        if (!($this->file instanceof File)) {
            throw new BuildException('Input file not specified');
        }

        $size = filesize($this->file);

        if (false === $size) {
            throw new BuildException(sprintf('Cannot determine filesize of: %s', $this->file));
        }
        $this->log(sprintf('%s filesize is %s%s', $this->file, $size, SizeHelper::B), Project::MSG_VERBOSE);

        $size = SizeHelper::fromBytesTo($size, $this->unit);

        $this->log(sprintf('%s filesize is %s%s', $this->file, $size, $this->unit), Project::MSG_INFO);

        $this->project->setProperty($this->propertyName, $size);
    }
}
