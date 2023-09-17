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

/**
 * Integration/Wrapper for hg init
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     HgInitTask.php
 */
class HgInitTask extends HgBaseTask
{
    /**
     * Path to target directory
     *
     * @var string
     */
    protected $targetPath;

    /**
     * Set path to source repo
     *
     * @param string $targetPath Path to repository used as source
     *
     * @return void
     */
    public function setTargetPath($targetPath)
    {
        $this->targetPath = $targetPath;
    }

    /**
     * Main entry point for this task.
     *
     * @return void
     */
    public function main()
    {
        $clone = $this->getFactoryInstance('init');
        $this->log('Initializing', Project::MSG_INFO);
        $clone->setQuiet($this->getQuiet());
        $clone->setInsecure($this->getInsecure());
        $cwd = getcwd();
        if ($this->repository === '') {
            $project = $this->getProject();
            $dir = $project->getProperty('application.startdir');
        } else {
            $dir = $this->repository;
        }
        if (!is_dir($dir)) {
            throw new BuildException("$dir is not a directory.");
        }
        chdir($dir);
        try {
            $this->log("Executing: " . $clone->asString(), Project::MSG_INFO);
            $output = $clone->execute();
            if ($output !== '') {
                $this->log($output);
            }
        } catch (\Exception $ex) {
            $msg = $ex->getMessage();
            $p = strpos($msg, 'hg returned:');
            if ($p !== false) {
                $msg = substr($msg, $p + 13);
            }
            chdir($cwd);
            throw new BuildException($msg);
        }
        chdir($cwd);
    }
}
