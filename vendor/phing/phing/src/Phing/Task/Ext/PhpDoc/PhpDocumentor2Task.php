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

namespace Phing\Task\Ext\PhpDoc;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileSystem;
use Phing\Phing;
use Phing\Project;
use Phing\Task;
use Phing\Type\Element\FileSetAware;
use phpDocumentor\Bootstrap;
use phpDocumentor\Descriptor\Cache\ProjectDescriptorMapper;
use phpDocumentor\Fileset\Collection;

/**
 * PhpDocumentor2 Task (http://www.phpdoc.org)
 * Based on the DocBlox Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @since   2.4.10
 * @package phing.tasks.ext.phpdoc
 */
class PhpDocumentor2Task extends Task
{
    use FileSetAware;

    /**
     * Destination/target directory
     *
     * @var File
     */
    private $destDir = null;

    /**
     * name of the template to use
     *
     * @var string
     */
    private $template = "responsive-twig";

    /**
     * Title of the project
     *
     * @var string
     */
    private $title = "API Documentation";

    /**
     * Name of default package
     *
     * @var string
     */
    private $defaultPackageName = "Default";

    /**
     * Path to the phpDocumentor .phar
     *
     * @var string
     */
    private $pharLocation = '';

    /**
     * Path to the phpDocumentor 2 source
     *
     * @var string
     */
    private $phpDocumentorPath = "";

    /**
     * @var \phpDocumentor\Application
     */
    private $app = null;

    /**
     * Sets destination/target directory
     *
     * @param File $destDir
     */
    public function setDestDir(File $destDir)
    {
        $this->destDir = $destDir;
    }

    /**
     * Convenience setter (@see setDestDir)
     *
     * @param File $output
     */
    public function setOutput(File $output)
    {
        $this->destDir = $output;
    }

    /**
     * Sets the template to use
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = (string) $template;
    }

    /**
     * Sets the title of the project
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;
    }

    /**
     * Sets the default package name
     *
     * @param string $defaultPackageName
     */
    public function setDefaultPackageName($defaultPackageName)
    {
        $this->defaultPackageName = (string) $defaultPackageName;
    }

    /**
     * @param string $pharLocation
     */
    public function setPharLocation($pharLocation)
    {
        $this->pharLocation = $pharLocation;
    }

    /**
     * Finds and initializes the phpDocumentor installation
     */
    private function initializePhpDocumentor()
    {
        $phpDocumentorPath = '';

        if (!empty($this->pharLocation)) {
            include_once 'phar://' . $this->pharLocation . '/vendor/autoload.php';

            if (!class_exists('phpDocumentor\\Bootstrap')) {
                throw new BuildException(
                    $this->pharLocation . ' does not look like a phpDocumentor 2 .phar'
                );
            }
        } elseif (class_exists('Composer\\Autoload\\ClassLoader', false)) {
            if (!class_exists('phpDocumentor\\Bootstrap')) {
                throw new BuildException('You need to install phpDocumentor 2 or add your include path to your composer installation.');
            }
        } else {
            $phpDocumentorPath = $this->findPhpDocumentorPath();

            if (empty($phpDocumentorPath)) {
                throw new BuildException("Please make sure phpDocumentor 2 is installed and on the include_path.");
            }

            set_include_path($phpDocumentorPath . PATH_SEPARATOR . get_include_path());

            include_once $phpDocumentorPath . '/phpDocumentor/Bootstrap.php';
        }

        /** @phpstan-ignore-next-line */
        $this->app = Bootstrap::createInstance()->initialize();

        $this->phpDocumentorPath = $phpDocumentorPath;
    }

    /**
     * Build a list of files (from the fileset elements)
     * and call the phpDocumentor parser
     *
     * @return string
     */
    private function parseFiles()
    {
        $parser = $this->app['parser'];
        $builder = $this->app['descriptor.builder'];

        $builder->createProjectDescriptor();
        $projectDescriptor = $builder->getProjectDescriptor();
        $projectDescriptor->setName($this->title);

        $paths = [];

        // filesets
        foreach ($this->filesets as $fs) {
            $ds = $fs->getDirectoryScanner($this->project);
            $dir = $fs->getDir($this->project);
            $srcFiles = $ds->getIncludedFiles();

            foreach ($srcFiles as $file) {
                $paths[] = $dir . FileSystem::getFileSystem()->getSeparator() . $file;
            }
        }

        $this->project->log("Will parse " . count($paths) . " file(s)", Project::MSG_VERBOSE);

        /** @phpstan-ignore-next-line */
        $files = new Collection();
        $files->addFiles($paths);

        /** @phpstan-ignore-next-line */
        $mapper = new ProjectDescriptorMapper($this->app['descriptor.cache']);
        $mapper->garbageCollect($files);
        $mapper->populate($projectDescriptor);

        $parser->setPath($files->getProjectRoot());
        $parser->setDefaultPackageName($this->defaultPackageName);

        $parser->parse($builder, $files);

        $mapper->save($projectDescriptor);

        return $mapper;
    }

    /**
     * Transforms the parsed files
     */
    private function transformFiles()
    {
        $transformer = $this->app['transformer'];
        $compiler = $this->app['compiler'];
        $builder = $this->app['descriptor.builder'];
        $projectDescriptor = $builder->getProjectDescriptor();

        $transformer->getTemplates()->load($this->template, $transformer);
        $transformer->setTarget($this->destDir->getAbsolutePath());

        foreach ($compiler as $pass) {
            $pass->execute($projectDescriptor);
        }
    }

    /**
     * Runs phpDocumentor 2
     */
    public function run()
    {
    }

    /**
     * Find the correct php documentor path
     *
     * @return null|string
     */
    private function findPhpDocumentorPath()
    {
        $phpDocumentorPath = null;
        $directories = ['phpDocumentor', 'phpdocumentor'];
        foreach ($directories as $directory) {
            foreach (Phing::explodeIncludePath() as $path) {
                $testPhpDocumentorPath = $path . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . 'src';
                if (file_exists($testPhpDocumentorPath)) {
                    $phpDocumentorPath = $testPhpDocumentorPath;
                }
            }
        }

        return $phpDocumentorPath;
    }

    /**
     * Task entry point
     *
     * @see Task::main()
     */
    public function main()
    {
        if (empty($this->destDir)) {
            throw new BuildException("You must supply the 'destdir' attribute", $this->getLocation());
        }

        if (empty($this->filesets)) {
            throw new BuildException("You have not specified any files to include (<fileset>)", $this->getLocation());
        }

        $this->initializePhpDocumentor();

        $cache = $this->app['descriptor.cache'];
        $cache->getOptions()->setCacheDir($this->destDir->getAbsolutePath());

        $this->parseFiles();

        $this->project->log("Transforming...", Project::MSG_VERBOSE);

        $this->transformFiles();
    }
}
