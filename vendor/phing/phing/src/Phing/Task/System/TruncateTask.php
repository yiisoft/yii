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
use Phing\Project;
use Phing\Task;
use Phing\Util\SizeHelper;
use SplFileObject;

/**
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class TruncateTask extends Task
{
    private $create = true;
    private $mkdirs = false;

    private $length;
    private $adjust;
    private $file;

    /**
     * Set a single target File.
     *
     * @param File $f the single File
     */
    public function setFile(File $f): void
    {
        $this->file = $f;
    }

    /**
     * Set the amount by which files' lengths should be adjusted.
     * It is permissible to append b / k / m / g / t.
     *
     * @param string $adjust (positive or negative) adjustment amount
     */
    public function setAdjust(string $adjust): void
    {
        $this->adjust = SizeHelper::fromHumanToBytes($adjust);
    }

    /**
     * Set the length to which files should be set.
     * It is permissible to append k / m / g.
     *
     * @param string $length (positive) adjustment amount
     *
     * @throws BuildException
     */
    public function setLength(string $length): void
    {
        $this->length = SizeHelper::fromHumanToBytes($length);
        if (null !== $this->length && $this->length < 0) {
            throw new BuildException('Cannot truncate to length ' . $this->length);
        }
    }

    /**
     * Set whether to create nonexistent files.
     *
     * @param bool $create default <code>true</code>
     */
    public function setCreate($create): void
    {
        $this->create = $create;
    }

    /**
     * Set whether, when creating nonexistent files, nonexistent directories
     * should also be created.
     *
     * @param bool $mkdirs default <code>false</code>
     */
    public function setMkdirs($mkdirs): void
    {
        $this->mkdirs = $mkdirs;
    }

    /**
     * {@inheritDoc}.
     *
     * @throws BuildException
     */
    public function main()
    {
        if (null !== $this->length && null !== $this->adjust) {
            throw new BuildException(
                'length and adjust are mutually exclusive options'
            );
        }
        if (null === $this->length && null === $this->adjust) {
            $this->length = 0;
        }
        if (null === $this->file) {
            throw new BuildException('No files specified.');
        }

        if ($this->shouldProcess($this->file)) {
            $this->process($this->file);
        }
    }

    private function shouldProcess(File $f): bool
    {
        if ($f->isFile()) {
            return true;
        }
        if (!$this->create) {
            return false;
        }
        $exception = null;

        try {
            if ($f->createNewFile($this->mkdirs)) {
                return true;
            }
        } catch (IOException $e) {
            $exception = $e;
        }
        $msg = 'Unable to create ' . $f;
        if (null === $exception) {
            $this->log($msg, Project::MSG_WARN);

            return false;
        }

        throw new BuildException($msg, $exception);
    }

    private function process(File $f): void
    {
        $len = $f->length();
        $newLength = $this->length ?? $len + $this->adjust;

        if ($len === $newLength) {
            //nothing to do!
            return;
        }

        $splFile = new SplFileObject($f->getPath(), 'a+');

        if (!$splFile->ftruncate((int) $newLength)) {
            throw new BuildException('Exception working with ' . (string) $splFile);
        }

        $splFile->rewind();
        clearstatcache();
    }
}
