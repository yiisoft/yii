<?php

/**
 * Utilise notify-send from within Phing.
 *
 * PHP Version 5
 *
 * @category Tasks
 *
 * @author   Ken Guest <ken@linux.ie>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 *
 * @see     https://github.com/kenguest/Phing-NotifySendTask
 */

namespace Phing\Task\Ext;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileSystem;
use Phing\Project;
use Phing\Task;
use Phing\Util\StringHelper;

/**
 * NotifySendTask.
 *
 * @category Tasks
 *
 * @author   Ken Guest <ken@linux.ie>
 * @license  LGPL (see http://www.gnu.org/licenses/lgpl.html)
 *
 * @see     NotifySendTask.php
 */
class NotifySendTask extends Task
{
    protected $msg;
    protected $title;
    protected $icon = 'info';
    protected $silent = false;

    /**
     * Set icon attribute.
     *
     * @param \Phing\Io\File $icon name/location of icon
     */
    public function setIcon(File $icon)
    {
        if ($icon->isFile()) {
            $this->log(sprintf('Using "%s" as icon.', $icon), Project::MSG_VERBOSE);
            $this->icon = $icon->getAbsoluteFile();

            return;
        }

        $this->log(sprintf('"%s" is not a file. Assuming it is a stock icon name.', $icon->getName()), Project::MSG_WARN);
        $this->icon = $icon->getName();
    }

    /**
     * Get icon to be used (filename or generic name).
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set to a true value to not execute notifysend command.
     *
     * @param string $silent don't execute notifysend? Truthy value
     */
    public function setSilent($silent)
    {
        $this->silent = StringHelper::booleanValue($silent);
    }

    /**
     * Set title attribute.
     *
     * @param string $title Title to display
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get Title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set msg attribute.
     *
     * @param string $msg Message
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }

    /**
     * Get message.
     *
     * @return string
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * The main entry point method.
     *
     * @throws BuildException
     */
    public function main()
    {
        $msg = '';
        $title = 'Phing';
        $executable = 'notify-send';

        if ('' != $this->title) {
            $title = "'" . $this->title . "'";
        }

        if ('' != $this->msg) {
            $msg = "'" . $this->msg . "'";
        }

        $cmd = $executable . ' -i ' . $this->icon . ' ' . $title . ' ' . $msg;

        $this->log(sprintf("Title: '%s'", $title), Project::MSG_DEBUG);
        $this->log(sprintf("Message: '%s'", $msg), Project::MSG_DEBUG);
        $this->log($msg, Project::MSG_INFO);

        $this->log(sprintf('cmd: %s', $cmd), Project::MSG_DEBUG);
        if (!$this->silent) {
            $fs = FileSystem::getFileSystem();
            if (false !== $fs->which($executable)) {
                exec(escapeshellcmd($cmd), $output, $return);
                if (0 !== $return) {
                    throw new BuildException('Notify task failed.');
                }
            } else {
                $this->log("Executable ({$executable}) not found", Project::MSG_DEBUG);
            }
        } else {
            $this->log('Silent flag set; not executing', Project::MSG_DEBUG);
        }
    }
}

// vim:set et ts=4 sw=4:
