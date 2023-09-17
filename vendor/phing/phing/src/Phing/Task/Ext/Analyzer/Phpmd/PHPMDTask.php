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

namespace Phing\Task\Ext\Analyzer\Phpmd;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Task;
use Phing\Type\Element\FileSetAware;
use Phing\Util\DataStore;
use PHPMD\AbstractRule;
use PHPMD\PHPMD;
use PHPMD\Report;
use PHPMD\RuleSetFactory;

/**
 * Runs PHP Mess Detector. Checking PHP files for several potential problems
 * based on rulesets.
 *
 * @package phing.tasks.ext.phpmd
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @since   2.4.1
 */
class PHPMDTask extends Task
{
    use FileSetAware;

    /**
     * A php source code filename or directory
     *
     * @var File
     */
    protected $file = null;

    /**
     * The rule-set filenames or identifier.
     *
     * @var string
     */
    protected $rulesets = 'codesize,unusedcode';

    /**
     * The minimum priority for rules to load.
     *
     * @var integer
     */
    protected $minimumPriority = 0;

    /**
     * List of valid file extensions for analyzed files.
     *
     * @var array
     */
    protected $allowedFileExtensions = ['php'];

    /**
     * List of exclude directory patterns.
     *
     * @var array
     */
    protected $ignorePatterns = ['.git', '.svn', 'CVS', '.bzr', '.hg'];

    /**
     * The format for the report
     *
     * @var string
     */
    protected $format = 'text';

    /**
     * Formatter elements.
     *
     * @var PHPMDFormatterElement[]
     */
    protected $formatters = [];

    /**
     * @var string
     */
    protected $pharLocation = "";

    /**
     * Cache data storage
     *
     * @var DataStore
     */
    protected $cache;

    /**
     * Set the input source file or directory.
     *
     * @param File $file The input source file or directory.
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }

    /**
     * Sets the minimum rule priority.
     *
     * @param integer $minimumPriority Minimum rule priority.
     */
    public function setMinimumPriority($minimumPriority)
    {
        $this->minimumPriority = $minimumPriority;
    }

    /**
     * Sets the rule-sets.
     *
     * @param string $ruleSetFileNames Comma-separated string of rule-set filenames or identifier.
     */
    public function setRulesets($ruleSetFileNames)
    {
        $this->rulesets = $ruleSetFileNames;
    }

    /**
     * Sets a list of filename extensions for valid php source code files.
     *
     * @param string $fileExtensions List of valid file extensions without leading dot.
     */
    public function setAllowedFileExtensions($fileExtensions)
    {
        $this->allowedFileExtensions = [];

        $token = ' ,;';
        $ext = strtok($fileExtensions, $token);

        while ($ext !== false) {
            $this->allowedFileExtensions[] = $ext;
            $ext = strtok($token);
        }
    }

    /**
     * Sets a list of ignore patterns that is used to exclude directories from the source analysis.
     *
     * @param string $ignorePatterns List of ignore patterns.
     */
    public function setIgnorePatterns($ignorePatterns)
    {
        $this->ignorePatterns = [];

        $token = ' ,;';
        $pattern = strtok($ignorePatterns, $token);

        while ($pattern !== false) {
            $this->ignorePatterns[] = $pattern;
            $pattern = strtok($token);
        }
    }

