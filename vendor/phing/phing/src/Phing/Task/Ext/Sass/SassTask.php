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
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Paul Stuart <pstuart2@gmail.com>
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 */

namespace Phing\Task\Ext\Sass;

use Exception;
use Phing\Exception\BuildException;
use Phing\Io\FileSystem;
use Phing\Project;
use Phing\Task;
use Phing\Type\FileSet;
use Phing\Util\StringHelper;

/**
 * Executes Sass for a particular fileset.
 *
 * If the sass executable is not available, but scssphp is, then use that instead.
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Paul Stuart <pstuart2@gmail.com>
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     SassTask.php
 */
class SassTask extends Task
{
    /**
     * Style to generate to.
     *
     * @var string
     */
    protected $style = 'nested';

    /**
     * Stack trace on error.
     *
     * @var bool
     */
    protected $trace = false;

    /**
     * Unix-style newlines?
     *
     * @var bool
     */
    protected $unixnewlines = true;

    /**
     * Encoding
     *
     * @var string
     */
    protected $encoding = 'utf-8';

    /**
     * SASS import path.
     *
     * @var string
     */
    protected $loadPath = '';

    /**
     * Whether to just check syntax
     *
     * @var bool
     */
    protected $check = false;

    /**
     * Whether to use the sass command line tool.
     *
     * @var bool
     */
    protected $useSass = true;

    /**
     * Whether to use the scssphp compiler, if available.
     *
     * @var bool
     */
    protected $useScssphp = true;

    /**
     * Input filename if only processing one file is required.
     *
     * @var string|null
     */
    protected $file = null;

    /**
     * Output filename
     *
     * @var string|null
     */
    protected $output = null;

    /**
     * Contains the path info of our file to allow us to parse.
     *
     * @var array
     */
    protected $pathInfo = null;

    /**
     * The Sass executable.
     *
     * @var string
     */
    protected $executable = 'sass';

    /**
     * The ext type we are looking for when Verifyext is set to true.
     *
     * More than likely should be "scss" or "sass".
     *
     * @var string
     */
    protected $extfilter = '';

    /**
     * This flag means 'note errors to the output, but keep going'
     *
     * @var bool
     */
    protected $failonerror = true;

    /**
     * The fileset we will be running Sass on.
     *
     * @var array
     */
    protected $filesets = [];

    /**
     * Additional flags to pass to sass.
     *
     * @var string
     */
    protected $flags = '';

    /**
     * Indicates if we want to keep the directory structure of the files.
     *
     * @var bool
     */
    protected $keepsubdirectories = true;

    /**
     * When true we will remove the current file ext.
     *
     * @var bool
     */
    protected $removeoldext = true;

    /**
     * The new ext our files will have.
     *
     * @var string
     */
    protected $newext = 'css';
    /**
     * The path to send our output files to.
     *
     * If not defined they will be created in the same directory the
     * input is from.
     *
     * @var string
     */
    protected $outputpath = '';

    /**
     * @var bool
     */
    protected $force;

    /**
     * @var bool
     */
    protected $lineNumbers = false;

    /**
     * @var bool
     */
    protected $noCache;

    /**
     * Set input file (For example style.scss)
     *
     * Synonym for @see setFile
     *
     * @param string $file Filename
     *
     * @return void
     */
    public function setInput($file)
    {
        $this->setFile($file);
    }

    /**
     * Set name of output file.
     *
     * @param string $file Filename of [css] to output.
     *
     * @return void
     */
    public function setOutput($file)
    {
        $this->output = $file;
    }

    /**
     * Sets the failonerror flag. Default: true
     *
     * @param string $failonerror Jenkins style boolean value
     *
     * @access public
     * @return void
     */
    public function setFailonerror($failonerror)
    {
        $this->failonerror = StringHelper::booleanValue($failonerror);
    }

    /**
     * Sets the executable to use for sass. Default: sass
     *
     * The default assumes sass is in your path. If not you can provide the full
     * path to sass.
     *
     * @param string $executable Name of executable, optionally including full path
     *
     * @return void
     */
    public function setExecutable(string $executable): void
    {
        $this->executable = $executable;
    }

