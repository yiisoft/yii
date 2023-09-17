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
 * Integration/Wrapper for hg update
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     HgUpdateTask.php
 */
class HgUpdateTask extends HgBaseTask
{
    /**
     * Branch argument
     *
     * Defaults to 'default'
     *
     * @var string
     */
    protected $branch = 'default';

    /**
     * Clean argument
     *
     * @var bool
     */
    protected $clean = false;

    /**
     * Set 'clean' attribute.
     *
     * @param string $value Clean attribute value
     *
     * @return void
     */
    public function setClean($value)
    {
        $this->clean = StringHelper::booleanValue($value);
    }

    /**
     * Get 'clean' attribute.
     *
     * @return bool
     */
    public function getClean()
    {
        return $this->clean;
    }

    /**
     * Set branch attribute
     *
     * @param string $value Branch name
     *
     * @return void
     */
    public function setBranch($value)
    {
        $this->branch = $value;
    }

    /**
     * Get branch attribute
     *
     * @return string
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * The main entry point method.
     *
     * @throws BuildException
     * @return void
     */
    public function main()
    {
        $pull = $this->getFactoryInstance('update');
        try {
            $pull->setBranch($this->getBranch());
        } catch (\Exception $ex) {
            $this->log("Caught: " . $ex->getMessage(), Project::MSG_DEBUG);
        }
        $pull->setClean($this->getClean());
        $pull->setQuiet($this->getQuiet());

        $cwd = getcwd();

        if ($this->repository === '') {
            $prog = $this->getProject();
            $dir = $prog->getProperty('application.startdir');
        } else {
            $dir = $this->repository;
        }

        $this->checkRepositoryIsDirAndExists($dir);
        chdir($dir);
        try {
            $this->log("Executing: " . $pull->asString(), Project::MSG_INFO);
            $output = $pull->execute();
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
