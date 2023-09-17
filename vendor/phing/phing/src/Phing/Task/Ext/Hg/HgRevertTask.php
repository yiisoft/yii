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
use Phing\Util\StringHelper;

/**
 * Integration/Wrapper for hg revert
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     HgRevertTask.php
 */
class HgRevertTask extends HgBaseTask
{
    /**
     * All
     *
     * @var bool
     */
    protected $all = false;

    /**
     * Name of file to be reverted.
     *
     * @var string
     */
    protected $file = null;

    /**
     * Revision
     *
     * @var string
     */
    protected $revision = '';

    /**
     * Set whether all files are to be reverted.
     *
     * @param string $value Jenkins style boolean value
     *
     * @return void
     */
    public function setAll($value)
    {
        $this->all = StringHelper::booleanValue($value);
    }

    /**
     * Set filename to be reverted.
     *
     * @param string $file Filename
     *
     * @return void
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Get filename to be reverted.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set revision attribute
     *
     * @param string $revision Revision
     *
     * @return void
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;
    }

    /**
     * The main entry point method.
     *
     * @throws BuildException
     * @return void
     */
    public function main()
    {
        $clone = $this->getFactoryInstance('revert');
        $clone->setQuiet($this->getQuiet());
        $clone->setAll($this->all);
        if ($this->repository === '') {
            $project = $this->getProject();
            $dir = $project->getProperty('application.startdir');
        } else {
            $dir = $this->repository;
        }
        $cwd = getcwd();
        $this->checkRepositoryIsDirAndExists($dir);
        chdir($dir);
        if ($this->revision !== '') {
            $clone->setRev($this->revision);
        }
        if ($this->file !== null) {
            $clone->addName($this->file);
        }

        try {
            $this->log("Executing: " . $clone->asString(), Project::MSG_INFO);
            $output = $clone->execute();
            if ($output !== '') {
                $this->log(PHP_EOL . $output);
            }
        } catch (\Exception $ex) {
            $msg = $ex->getMessage();
            $p = strpos($msg, 'hg returned:');
            if ($p !== false) {
                $msg = substr($msg, $p + 13);
            }
            throw new BuildException($msg);
        }
    }
}
