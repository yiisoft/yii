<?php

namespace Phing\Task\Ext;

use Exception;
use Phing\Exception\BuildException;
use Phing\Io\{FileSystem, UnixFileSystem, WindowsFileSystem};
use Phing\{Project, Task};
use Phing\Task\System\ExecTask;
use Throwable;

/**
 * Opens a file or URL in the user's preferred application.
 *
 * @author Jawira Portugal <dev@tugal.be>
 */
class OpenTask extends Task
{
    /**
     * @var string Can be a file, directory or URL
     */
    protected $path;

    /**
     * @var UnixFileSystem|WindowsFileSystem
     */
    protected $fileSystem;

    /**
     * @var ExecTask
     */
    protected $execTask;

    /**
     * Path to be opened later
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Initialize dependencies
     */
    public function init(): void
    {
        $this->path       = '';
        $this->fileSystem = FileSystem::getFileSystem();
        $this->execTask   = new ExecTask();
    }

    /**
     * Main method
     */
    public function main(): void
    {
        try {
            $this->log("Path: $this->path", Project::MSG_VERBOSE);
            if (empty($this->path)) {
                throw new Exception('"path" is required');
            }
            $executable = $this->retrieveOpenerTool();
            $this->openPath($executable, $this->path);
        } catch (Throwable $th) {
            $this->log($th->getMessage(), Project::MSG_ERR);
            throw new BuildException("Error while opening $this->path");
        }
    }

    /**
     * Retrieves right opener tool to call according to current OS
     */
    public function retrieveOpenerTool(): string
    {
        $executables = ($this->fileSystem instanceof UnixFileSystem) ? ['xdg-open', 'wslview', 'open'] : ['start'];
        $which       = null;

        foreach ($executables as $executable) {
            $which = $this->fileSystem->which($executable, null);
            if ($which) {
                $this->log("Opener tool found: $which", Project::MSG_VERBOSE);
                break;
            }
        }

        if (empty($which)) {
            throw new Exception('Cannot retrieve opener tool');
        }

        return $which;
    }

    /**
     * Run executable with path as argument
     */
    protected function openPath(string $executable, string $path): void
    {
        $this->log("Opening $path");
        $this->execTask->setProject($this->getProject());
        $this->execTask->setLocation($this->getLocation());
        $this->execTask->setExecutable($executable);
        $this->execTask->setSpawn(true);
        $this->execTask->setPassthru(false);
        $this->execTask->createArg()->setValue($path);
        $this->execTask->main();
    }
}
