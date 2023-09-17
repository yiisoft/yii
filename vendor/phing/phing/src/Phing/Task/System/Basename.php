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
use Phing\Task;
use Phing\Util\StringHelper;

/**
 * Task that changes the permissions on a file/directory.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class Basename extends Task
{
    /**
     * @var File
     */
    private $file;

    /**
     * @var string
     */
    private $property;

    /**
     * @var string
     */
    private $suffix;

    /**
     * file or directory to get base name from.
     *
     * @param File $file file or directory to get base name from
     */
    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    /**
     * Property to set base name to.
     *
     * @param string $property name of property
     */
    public function setProperty(string $property): void
    {
        $this->property = $property;
    }

    /**
     * Optional suffix to remove from base name.
     *
     * @param string $suffix suffix to remove from base name
     */
    public function setSuffix(string $suffix): void
    {
        $this->suffix = $suffix;
    }

    /**
     * do the work.
     *
     * @throws BuildException if required attributes are not supplied
     *                        property and attribute are required attributes
     */
    public function main()
    {
        if (null === $this->property) {
            throw new BuildException('property attribute required', $this->getLocation());
        }

        if (null === $this->file) {
            throw new BuildException('file attribute required', $this->getLocation());
        }

        $this->getProject()->setNewProperty(
            $this->property,
            $this->removeExtension($this->file->getName(), $this->suffix)
        );
    }

    private function removeExtension(?string $s, ?string $ext)
    {
        if (null === $ext || !StringHelper::endsWith($ext, $s)) {
            return $s;
        }

        return rtrim(substr($s, 0, -strlen($ext)), '.');
    }
}