    /**
     * Return name/path of sass executable.
     */
    public function getExecutable(): string
    {
        return $this->executable;
    }

    /**
     * Sets the extfilter. Default: <none>
     *
     * This will filter the fileset to only process files that match
     * this extension. This could also be done with the fileset.
     *
     * @param string $extfilter Extension to filter for.
     *
     * @access public
     * @return void
     */
    public function setExtfilter($extfilter)
    {
        $this->extfilter = trim($extfilter, ' .');
    }

    /**
     * Return extfilter setting.
     *
     * @return string
     */
    public function getExtfilter()
    {
        return $this->extfilter;
    }

    /**
     * Additional flags to pass to sass.
     *
     * Command will be:
     * sass {$flags} {$inputfile} {$outputfile}
     *
     * @param string $flags Flags to pass
     *
     * @return void
     */
    public function setFlags(string $flags): void
    {
        $this->flags = trim($flags);
    }

    /**
     * Return flags to be used when running the sass executable.
     */
    public function getFlags(): string
    {
        return trim($this->flags);
    }

    /**
     * Sets the removeoldext flag. Default: true
     *
     * This will cause us to strip the existing extension off the output
     * file.
     *
     * @param string $removeoldext Jenkins style boolean value
     *
     * @access public
     * @return void
     */
    public function setRemoveoldext($removeoldext)
    {
        $this->removeoldext = StringHelper::booleanValue($removeoldext);
    }

    /**
     * Return removeoldext value (true/false)
     *
     * @return bool
     */
    public function getRemoveoldext()
    {
        return $this->removeoldext;
    }

    /**
     * Set default encoding
     *
     * @param string $encoding Default encoding to use.
     *
     * @return void
     */
    public function setEncoding($encoding)
    {
        $encoding = trim($encoding);
        if ($encoding !== '') {
            $this->flags .= " --default-encoding $encoding";
        } else {
            $this->flags = str_replace(
                ' --default-encoding ' . $this->encoding,
                '',
                $this->flags
            );
        }
        $this->encoding = $encoding;
    }

    /**
     * Return the output encoding.
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Sets the newext value. Default: css
     *
     * This is the extension we will add on to the output file regardless
     * of if we remove the old one or not.
     *
     * @param string $newext New extension to use, e.g. css
     *
     * @access public
     * @return void
     */
    public function setNewext($newext)
    {
        $this->newext = trim($newext, ' .');
    }

    /**
     * Return extension added to output files.
     *
     * @return string
     */
    public function getNewext()
    {
        return $this->newext;
    }

    /**
     * Sets the outputpath value. Default: <none>
     *
     * This will force the output path to be something other than
     * the path of the fileset used.
     *
     * @param string $outputpath Path name
     *
     * @access public
     * @return void
     */
    public function setOutputpath($outputpath)
    {
        $this->outputpath = rtrim(trim($outputpath), DIRECTORY_SEPARATOR);
    }

    /**
     * Return the outputpath value.
     *
     * @return string
     */
    public function getOutputpath()
    {
        return $this->outputpath;
    }

    /**
     * Sets the keepsubdirectories value. Default: true
     *
     * When set to true we will keep the directory structure. So any input
     * files in subdirectories will have their output file in that same
     * sub-directory. If false, all output files will be put in the path
     * defined by outputpath or in the directory top directory of the fileset.
     *
     * @param bool $keepsubdirectories Jenkins style boolean
     *
     * @access public
     * @return void
     */
    public function setKeepsubdirectories($keepsubdirectories)
    {
        $this->keepsubdirectories = StringHelper::booleanValue($keepsubdirectories);
    }

    /**
     * Return keepsubdirectories value.
     *
     * @return bool
     */
    public function getKeepsubdirectories()
    {
        return $this->keepsubdirectories;
    }

