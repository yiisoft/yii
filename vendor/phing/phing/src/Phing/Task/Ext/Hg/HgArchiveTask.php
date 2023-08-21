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
 * Integration/Wrapper for hg archive
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     HgArchiveTask.php
 */
class HgArchiveTask extends HgBaseTask
{
    /**
     * Which revision to archive.
     *
     * @var string
     */
    protected $revision = '';

    /**
     * Name of destination archive file.
     *
     * @var string
     */
    protected $destination = null;

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
     * Set Destination attribute
     *
     * @param string $destination Destination filename
     *
     * @return void
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    /**
     * The main entry point for the task.
     *
     * @return void
     */
    public function main()
    {
        $clone = $this->getFactoryInstance('archive');
        if ($this->revision !== '') {
            $clone->setRev($this->revision);
        }

        if ($this->destination === null) {
            throw new BuildException("Destination must be set.");
        }
        $clone->setDestination($this->destination);

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
