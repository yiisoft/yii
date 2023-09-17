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
use Phing\Project;
use Phing\Task;
use Phing\Type\Element\DirSetAware;
use Phing\Type\Element\FileSetAware;

/**
 * Echos a message to the logging system or to a file.
 *
 * @author  Michiel Rook <mrook@php.net>
 * @author  Andreas Aderhold, andi@binarycloud.com
 */
class EchoTask extends Task
{
    use DirSetAware;
    use FileSetAware;

    protected $msg = '';

    protected $file = '';

    protected $append = false;

    protected $level = 'info';

    public function main()
    {
        switch ($this->level) {
            case 'error':
                $loglevel = Project::MSG_ERR;

                break;

            case 'warning':
                $loglevel = Project::MSG_WARN;

                break;

            case 'verbose':
                $loglevel = Project::MSG_VERBOSE;

                break;

            case 'debug':
                $loglevel = Project::MSG_DEBUG;

                break;

            case 'info':
            default:
                $loglevel = Project::MSG_INFO;

                break;
        }

        $this->filesets = array_merge($this->filesets, $this->dirsets);

        if (count($this->filesets)) {
            if ('' != trim(substr($this->msg, -1))) {
                $this->msg .= "\n";
            }
            $this->msg .= $this->getFilesetsMsg();
        }

        if (empty($this->file)) {
            $this->log($this->msg, $loglevel);
        } else {
            if ($this->append) {
                $handle = @fopen($this->file, 'a');
            } else {
                $handle = @fopen($this->file, 'w');
            }

            if (false === $handle) {
                throw new BuildException("Unable to open file {$this->file}");
            }

            fwrite($handle, $this->msg);

            fclose($handle);
        }
    }

    public function setFile(string $file)
    {
        $this->file = $file;
    }

    public function setLevel(string $level)
    {
        $this->level = $level;
    }

    public function setAppend(bool $append)
    {
        $this->append = $append;
    }

    public function setMsg(string $msg)
    {
        $this->setMessage($msg);
    }

    public function setMessage(string $msg)
    {
        $this->msg = $msg;
    }

    /**
     * Supporting the <echo>Message</echo> syntax.
     */
    public function addText(string $msg)
    {
        $this->msg = $msg;
    }

    /**
     * Merges all filesets into a string to be echoed out.
     *
     * @return string String to echo
     */
    protected function getFilesetsMsg()
    {
        $project = $this->getProject();
        $msg = '';
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($project);
            $fromDir = $fs->getDir($project);
            $srcDirs = $ds->getIncludedDirectories();
            $srcFiles = $ds->getIncludedFiles();
            $msg .= 'Directory: ' . $fromDir . ' => '
                . realpath($fromDir) . "\n";
            foreach ($srcDirs as $dir) {
                $relPath = $fromDir . DIRECTORY_SEPARATOR . $dir;
                $msg .= $relPath . "\n";
            }
            foreach ($srcFiles as $file) {
                $relPath = $fromDir . DIRECTORY_SEPARATOR . $file;
                $msg .= $relPath . "\n";
            }
        }

        return $msg;
    }
}