    /**
     * Nested creator, creates a FileSet for this task
     *
     * @return FileSet The created fileset object
     */
    public function createFileSet()
    {
        $num = array_push($this->filesets, new FileSet());
        return $this->filesets[$num - 1];
    }

    /**
     * Whether to just check syntax.
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setCheck($value)
    {
        $check = StringHelper::booleanValue($value);
        $this->check = $check;
        if ($check) {
            $this->flags .= ' --check ';
        } else {
            $this->flags = str_replace(' --check ', '', $this->flags);
        }
    }

    /**
     * Indicate if just a syntax check is required.
     *
     * @return boolean
     */
    public function getCheck()
    {
        return $this->check;
    }

    /**
     * Set style to compact
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setCompact($value)
    {
        $compress = StringHelper::booleanValue($value);
        if ($compress) {
            $this->flags = str_replace(' --style ' . $this->style, '', $this->flags);
            $this->flags .= ' --style compact';
            $this->style = 'compact';
        }
    }

    /**
     * Indicate whether style is set to 'coompact'.
     *
     * @return bool
     * @see    setCompact
     */
    public function getCompact()
    {
        return $this->style === 'compact';
    }

    /**
     * Set style to compressed
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setCompressed($value)
    {
        $compress = StringHelper::booleanValue($value);
        if ($compress) {
            $this->flags = str_replace(' --style ' . $this->style, '', $this->flags);
            $this->flags .= ' --style compressed';
            $this->style = 'compressed';
        }
    }

    /**
     * Indicate whether style is set to 'compressed'.
     *
     * @return bool
     * @see    setCompressed
     */
    public function getCompressed()
    {
        return $this->style === 'compressed';
    }

    /**
     * Set style to crunched. Supported by scssphp only.
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setCrunched($value)
    {
        $compress = StringHelper::booleanValue($value);
        if ($compress) {
            $this->style = 'crunched';
        }
    }

    /**
     * Indicate whether style is set to 'crunched'.
     *
     * @return bool
     * @see    setCrunched
     */
    public function getCrunched()
    {
        return $this->style === 'crunched';
    }

    /**
     * Set style to expanded
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setExpand($value)
    {
        $expand = StringHelper::booleanValue($value);
        if ($expand) {
            $this->flags = str_replace(' --style ' . $this->style, '', $this->flags);
            $this->flags .= ' --style expanded';
            $this->style = 'expanded';
        }
    }

    /**
     * Indicate whether style is set to 'expanded'.
     *
     * @return bool
     * @see    setExpand
     */
    public function getExpand()
    {
        return $this->style === 'expanded';
    }

    /**
     * Set style to nested
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setNested($value)
    {
        $nested = StringHelper::booleanValue($value);
        if ($nested) {
            $this->flags = str_replace(' --style ' . $this->style, '', $this->flags);
            $this->flags .= ' --style nested';
            $this->style = 'nested';
        }
    }

    /**
     * Indicate whether style is set to 'nested'.
     *
     * @return bool
     * @see    setNested
     */
    public function getNested()
    {
        return $this->style === 'nested';
    }

    /**
     * Whether to force recompiled when --update is used.
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setForce($value)
    {
        $force = StringHelper::booleanValue($value);
        $this->force = $force;
        if ($force) {
            $this->flags .= ' --force ';
        } else {
            $this->flags = str_replace(' --force ', '', $this->flags);
        }
    }

    /**
     * Return force value.
     *
     * @return bool
     */
    public function getForce()
    {
        return $this->force;
    }

    /**
     * Whether to cache parsed sass files.
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setNoCache($value)
    {
        $noCache = StringHelper::booleanValue($value);
        $this->noCache = $noCache;
        if ($noCache) {
            $this->flags .= ' --no-cache ';
        } else {
            $this->flags = str_replace(' --no-cache ', '', $this->flags);
        }
    }

    /**
     * Return noCache value.
     *
     * @return bool
     */
    public function getNoCache()
    {
        return $this->noCache;
    }