    /**
     * Create object for nested formatter element.
     *
     * @return PHPMDFormatterElement
     */
    public function createFormatter()
    {
        $num = array_push($this->formatters, new PHPMDFormatterElement());

        return $this->formatters[$num - 1];
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @param string $pharLocation
     */
    public function setPharLocation($pharLocation)
    {
        $this->pharLocation = $pharLocation;
    }

    /**
     * Whether to store last-modified times in cache
     *
     * @param File $file
     */
    public function setCacheFile(File $file)
    {
        $this->cache = new DataStore($file);
    }

    /**
     * Find PHPMD
     *
     * @return string
     * @throws BuildException
     */
    protected function loadDependencies()
    {
        if (!empty($this->pharLocation)) {
            // nasty but necessary: reorder the autoloaders so the one in the PHAR gets priority
            $autoloadFunctions = spl_autoload_functions();
            $composerAutoloader = null;
            if (get_class($autoloadFunctions[0][0]) === 'Composer\Autoload\ClassLoader') {
                $composerAutoloader = $autoloadFunctions[0];
                spl_autoload_unregister($composerAutoloader);
            }

            $GLOBALS['_SERVER']['SCRIPT_NAME'] = '-';
            ob_start();
            include_once 'phar://' . $this->pharLocation . '/vendor/autoload.php';
            ob_end_clean();

            if ($composerAutoloader !== null) {
                spl_autoload_register($composerAutoloader);
            }
        }

        $className = '\PHPMD\PHPMD';

        if (!class_exists($className)) {
            throw new BuildException(
                'PHPMDTask depends on PHPMD being installed and on include_path or listed in pharLocation.',
                $this->getLocation()
            );
        }

        $minPriority = AbstractRule::LOWEST_PRIORITY;

        if (!$this->minimumPriority) {
            $this->minimumPriority = $minPriority;
        }

        return $className;
    }

    /**
     * Return the list of files to parse
     *
     * @return string[] list of absolute files to parse
     */
    protected function getFilesToParse()
    {
        $filesToParse = [];

        if ($this->file instanceof File) {
            $filesToParse[] = $this->file->getPath();
        } else {
            // append any files in filesets
            foreach ($this->filesets as $fs) {
                $dir = $fs->getDir($this->project)->getAbsolutePath();
                foreach ($fs->getDirectoryScanner($this->project)->getIncludedFiles() as $filename) {
                    $fileAbsolutePath = $dir . DIRECTORY_SEPARATOR . $filename;
                    if ($this->cache) {
                        $lastMTime = $this->cache->get($fileAbsolutePath);
                        $currentMTime = filemtime($fileAbsolutePath);
                        if ($lastMTime >= $currentMTime) {
                            continue;
                        }

                        $this->cache->put($fileAbsolutePath, $currentMTime);
                    }
                    $filesToParse[] = $fileAbsolutePath;
                }
            }
        }
        return $filesToParse;
    }

    /**
     * Executes PHPMD against PhingFile or a FileSet
     *
     * @throws BuildException - if the phpmd classes can't be loaded.
     */
    public function main()
    {
        $className = $this->loadDependencies();

        if (!isset($this->file) and count($this->filesets) == 0) {
            throw new BuildException('Missing either a nested fileset or attribute "file" set');
        }

        if (count($this->formatters) == 0) {
            // turn legacy format attribute into formatter
            $fmt = new PHPMDFormatterElement();
            $fmt->setType($this->format);
            $fmt->setUseFile(false);

            $this->formatters[] = $fmt;
        }

        $reportRenderers = [];

        foreach ($this->formatters as $fe) {
            if ($fe->getType() == '') {
                throw new BuildException('Formatter missing required "type" attribute.');
            }

            if ($fe->getUsefile() && $fe->getOutfile() === null) {
                throw new BuildException('Formatter requires "outfile" attribute when "useFile" is true.');
            }

            $reportRenderers[] = $fe->getRenderer();
        }

        if ($this->cache) {
            $reportRenderers[] = new PHPMDRendererRemoveFromCache($this->cache);
        } else {
            $this->cache = null; // cache not compatible to old version
        }

        // Create a rule set factory
        $ruleSetFactory = new RuleSetFactory();
        $ruleSetFactory->setMinimumPriority($this->minimumPriority);

        /**
         * @var PHPMD $phpmd
         */
        $phpmd = new $className();
        $phpmd->setFileExtensions($this->allowedFileExtensions);
        $phpmd->addIgnorePatterns($this->ignorePatterns);

        $filesToParse = $this->getFilesToParse();

        if (count($filesToParse) > 0) {
            $inputPath = implode(',', $filesToParse);

            $this->log('Processing files...');

            $report = new Report();
            $phpmd->processFiles($inputPath, $this->rulesets, $reportRenderers, $ruleSetFactory, $report);

            if ($this->cache) {
                $this->cache->commit();
            }

            $this->log('Finished processing files');
        } else {
            $this->log('No files to process');
        }
    }
}
