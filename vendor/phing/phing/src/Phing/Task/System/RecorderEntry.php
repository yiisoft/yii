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
use Phing\Io\FileOutputStream;
use Phing\Io\IOException;
use Phing\Io\OutputStream;
use Phing\Listener\BuildEvent;
use Phing\Listener\BuildLogger;
use Phing\Listener\DefaultLogger;
use Phing\Listener\SubBuildListener;
use Phing\Phing;
use Phing\Project;
use Phing\Util\StringHelper;

/**
 * This is a class that represents a recorder. This is the listener to the
 * build process.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class RecorderEntry implements BuildLogger, SubBuildListener
{
    /**
     * The name of the file associated with this recorder entry.
     *
     * @var string
     */
    private $filename;

    /**
     * The state of the recorder (recorder on or off).
     *
     * @var bool
     */
    private $record = true;

    /**
     * The current verbosity level to record at.
     */
    private $loglevel;

    /**
     * The output OutputStream to record to.
     *
     * @var OutputStream
     */
    private $out;

    /**
     * The start time of the last know target.
     */
    private $targetStartTime;

    /**
     * Strip task banners if true.
     */
    private $emacsMode = false;

    /**
     * project instance the recorder is associated with.
     *
     * @var Project
     */
    private $project;

    /**
     * @param string $name the name of this recorder (used as the filename)
     */
    public function __construct($name)
    {
        $this->targetStartTime = microtime(true);
        $this->filename = $name;
        $this->loglevel = Project::MSG_INFO;
    }

    /**
     * @return string the name of the file the output is sent to
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Turns off or on this recorder.
     *
     * @param null|bool $state true for on, false for off, null for no change
     */
    public function setRecordState($state)
    {
        if (null != $state) {
            $this->flush();
            $this->record = StringHelper::booleanValue($state);
        }
    }

    /**
     * {@inheritDoc}.
     */
    public function buildStarted(BuildEvent $event)
    {
        $this->log('> BUILD STARTED', Project::MSG_DEBUG);
    }

    /**
     * {@inheritDoc}.
     */
    public function buildFinished(BuildEvent $event)
    {
        $this->log('< BUILD FINISHED', Project::MSG_DEBUG);

        if ($this->record && null != $this->out) {
            $error = $event->getException();

            if (null == $error) {
                $this->out->write(Phing::getProperty('line.separator') . 'BUILD SUCCESSFUL' . PHP_EOL);
            } else {
                $this->out->write(
                    Phing::getProperty('line.separator') . 'BUILD FAILED'
                    . Phing::getProperty('line.separator') . PHP_EOL
                );
                $this->out->write($error->getTraceAsString());
            }
        }
        $this->cleanup();
    }

    /**
     * Cleans up any resources held by this recorder entry at the end
     * of a subbuild if it has been created for the subbuild's project
     * instance.
     *
     * @param BuildEvent $event the buildFinished event
     */
    public function subBuildFinished(BuildEvent $event)
    {
        if ($event->getProject() == $this->project) {
            $this->cleanup();
        }
    }

    /**
     * Empty implementation to satisfy the BuildListener interface.
     *
     * @param BuildEvent $event the buildStarted event
     */
    public function subBuildStarted(BuildEvent $event)
    {
    }

    /**
     * {@inheritDoc}.
     */
    public function targetStarted(BuildEvent $event)
    {
        $this->log('>> TARGET STARTED -- ' . $event->getTarget()->getName(), Project::MSG_DEBUG);
        $this->log(
            Phing::getProperty('line.separator') . $event->getTarget()->getName() . ':',
            Project::MSG_INFO
        );
        $this->targetStartTime = microtime(true);
    }

    /**
     * {@inheritDoc}.
     */
    public function targetFinished(BuildEvent $event)
    {
        $this->log('<< TARGET FINISHED -- ' . $event->getTarget()->getName(), Project::MSG_DEBUG);

        $time = DefaultLogger::formatTime(microtime(true) - $this->targetStartTime);

        $this->log($event->getTarget()->getName() . ':  duration ' . $time, Project::MSG_VERBOSE);
        flush();
    }

    /**
     * {@inheritDoc}.
     */
    public function taskStarted(BuildEvent $event)
    {
        $this->log('>>> TASK STARTED -- ' . $event->getTask()->getTaskName(), Project::MSG_DEBUG);
    }

    /**
     * {@inheritDoc}.
     */
    public function taskFinished(BuildEvent $event)
    {
        $this->log('<<< TASK FINISHED -- ' . $event->getTask()->getTaskName(), Project::MSG_DEBUG);
        $this->flush();
    }

    /**
     * {@inheritDoc}.
     */
    public function messageLogged(BuildEvent $event)
    {
        $this->log('--- MESSAGE LOGGED', Project::MSG_DEBUG);

        $buf = '';

        if (null != $event->getTask()) {
            $name = $event->getTask()->getTaskName();

            if (!$this->emacsMode) {
                $label = '[' . $name . '] ';
                $size = DefaultLogger::LEFT_COLUMN_SIZE - strlen($label);

                for ($i = 0; $i < $size; ++$i) {
                    $buf .= ' ';
                }
                $buf .= $label;
            }
        }
        $buf .= $event->getMessage();

        $this->log($buf, $event->getPriority());
    }

    /**
     * {@inheritDoc}.
     */
    public function setMessageOutputLevel($level)
    {
        if ($level >= Project::MSG_ERR && $level <= Project::MSG_DEBUG) {
            $this->loglevel = $level;
        }
    }

    /**
     * {@inheritDoc}.
     */
    public function setOutputStream(OutputStream $output)
    {
        $this->closeFile();
        $this->out = $output;
    }

    /**
     * {@inheritDoc}.
     */
    public function setEmacsMode($emacsMode)
    {
        $this->emacsMode = $emacsMode;
    }

    /**
     * {@inheritDoc}.
     */
    public function setErrorStream(OutputStream $err)
    {
        $this->setOutputStream($err);
    }

    /**
     * Set the project associated with this recorder entry.
     *
     * @param Project $project the project instance
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
        if (null != $this->project) {
            $this->project->addBuildListener($this);
        }
    }

    /**
     * Get the project associated with this recorder entry.
     */
    public function getProject()
    {
        return $this->project;
    }

    public function cleanup()
    {
        $this->closeFile();
        if (null != $this->project) {
            $this->project->removeBuildListener($this);
        }
        $this->project = null;
    }

    /**
     * Initially opens the file associated with this recorder.
     * Used by Recorder.
     *
     * @param bool $append indicates if output must be appended to the logfile or that
     *                     the logfile should be overwritten
     *
     * @throws BuildException
     */
    public function openFile($append)
    {
        $this->openFileImpl($append);
    }

    /**
     * Closes the file associated with this recorder.
     * Used by Recorder.
     */
    public function closeFile()
    {
        if (null != $this->out) {
            $this->out->close();
            $this->out = null;
        }
    }

    /**
     * Re-opens the file associated with this recorder.
     * Used by Recorder.
     *
     * @throws BuildException
     */
    public function reopenFile()
    {
        $this->openFileImpl(true);
    }

    /**
     * The thing that actually sends the information to the output.
     *
     * @param string $mesg  the message to log
     * @param int    $level the verbosity level of the message
     */
    private function log($mesg, $level)
    {
        if ($this->record && ($level <= $this->loglevel) && null != $this->out) {
            $this->out->write($mesg . PHP_EOL);
        }
    }

    private function flush()
    {
        if ($this->record && null != $this->out) {
            $this->out->flush();
        }
    }

    private function openFileImpl($append)
    {
        if (null == $this->out) {
            try {
                $this->out = new FileOutputStream($this->filename, $append);
            } catch (IOException $ioe) {
                throw new BuildException('Problems opening file using a recorder entry', $ioe);
            }
        }
    }
}
