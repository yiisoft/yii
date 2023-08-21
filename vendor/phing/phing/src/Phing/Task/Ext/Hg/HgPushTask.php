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
 * Integration/Wrapper for hg push
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     HgPushTask.php
 */
class HgPushTask extends HgBaseTask
{
    /**
     * Whether the task should halt if an error occurs.
     *
     * @var bool
     */
    protected $haltonerror = false;

    /**
     * Set haltonerror attribute.
     *
     * @param string $halt 'yes', or '1' to halt.
     *
     * @return void
     */
    public function setHaltonerror($halt)
    {
        $this->haltonerror = StringHelper::booleanValue($halt);
    }

    /**
     * Return haltonerror value.
     *
     * @return bool
     */
    public function getHaltonerror()
    {
        return $this->haltonerror;
    }

    /**
     * The main entry point method.
     *
     * @throws BuildException
     * @return void
     */
    public function main()
    {
        $clone = $this->getFactoryInstance('push');
        $this->log('Pushing...', Project::MSG_INFO);
        $clone->setInsecure($this->getInsecure());
        $clone->setQuiet($this->getQuiet());
        if ($this->repository === '') {
            $project = $this->getProject();
            $dir = $project->getProperty('application.startdir');
        } else {
            $dir = $this->repository;
        }
        $cwd = getcwd();
        $this->checkRepositoryIsDirAndExists($dir);
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
            if ($this->haltonerror) {
                throw new BuildException($msg);
            }
            $this->log($msg, Project::MSG_ERR);
        }
        chdir($cwd);
    }
}
