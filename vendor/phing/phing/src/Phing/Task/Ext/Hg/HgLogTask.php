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
 * Integration/Wrapper for hg log
 *
 * @category Tasks
 * @package  phing.tasks.ext.hg
 * @author   Ken Guest <kguest@php.net>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 * @link     HgLogTask.php
 */
class HgLogTask extends HgBaseTask
{
    /**
     * Maximum number of changes to get. See --limit
     *
     * @var int
     */
    protected $maxCount = null;

    /**
     * Commit format/template. See --template
     *
     * @var string
     */
    protected $format = null;

    /**
     * Revision
     *
     * @var string
     */
    protected $revision = '';

    /**
     * Propery name to set the output to.
     *
     * @var string
     */
    protected $outputProperty = null;

    /**
     * Set maximum number of changes to get.
     *
     * @param int $count Maximum number of log entries to retrieve.
     *
     * @return void
     */
    public function setMaxcount($count)
    {
        $this->maxCount = $count;
    }

    /**
     * Retrieve max count of commits to limit to.
     *
     * @return int
     */
    public function getMaxcount()
    {
        return $this->maxCount;
    }

    /**
     * Template/log format.
     *
     * @param string $format Log format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Get the log format/template
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Property to assign output to.
     *
     * @param string $property name of property to assign output to.
     *
     * @return void
     */
    public function setOutputProperty($property)
    {
        $this->outputProperty = $property;
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
     * Main entry point for this task
     *
     * @return void
     */
    public function main()
    {
        $clone = $this->getFactoryInstance('log');

        if ($this->repository === '') {
            $project = $this->getProject();
            $dir = $project->getProperty('application.startdir');
        } else {
            $dir = $this->repository;
        }
        $clone->setCwd($dir);

        if ($this->maxCount !== null) {
            $max = filter_var($this->maxCount, FILTER_VALIDATE_INT);
            if ($max) {
                $max = (int) $this->maxCount;
            }
            if (!$max || (int) $this->maxCount <= 0) {
                throw new BuildException("maxcount should be a positive integer.");
            }
            $clone->setLimit('' . $this->maxCount);
        }

        if ($this->format !== null) {
            $clone->setTemplate($this->format);
        }

        if ($this->revision !== '') {
            $clone->setRev($this->revision);
        }

        try {
            $this->log("Executing: " . $clone->asString(), Project::MSG_INFO);
            $output = $clone->execute();
            if ($this->outputProperty !== null) {
                $this->project->setProperty($this->outputProperty, $output);
            } else {
                if ($output !== '') {
                    $this->log(PHP_EOL . $output);
                }
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
