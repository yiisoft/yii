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
 * Integration/Wrapper for hg commit
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     HgCommitTask.php
 */
class HgCommitTask extends HgBaseTask
{
    /**
     * Message to be recorded against commit.
     *
     * @var string
     */
    protected $message = '';

    /**
     * Set message to be used.
     *
     * @param string $message Message to use
     *
     * @return void
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get message to apply for the commit.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * The main entry point method.
     *
     * @throws BuildException If message is not set
     * @throws BuildException If error occurs during commit
     * @return void
     */
    public function main()
    {
        $message = $this->getMessage();
        if ($message === '') {
            throw new BuildException('"message" is a required parameter');
        }

        $user = $this->getUser();

        $clone = $this->getFactoryInstance('commit');
        $msg = sprintf("Commit: '%s'", $message);
        $this->log($msg, Project::MSG_INFO);
        $clone->setQuiet($this->getQuiet());
        $clone->setMessage($message);

        if (trim($user) === "") {
            throw new BuildException('"user" parameter can not be set to ""');
        }
        if ($user !== null) {
            $clone->setUser($user);
            $this->log("Commit: user = '$user'", Project::MSG_VERBOSE);
        }

        if ($this->repository === '') {
            $project = $this->getProject();
            $dir = $project->getProperty('application.startdir');
        } else {
            $dir = $this->repository;
        }
        $this->log('DIR:' . $dir, Project::MSG_INFO);
        $this->log('REPO: ' . $this->repository, Project::MSG_INFO);
        $cwd = getcwd();
        chdir($dir);

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
        chdir($cwd);
    }
}
