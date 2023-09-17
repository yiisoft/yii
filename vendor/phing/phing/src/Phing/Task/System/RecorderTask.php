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

namespace Phing\Task\System;

use Phing\Exception\BuildException;
use Phing\Listener\BuildEvent;
use Phing\Listener\SubBuildListener;
use Phing\Project;
use Phing\Task;
use Phing\Util\StringHelper;

/**
 * Adds a listener to the current build process that records the
 * output to a file.
 * <p>Several recorders can exist at the same time.  Each recorder is
 * associated with a file.  The filename is used as a unique identifier for
 * the recorders.  The first call to the recorder task with an unused filename
 * will create a recorder (using the parameters provided) and add it to the
 * listeners of the build.  All subsequent calls to the recorder task using
 * this filename will modify that recorders state (recording or not) or other
 * properties (like logging level).</p>
 * <p>Some technical issues: the file's print stream is flushed for &quot;finished&quot;
 * events (buildFinished, targetFinished and taskFinished), and is closed on
 * a buildFinished event.</p>.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class RecorderTask extends Task implements SubBuildListener
{
    /**
     * The name of the file to record to.
     */
    private $filename;
    /**
     * Whether or not to append. Need bool to record an unset state (null).
     */
    private $append;
    /**
     * Whether to start or stop recording. Need bool to record an unset
     * state (null).
     */
    private $start;
    /**
     * The level to log at. A level of -1 means not initialized yet.
     */
    private $loglevel = -1;
    /**
     * Strip task banners if true.
     */
    private $emacsMode = false;

    private $logLevelChoices = [
        'error' => 0,
        'warn' => 1,
        'info' => 2,
        'verbose' => 3,
        'debug' => 4,
    ];

    /**
     * The list of recorder entries.
     *
     * @var RecorderEntry[]
     */
    private static $recorderEntries = [];

    /**
     * Overridden so we can add the task as build listener.
     */
    public function init()
    {
        $this->getProject()->addBuildListener($this);
    }

    /**
     * Sets the name of the file to log to, and the name of the recorder
     * entry.
     *
     * @param string $fname file name of logfile
     */
    public function setName($fname)
    {
        $this->filename = $fname;
    }

    /**
     * Sets the action for the associated recorder entry.
     *
     * @param string $action the action for the entry to take: start or stop
     */
    public function setAction($action)
    {
        $this->start = 'start' === strtolower($action);
    }

    /**
     * Whether or not the logger should append to a previous file.
     *
     * @param bool $append if true, append to a previous file
     */
    public function setAppend(bool $append)
    {
        $this->append = $append;
    }

    /**
     * Set emacs mode.
     *
     * @param bool $emacsMode if true use emacs mode
     */
    public function setEmacsMode($emacsMode)
    {
        $this->emacsMode = $emacsMode;
    }

    /**
     * Sets the level to which this recorder entry should log to.
     *
     * @param string $level the level to set
     */
    public function setLoglevel($level)
    {
        $this->loglevel = $level;
    }

    /**
     * The main execution.
     *
     * @throws BuildException on error
     */
    public function main()
    {
        if (null == $this->filename) {
            throw new BuildException('No filename specified');
        }

        $this->getProject()->log('setting a recorder for name ' . $this->filename, Project::MSG_DEBUG);

        // get the recorder entry
        $recorder = $this->getRecorder($this->filename, $this->getProject());
        // set the values on the recorder
        if (-1 === $this->loglevel) {
            $recorder->setMessageOutputLevel($this->loglevel);
        } elseif (isset($this->logLevelChoices[$this->loglevel])) {
            $recorder->setMessageOutputLevel($this->logLevelChoices[$this->loglevel]);
        } else {
            throw new BuildException('Loglevel should be one of (error|warn|info|verbose|debug).');
        }

        $recorder->setEmacsMode(StringHelper::booleanValue($this->emacsMode));
        if (null != $this->start) {
            if (StringHelper::booleanValue($this->start)) {
                $recorder->reopenFile();
                $recorder->setRecordState($this->start);
            } else {
                $recorder->setRecordState($this->start);
                $recorder->closeFile();
            }
        }
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored
     */
    public function buildStarted(BuildEvent $event)
    {
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored
     */
    public function subBuildStarted(BuildEvent $event)
    {
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored
     */
    public function targetStarted(BuildEvent $event)
    {
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored
     */
    public function targetFinished(BuildEvent $event)
    {
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored
     */
    public function taskStarted(BuildEvent $event)
    {
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored
     */
    public function taskFinished(BuildEvent $event)
    {
    }

    /**
     * Empty implementation required by SubBuildListener interface.
     *
     * @param BuildEvent $event ignored
     */
    public function messageLogged(BuildEvent $event)
    {
    }

    /**
     * Cleans recorder registry.
     *
     * @param BuildEvent $event ignored
     */
    public function buildFinished(BuildEvent $event)
    {
        $this->cleanup();
    }

    /**
     * Cleans recorder registry, if this is the subbuild the task has
     * been created in.
     *
     * @param BuildEvent $event ignored
     */
    public function subBuildFinished(BuildEvent $event)
    {
        if ($event->getProject() == $this->getProject()) {
            $this->cleanup();
        }
    }

    /**
     * Gets the recorder that's associated with the passed in name. If the
     * recorder doesn't exist, then a new one is created.
     *
     * @param string  $name the name of the recorder
     * @param Project $proj the current project
     *
     * @throws BuildException on error
     *
     * @return RecorderEntry a recorder
     */
    protected function getRecorder($name, Project $proj)
    {
        // create a recorder entry
        $entry = self::$recorderEntries[$name] ?? new RecorderEntry($name);

        if (null == $this->append) {
            $entry->openFile(false);
        } else {
            $entry->openFile(StringHelper::booleanValue($this->append));
        }
        $entry->setProject($proj);
        self::$recorderEntries[$name] = $entry;

        return $entry;
    }

    /**
     * cleans recorder registry and removes itself from BuildListener list.
     */
    private function cleanup()
    {
        $entries = self::$recorderEntries;
        foreach ($entries as $key => $entry) {
            if ($entry->getProject() == $this->getProject()) {
                unset(self::$recorderEntries[$key]);
            }
        }
        $this->getProject()->removeBuildListener($this);
    }
}
