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

use Phing\Io\File;
use Phing\Project;
use ZipArchive;

/**
 * Extracts one or several zip archives using ZipArchive class.
 *
 * @author  Joakim Bodin <joakim.bodin+phing@gmail.com>
 * @author  George Miroshnikov <laggy.luke@gmail.com>
 * @package phing.tasks.ext
 */
class UnzipTask extends ExtractBaseTask
{
    /**
     * Extract archive content into $this->todir directory
     *
     * @param  File Zip file to extract
     * @return boolean
     */
    protected function extractArchive(File $zipfile)
    {
        $this->log(
            "Extracting zip: " . $zipfile->__toString() . ' to ' . $this->todir->__toString(),
            Project::MSG_INFO
        );

        $zip = new ZipArchive();

        $result = $zip->open($zipfile->getAbsolutePath());
        if (!$result) {
            $this->log("Unable to open zipfile " . $zipfile->__toString(), Project::MSG_ERR);

            return false;
        }

        $result = $zip->extractTo($this->todir->getAbsolutePath());
        if (!$result) {
            $this->log("Unable to extract zipfile " . $zipfile->__toString(), Project::MSG_ERR);

            return false;
        }

        return true;
    }

    /**
     * List archive content
     *
     * @param  File Zip file to list content
     * @return array List of files inside $zipfile
     */
    protected function listArchiveContent(File $zipfile)
    {
        $zip = new ZipArchive();
        $zip->open($zipfile->getAbsolutePath());

        $content = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $content[] = [
                'filename' => $zip->getNameIndex($i)
            ];
        }

        return $content;
    }
}
