<?php

/**
 * Utilise Mercurial from within Phing.
 *
 * PHP Version 5.4
 *
 * @category Tasks
 * @package  phing.tasks.ext
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     https://github.com/kenguest/Phing-HG
 */

namespace Phing\Task\Ext\Hg;

use Phing\Exception\BuildException;
use Phing\Project;
use Phing\Type\FileSet;
use Phing\Type\Element\FileSetAware;

/**
 * Integration/Wrapper for hg add
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     HgAddTask.php
 */
class HgAddTask extends HgBaseTask
{
    use FileSetAware;

    /**
     * Array of files to ignore
     *
     * @var string[]
     */
    protected $ignoreFile = [];

    /**
     * The main entry point method.
     *
     * @throws BuildException
     * @return void
     */
    public function main()
    {
        $filesAdded = false;
        $clone = $this->getFactoryInstance('add');
        $clone->setQuiet($this->getQuiet());

        $cwd = getcwd();
        $project = $this->getProject();
        if ($this->repository === '') {
            $dir = $project->getProperty('application.startdir');
        } else {
            $dir = $this->repository;
        }

        if (!file_exists($dir)) {
            throw new BuildException("\"$dir\" does not exist.");
        }

        if (!is_dir($dir)) {
            throw new BuildException("\"$dir\" is not a directory.");
        }

        chdir($dir);

        if (file_exists('.hgignore')) {
            $this->loadIgnoreFile();
        }
        if (count($this->filesets)) {
            $this->log('filesets set', Project::MSG_DEBUG);
            /**
             * $fs is a FileSet
             *
             * @var $fs FileSet
             */
            foreach ($this->filesets as $fs) {
                $ds = $fs->getDirectoryScanner($project);
                $fromDir = $fs->getDir($project);
                if ($fromDir->getName() === '.') {
                    $statusClone = $this->getFactoryInstance('status');
                    $statusClone->setUnknown(true);
                    $statusClone->setNoStatus(true);
                    $statusClone->setRepository($this->getRepository());
                    $statusOut = $statusClone->execute();
                    if ($statusOut !== '') {
                        $files = explode(PHP_EOL, $statusOut);
                        foreach ($files as $file) {
                            if ($file != '') {
                                $clone->addFile($file);
                                $filesAdded = true;
                            }
                        }
                    }
                }
            }
        }

        if ($filesAdded) {
            try {
                $this->log("Executing: " . $clone->asString(), Project::MSG_INFO);
                $output = $clone->execute();
                if ($output !== '') {
                    $this->log($output);
                }
            } catch (\Exception $ex) {
                $msg = $ex->getMessage();
                $this->log("Exception: $msg", Project::MSG_INFO);
                $p = strpos($msg, 'hg returned:');
                if ($p !== false) {
                    $msg = substr($msg, $p + 13);
                }
                chdir($cwd);
                throw new BuildException($msg);
            }
        }
        chdir($cwd);
    }

    /**
     * Load .hgignore file.
     *
     * @return void
     */
    public function loadIgnoreFile()
    {
        $ignores = [];
        $lines = file('.hgignore');
        foreach ($lines as $line) {
            $nline =  trim($line);
            $nline = preg_replace('/\/\*$/', '/', $nline);
            $ignores[] = $nline;
        }
        $this->ignoreFile = $ignores;
    }

    /**
     * Determine if a file is to be ignored.
     *
     * @param string $file filename
     *
     * @return bool
     */
    public function fileIsIgnored($file)
    {
        $line = $this->ignoreFile[0];
        $mode = 'regexp';
        $ignored = false;
        if (
            preg_match('#^syntax\s*:\s*(glob|regexp)$#', $line, $matches)
            || $matches[1] === 'glob'
        ) {
            $mode = 'glob';
        }
        if ($mode === 'glob') {
            $ignored = $this->ignoredByGlob($file);
        } elseif ($mode === 'regexp') {
            $ignored = $this->ignoredByRegex($file);
        }
        return $ignored;
    }

    /**
     * Determine if file is ignored by glob pattern.
     *
     * @param string $file filename
     *
     * @return bool
     */
    public function ignoredByGlob($file)
    {
        $lfile = $file;
        if (strpos($lfile, './') === 0) {
            $lfile = substr($lfile, 2);
        }
        foreach ($this->ignoreFile as $line) {
            if (strpos($lfile, $line) === 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Is file ignored by regex?
     *
     * @param string $file Filename
     *
     * @return bool
     */
    public function ignoredByRegex($file)
    {
        return true;
    }
}
