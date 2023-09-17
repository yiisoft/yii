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

namespace Phing\Task\Ext\JsMin;

use Exception;
use Phing\Task;
use Phing\Type\Element\FileSetAware;
use Phing\Type\FileSet;
use Phing\Project;
use Phing\Exception\BuildException;
use JShrink\Minifier;

/**
 * Task to minify javascript files.
 *
 * Requires JShrink (https://github.com/tedivm/JShrink) which
 * can be installed using composer
 *
 * @author  Frank Kleine <mikey@stubbles.net>
 * @package phing.tasks.ext
 * @since   2.3.0
 */
class JsMinTask extends Task
{
    use FileSetAware;

    /**
     * Whether the build should fail, if
     * errors occurred
     *
     * @var boolean
     */
    protected $failonerror = false;

    /**
     * Define if the target should use or not a suffix -min
     *
     * @var string
     */
    protected $suffix = '-min';

    /**
     * directory to put minified javascript files into
     *
     * @var string
     */
    protected $targetDir = "";

    /**
     * Whether the build should fail, if an error occurred.
     *
     * @param boolean $value
     */
    public function setFailonerror($value)
    {
        $this->failonerror = $value;
    }

    /**
     * Define if the task should or not use a suffix (-min is the default)
     *
     * @param string $value
     */
    public function setSuffix($value)
    {
        $this->suffix = $value;
    }

    /**
     * sets the directory where minified javascript files should be put into
     *
     * @param string $targetDir
     */
    public function setTargetDir($targetDir)
    {
        $this->targetDir = $targetDir;
    }

    /**
     * The main entry point method.
     */
    public function main()
    {
        if (empty($this->targetDir)) {
            throw new BuildException('Attribute "targetDir" is required');
        }

        foreach ($this->filesets as $fs) {
            try {
                $this->processFileSet($fs);
            } catch (BuildException $be) {
                // directory doesn't exist or is not readable
                if ($this->failonerror) {
                    throw $be;
                }

                $this->log($be->getMessage(), Project::MSG_WARN);
            }
        }
    }

    /**
     * @param FileSet $fs
     * @throws BuildException
     */
    protected function processFileSet(FileSet $fs)
    {
        $files = $fs->getDirectoryScanner($this->project)->getIncludedFiles();
        $fullPath = realpath($fs->getDir($this->project));
        foreach ($files as $file) {
            $this->log('Minifying file ' . $file);
            try {
                $target = $this->targetDir . '/' . str_replace(
                    $fullPath,
                    '',
                    str_replace('.js', $this->suffix . '.js', $file)
                );
                if (!is_dir(dirname($target))) {
                    if (!mkdir($concurrentDirectory = dirname($target), 0777 - umask(), true) && !is_dir($concurrentDirectory)) {
                        throw new BuildException(sprintf('Directory "%s" was not created', $concurrentDirectory));
                    }
                }

                $contents = file_get_contents($fullPath . '/' . $file);

                $minified = Minifier::minify($contents);

                file_put_contents($target, $minified);
            } catch (Exception $jsme) {
                $this->log("Could not minify file $file: " . $jsme->getMessage(), Project::MSG_ERR);
            }
        }
    }
}
