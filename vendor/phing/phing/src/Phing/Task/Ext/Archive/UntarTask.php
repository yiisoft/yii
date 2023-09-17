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

namespace Phing\Task\Ext\Archive;

use Archive_Tar;
use Phing\Exception\BuildException;
use Phing\Io\IOException;
use Phing\Io\File;
use Phing\Project;

/**
 * Extracts one or several tar archives using PEAR Archive_Tar
 *
 * @author  Joakim Bodin <joakim.bodin+phing@gmail.com>
 * @package phing.tasks.ext
 * @since   2.2.0
 */
class UntarTask extends ExtractBaseTask
{
    /**
     * @var bool
     */
    private $preservePermissions = false;

    /**
     * @param bool $preservePermissions
     */
    public function setPreservePermissions($preservePermissions)
    {
        $this->preservePermissions = $preservePermissions;
    }

    /**
     * Ensures that PEAR lib exists.
     */
    public function init()
    {
        if (!class_exists('Archive_Tar')) {
            throw new BuildException("You must have installed the pear/archive_tar package to use UntarTask.");
        }
    }

    /**
     * @param File $tarfile
     * @return mixed|void
     * @throws BuildException
     */
    protected function extractArchive(File $tarfile)
    {
        $this->log(
            "Extracting tar file: " . $tarfile->__toString() . ' to ' . $this->todir->__toString(),
            Project::MSG_INFO
        );

        try {
            $tar = $this->initTar($tarfile);
            if (!$tar->extractModify($this->todir->getAbsolutePath(), $this->removepath, $this->preservePermissions)) {
                throw new BuildException('Failed to extract tar file: ' . $tarfile->getAbsolutePath() . '. Error: ' . $tar->error_object->getMessage());
            }
        } catch (IOException $ioe) {
            $msg = "Could not extract tar file: " . $ioe->getMessage();
            throw new BuildException($msg, $ioe, $this->getLocation());
        }
    }

    /**
     * @param File $tarfile
     * @return array|int
     */
    protected function listArchiveContent(File $tarfile)
    {
        $tar = $this->initTar($tarfile);

        return $tar->listContent();
    }

    /**
     * Init a Archive_Tar class with correct compression for the given file.
     *
     * @param  File $tarfile
     * @return Archive_Tar the tar class instance
     */
    private function initTar(File $tarfile)
    {
        $compression = null;
        $tarfileName = $tarfile->getName();
        $mode = strtolower(substr($tarfileName, strrpos($tarfileName, '.')));

        $compressions = [
            'gz' => ['.gz', '.tgz',],
            'bz2' => ['.bz2',],
        ];
        foreach ($compressions as $algo => $ext) {
            if (in_array($mode, $ext)) {
                $compression = $algo;
                break;
            }
        }

        return new Archive_Tar($tarfile->getAbsolutePath(), $compression);
    }
}