    /**
     * Specify SASS import path
     *
     * @param string $path Import path
     *
     * @return void
     */
    public function setPath(string $path): void
    {
        $this->flags .= " --load-path $path ";
        $this->loadPath = $path;
    }

    /**
     * Return the SASS import path.
     */
    public function getPath(): string
    {
        return $this->loadPath;
    }

    /**
     * Set output style.
     *
     * @param string $style nested|compact|compressed|expanded|crunched
     *
     * @return void
     */
    public function setStyle(string $style): void
    {
        $style = strtolower($style);
        switch ($style) {
            case 'nested':
            case 'compact':
            case 'compressed':
            case 'expanded':
            case 'crunched':
                $this->flags = str_replace(" --style $this->style", '', $this->flags);
                $this->style = $style;
                $this->flags .= " --style $style ";
                break;
            default:
                $this->log("Style $style ignored", Project::MSG_INFO);
        }
    }

    /**
     * Return style used for generating output.
     */
    public function getStyle(): string
    {
        return $this->style;
    }

    /**
     * Set trace option.
     *
     * IE: Whether to output a stack trace on error.
     *
     * @param string $trace Jenkins style boolean value
     *
     * @return void
     */
    public function setTrace($trace)
    {
        $this->trace = StringHelper::booleanValue($trace);
        if ($this->trace) {
            $this->flags .= ' --trace ';
        } else {
            $this->flags = str_replace(' --trace ', '', $this->flags);
        }
    }

    /**
     * Return trace option.
     *
     * @return bool
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * Whether to use unix-style newlines.
     *
     * @param string $newlines Jenkins style boolean value
     *
     * @return void
     */
    public function setUnixnewlines($newlines)
    {
        $unixnewlines = StringHelper::booleanValue($newlines);
        $this->unixnewlines = $unixnewlines;
        if ($unixnewlines) {
            $this->flags .= ' --unix-newlines ';
        } else {
            $this->flags = str_replace(' --unix-newlines ', '', $this->flags);
        }
    }

    /**
     * Return unix-newlines setting
     *
     * @return bool
     */
    public function getUnixnewlines()
    {
        return $this->unixnewlines;
    }

    /**
     * Whether to identify source-file and line number for generated CSS.
     *
     * @param string $lineNumbers Jenkins style boolean value
     */
    public function setLineNumbers(string $lineNumbers): void
    {
        $lineNumbers = StringHelper::booleanValue($lineNumbers);
        $this->lineNumbers = $lineNumbers;
        if ($lineNumbers) {
            $this->flags .= ' --line-numbers ';
        } else {
            $this->flags = str_replace(' --line-numbers ', '', $this->flags);
        }
    }

    /**
     * Return line-numbers setting.
     */
    public function getLineNumbers(): bool
    {
        return $this->lineNumbers;
    }

    /**
     * Whether to use the 'sass' command line tool.
     *
     * @param string $value Jenkins style boolean value.
     *
     * @return void
     * @link   http://sass-lang.com/install
     */
    public function setUseSass($value)
    {
        $this->useSass = StringHelper::booleanValue($value);
    }

    /**
     * Get useSass property value
     *
     * @return bool
     */
    public function getUseSass(): bool
    {
        return $this->useSass;
    }

    /**
     * Whether to use the scssphp compiler.
     *
     * @param string $value Jenkins style boolean value.
     *
     * @return void
     * @link   https://scssphp.github.io/scssphp/
     */
    public function setUseScssphp($value)
    {
        $this->useScssphp = StringHelper::booleanValue($value);
    }

    /**
     * Whether to use the Scss php library
     *
     * @return bool
     */
    public function getUseScssPhp(): bool
    {
        return $this->useScssphp;
    }

