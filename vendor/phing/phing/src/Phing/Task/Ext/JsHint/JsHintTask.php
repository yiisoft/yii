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

namespace Phing\Task\Ext\JsHint;

use Phing\Task;
use Phing\Type\Element\FileSetAware;
use Phing\Exception\BuildException;
use Phing\Io\File as PhingFile;
use Phing\Project;

/**
 * JsHintTask
 *
 * Checks the JavaScript code using JSHint
 * See http://www.jshint.com/
 *
 * @author  Martin Hujer <mhujer@gmail.com>
 * @package phing.tasks.ext
 * @since   2.6.2
 */
class JsHintTask extends Task
{
    use FileSetAware;

    /**
     * The source file (from xml attribute)
     *
     * @var string
     */
    protected $file;

    /**
     * Should the build fail on JSHint errors
     *
     * @var boolean
     */
    private $haltOnError = false;

    /**
     * Should the build fail on JSHint warnings
     *
     * @var boolean
     */
    private $haltOnWarning = false;

    /**
     * reporter
     *
     * @var string
     */
    private $reporter = 'checkstyle';

    /**
     * xmlAttributes
     *
     * @var array
     */
    private $xmlAttributes = [
        'severity' => [
            'error' => 'error',
            'warning' => 'warning',
            'info' => 'info'
        ],
        'fileError' => 'error',
        'line' => 'line',
        'column' => 'column',
        'message' => 'message',
    ];

    /**
     * Path where the the report in Checkstyle format should be saved
     *
     * @var string
     */
    private $checkstyleReportPath;

    /**
     * @var string $config
     */
    private $config;

    /**
     * @var string
     */
    private $executable = 'jshint';

    /**
     * File to be performed syntax check on
     *
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = new PhingFile($file);
    }

    /**
     * @param $haltOnError
     */
    public function setHaltOnError($haltOnError)
    {
        $this->haltOnError = $haltOnError;
    }

    /**
     * @param $haltOnWarning
     */
    public function setHaltOnWarning($haltOnWarning)
    {
        $this->haltOnWarning = $haltOnWarning;
    }

    /**
     * @param $checkstyleReportPath
     */
    public function setCheckstyleReportPath($checkstyleReportPath)
    {
        $this->checkstyleReportPath = $checkstyleReportPath;
    }

    /**
     * @param string $reporter
     */
    public function setReporter($reporter)
    {
        $this->reporter = $reporter;

        if ($this->reporter === 'jslint') {
            $this->xmlAttributes = [
                'severity' => ['error' => 'E', 'warning' => 'W', 'info' => 'I'],
                'fileError' => 'issue',
                'line' => 'line',
                'column' => 'char',
                'message' => 'reason',
            ];
        }
    }

    /**
     * @param string $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @param string $path
     */
    public function setExecutable($path)
    {
        $this->executable = $path;
    }

    /**
     * @throws BuildException
     */
    public function main()
    {
        if (!isset($this->file) && count($this->filesets) === 0) {
            throw new BuildException("Missing either a nested fileset or attribute 'file' set");
        }

        if (!isset($this->file)) {
            $fileList = [];
            $project = $this->getProject();
            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($project);
                $files = $ds->getIncludedFiles();
                $dir = $fs->getDir($this->project)->getAbsolutePath();
                foreach ($files as $file) {
                    $fileList[] = $dir . DIRECTORY_SEPARATOR . $file;
                }
            }
        } else {
            $fileList = [$this->file];
        }

        $this->checkJsHintIsInstalled();

        $fileList = array_map('escapeshellarg', $fileList);
        if ($this->config) {
            $command = sprintf(
                '%s --config=%s --reporter=%s %s',
                $this->executable,
                $this->config,
                $this->reporter,
                implode(' ', $fileList)
            );
        } else {
            $command = sprintf(
                '%s --reporter=%s %s',
                $this->executable,
                $this->reporter,
                implode(' ', $fileList)
            );
        }
        $this->log('Execute: ' . PHP_EOL . $command, Project::MSG_VERBOSE);
        $output = [];
        exec($command, $output);
        $output = implode(PHP_EOL, $output);

        if ($this->checkstyleReportPath) {
            file_put_contents($this->checkstyleReportPath, $output);
            $this->log('');
            $this->log('Checkstyle report saved to ' . $this->checkstyleReportPath);
        }

        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($output, 'SimpleXMLElement', LIBXML_PARSEHUGE);
        if (false === $xml) {
            $errors = libxml_get_errors();
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $msg = $xml[$error->line - 1] . "\n";
                    $msg .= str_repeat('-', $error->column) . "^\n";

                    switch ($error->level) {
                        case LIBXML_ERR_WARNING:
                            $msg .= 'Warning ' . $error->code . ': ';
                            break;
                        case LIBXML_ERR_ERROR:
                            $msg .= 'Error ' . $error->code . ': ';
                            break;
                        case LIBXML_ERR_FATAL:
                            $msg .= 'Fatal error ' . $error->code . ': ';
                            break;
                    }
                    $msg .= trim($error->message) . PHP_EOL . '  Line: ' . $error->line . PHP_EOL . '  Column: ' . $error->column;
                    $this->log($msg, Project::MSG_VERBOSE);
                }
                throw new BuildException('Unable to parse output of JSHint, use checkstyleReportPath="/path/to/report.xml" to debug');
            }
        }
        $projectBasedir = $this->getProjectBasedir();
        $errorsCount = 0;
        $warningsCount = 0;
        $fileError = $this->xmlAttributes['fileError'];
        foreach ($xml->file as $file) {
            $fileAttributes = $file->attributes();
            $fileName = (string) $fileAttributes['name'];
            foreach ($file->$fileError as $error) {
                $errAttr = (array) $error->attributes();
                $attrs = current($errAttr);

                if ($attrs['severity'] === $this->xmlAttributes['severity']['error']) {
                    $errorsCount++;
                } elseif ($attrs['severity'] === $this->xmlAttributes['severity']['warning']) {
                    $warningsCount++;
                } elseif ($attrs['severity'] !== $this->xmlAttributes['severity']['info']) {
                    throw new BuildException(sprintf('Unknown severity "%s"', $attrs['severity']));
                }
                $e = sprintf(
                    '%s: line %d, col %d, %s',
                    str_replace($projectBasedir, '', $fileName),
                    $attrs[$this->xmlAttributes['line']],
                    $attrs[$this->xmlAttributes['column']],
                    $attrs[$this->xmlAttributes['message']]
                );
                $this->log($e);
            }
        }

        $message = sprintf(
            'JSHint detected %d errors and %d warnings.',
            $errorsCount,
            $warningsCount
        );
        if ($this->haltOnError && $errorsCount) {
            throw new BuildException($message);
        }

        if ($this->haltOnWarning && $warningsCount) {
            throw new BuildException($message);
        }

        $this->log('');
        $this->log($message);
    }

    /**
     * @return string Path to the project basedir
     * @throws BuildException
     */
    private function getProjectBasedir()
    {
        return $this->getProject()->getBasedir()->getAbsolutePath() . DIRECTORY_SEPARATOR;
    }

    /**
     * Checks, whether the JSHint can be executed
     *
     * @throws BuildException
     */
    private function checkJsHintIsInstalled()
    {
        $command = sprintf('%s -v 2>&1', $this->executable);
        exec($command, $output, $return);
        if ($return !== 0) {
            throw new BuildException('JSHint is not installed!');
        }
    }
}
