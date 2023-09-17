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

use DOMDocument;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Project;
use Phing\Task;
use Phing\Type\Element\FileSetAware;

/**
 * A XML lint task. Checking syntax of one or more XML files against an XML Schema using the DOM extension.
 *
 * @author  Knut Urdalen <knut.urdalen@telio.no>
 */
class XmlLintTask extends Task
{
    use FileSetAware;

    protected $file; // the source file (from xml attribute)
    protected $schema; // the schema file (from xml attribute)
    protected $useRNG = false;

    protected $haltonfailure = true;

    /**
     * File to be performed syntax check on.
     */
    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    /**
     * XML Schema Description file to validate against.
     */
    public function setSchema(File $schema): void
    {
        $this->schema = $schema;
    }

    /**
     * Use RNG instead of DTD schema validation.
     */
    public function setUseRNG(bool $bool): void
    {
        $this->useRNG = $bool;
    }

    /**
     * Sets the haltonfailure attribute.
     */
    public function setHaltonfailure(bool $haltonfailure): void
    {
        $this->haltonfailure = $haltonfailure;
    }

    /**
     * Execute lint check against PhingFile or a FileSet.
     *
     * {@inheritdoc}
     *
     * @throws BuildException
     */
    public function main()
    {
        libxml_use_internal_errors(true);
        if (isset($this->schema) && !file_exists($this->schema->getPath())) {
            throw new BuildException('Schema file not found: ' . $this->schema->getPath());
        }
        if (!isset($this->file) && 0 === count($this->filesets)) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }

        set_error_handler([$this, 'errorHandler']);
        if ($this->file instanceof File) {
            $this->lint($this->file->getPath());
        } else { // process filesets
            $project = $this->getProject();
            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($project);
                $files = $ds->getIncludedFiles();
                $dir = $fs->getDir($this->project)->getPath();
                foreach ($files as $file) {
                    $this->lint($dir . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
        restore_error_handler();
    }

    /**
     * Local error handler to catch validation errors and log them through Phing.
     *
     * @param int    $level
     * @param string $message
     * @param string $file
     * @param int    $line
     */
    public function errorHandler($level, $message, $file, $line): void
    {
        $matches = [];
        preg_match('/^.*\(\): (.*)$/', $message, $matches);
        $this->log($matches[1], Project::MSG_ERR);
    }

    /**
     * @param $message
     *
     * @throws BuildException
     */
    protected function logError($message): void
    {
        if ($this->haltonfailure) {
            throw new BuildException($message);
        }

        $this->log($message, Project::MSG_ERR);
    }

    /**
     * Performs validation.
     *
     * @param string $file
     */
    protected function lint($file): void
    {
        if (file_exists($file)) {
            if (is_readable($file)) {
                $dom = new DOMDocument();
                if (false === $dom->load($file)) {
                    $this->libxmlGetErrors();
                    $this->logError($file . ' is not well-formed (See messages above)');
                } else {
                    if (isset($this->schema)) {
                        if ($this->useRNG) {
                            if ($dom->relaxNGValidate($this->schema->getPath())) {
                                $this->log($file . ' validated with RNG grammar');
                            } else {
                                $this->libxmlGetErrors();
                                $this->logError($file . ' fails to validate (See messages above)');
                            }
                        } else {
                            if ($dom->schemaValidate($this->schema->getPath())) {
                                $this->log($file . ' validated with schema');
                            } else {
                                $this->libxmlGetErrors();
                                $this->logError($file . ' fails to validate (See messages above)');
                            }
                        }
                    } else {
                        $this->log(
                            $file . ' is well-formed (not validated due to missing schema specification)'
                        );
                    }
                }
            } else {
                $this->logError('Permission denied to read file: ' . $file);
            }
        } else {
            $this->logError('File not found: ' . $file);
        }
    }

    private function libxmlGetErrors(): void
    {
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            [$severity, $message] = $this->libxmlGetError($error);
            $this->log($message, 'error' === $severity ? Project::MSG_ERR : Project::MSG_WARN);
        }
        libxml_clear_errors();
    }

    private function libxmlGetError($error): array
    {
        $return = '';
        $severity = '';

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning {$error->code}: ";
                $severity = 'warn';

                break;

            case LIBXML_ERR_ERROR:
                $return .= "Error {$error->code}: ";
                $severity = 'error';

                break;

            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error {$error->code}: ";
                $severity = 'error';

                break;
        }
        $return .= trim($error->message);
        if ($error->file) {
            $return .= " in {$error->file}";
        }
        $return .= " on line {$error->line}";

        return [$severity, $return];
    }
}