    /**
     * Set single filename to compile from scss to css.
     *
     * @param string $file Single filename to compile.
     *
     * @return void
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Our main execution of the task.
     *
     * @throws BuildException
     * @throws Exception
     *
     * @access public
     * @return void
     */
    public function main()
    {
        if ($this->useSass) {
            if (strlen($this->executable) < 0) {
                throw new BuildException("'executable' must be defined.");
            }
        }

        if (empty($this->filesets) && $this->file === null) {
            throw new BuildException(
                "Missing either a nested fileset or attribute 'file'"
            );
        }

        try {
            $compiler = (new SassTaskCompilerFactory(FileSystem::getFileSystem()))->prepareCompiler($this);
        } catch (BuildException $exception) {
            if ($this->failonerror) {
                throw $exception;
            }
            $this->log($exception->getMessage());
            return;
        }

        if (count($this->filesets) > 0) {
            $this->processFilesets($compiler);
        } elseif ($this->file !== null) {
            $this->processFile($compiler);
        }
    }

    /**
     * Compile a specified file.
     *
     * If output file is not specified, but outputpath is, place output in
     * that directory. If neither is specified, place .css file in the
     * directory that the input file is in.
     *
     * @param SassTaskCompiler $compiler Compiler to use for processing fileset
     *
     * @return void
     */
    public function processFile(SassTaskCompiler $compiler)
    {
        $this->log("Process file", Project::MSG_INFO);
        if (null === $this->output) {
            $specifiedOutputPath = (strlen($this->outputpath) > 0);
            $info = [];
            if ($specifiedOutputPath === false) {
                $info = pathinfo($this->file);
                $path = $info['dirname'];
                $this->outputpath = $path;
            } else {
                $path = $this->outputpath;
            }
            $output = $path . DIRECTORY_SEPARATOR . isset($info['filename']) ?? $info['filename'];
            if (!$this->removeoldext) {
                $output .= '.' . $this->pathInfo['extension'];
            }

            if (strlen($this->newext) > 0) {
                $output .= '.' . $this->newext;
            }
            $this->output = $output;
        } else {
            $output = $this->output;
        }

        $compiler->compile($this->file, $output, $this->failonerror);
    }

    /**
     * Process filesets - compiling/generating css files as required.
     *
     * @param SassTaskCompiler $compiler Compiler to use for processing fileset
     *
     * @return void
     */
    public function processFilesets(SassTaskCompiler $compiler): void
    {
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($this->project);
            $files = $ds->getIncludedFiles();
            $dir = $fs->getDir($this->project)->getPath();

            // If output path isn't defined then set it to the path of our fileset.
            $specifiedOutputPath = (strlen($this->outputpath) > 0);
            if ($specifiedOutputPath === false) {
                $this->outputpath = $dir;
            }

            foreach ($files as $file) {
                $fullFilePath = $dir . DIRECTORY_SEPARATOR . $file;
                $this->pathInfo = pathinfo($file);

                $run = true;
                switch (strtolower($this->pathInfo['extension'])) {
                    case 'scss':
                    case 'sass':
                        break;
                    default:
                        $this->log('Ignoring ' . $file, Project::MSG_DEBUG);
                        $run = false;
                }

                if (
                    $run
                    && ($this->extfilter === ''
                        || $this->extfilter === $this->pathInfo['extension'])
                ) {
                    $outputFile = $this->buildOutputFilePath();
                    $compiler->compile($fullFilePath, $outputFile, $this->failonerror);
                }
            }
        }
    }

    /**
     * Builds the full path to the output file based on our settings.
     *
     * @return string
     *
     * @access protected
     */
    protected function buildOutputFilePath()
    {
        $outputFile = $this->outputpath . DIRECTORY_SEPARATOR;

        $subpath = trim($this->pathInfo['dirname'], ' .');

        if ($this->keepsubdirectories === true && strlen($subpath) > 0) {
            $outputFile .= $subpath . DIRECTORY_SEPARATOR;
        }

        $outputFile .= $this->pathInfo['filename'];

        if (!$this->removeoldext) {
            $outputFile .= '.' . $this->pathInfo['extension'];
        }

        if (strlen($this->newext) > 0) {
            $outputFile .= '.' . $this->newext;
        }

        return $outputFile;
    }
}
