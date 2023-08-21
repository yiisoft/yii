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

namespace Phing\Task\Ext;

use Phing\Exception\BuildException;
use Phing\Project;
use Phing\Task;

/**
 * The FileSyncTask class copies files either to or from a remote host, or locally
 * on the current host. It allows rsync to transfer the differences between two
 * sets of files across the network connection, using an efficient checksum-search
 * algorithm.
 *
 * There are 4 different ways of using FileSyncTask:
 *
 *   1. For copying local files.
 *   2. For copying from the local machine to a remote machine using a remote
 *      shell program as the transport (ssh).
 *   3. For copying from a remote machine to the local machine using a remote
 *      shell program.
 *   4. For listing files on a remote machine.
 *
 * This is extended from Federico's original code, all his docs are kept in here below.
 *
 * @author  Federico Cargnelutti <fede.carg@gmail.com>
 * @author  Anton Stöckl <anton@stoeckl.de>
 *
 * @version $Revision$
 *
 * @see     http://svn.fedecarg.com/repo/Phing/tasks/ext/FileSyncTask.php
 *
 * @example http://fedecarg.com/wiki/FileSyncTask
 */
class FileSyncTask extends Task
{
    /**
     * Path to rsync command.
     *
     * @var string
     */
    protected $rsyncPath = '/usr/bin/rsync';

    /**
     * Source directory.
     * For remote sources this must contain user and host, e.g.: user@host:/my/source/dir.
     *
     * @var string
     */
    protected $sourceDir;

    /**
     * Destination directory.
     * For remote targets this must contain user and host, e.g.: user@host:/my/target/dir.
     *
     * @var string
     */
    protected $destinationDir;

    /**
     * Remote host.
     *
     * @var string
     */
    protected $remoteHost;

    /**
     * Rsync auth username.
     *
     * @var string
     */
    protected $remoteUser;

    /**
     * Rsync auth password.
     *
     * @var string
     */
    protected $remotePass;

    /**
     * Remote shell.
     *
     * @var string
     */
    protected $remoteShell;

    /**
     * Exclude file matching pattern.
     * Use comma seperated values to exclude multiple files/directories, e.g.: a,b.
     *
     * @var string
     */
    protected $exclude;

    /**
     * Excluded patterns file.
     *
     * @var string
     */
    protected $excludeFile;

    /**
     * This option creates a backup so users can rollback to an existing restore
     * point. The remote directory is copied to a new directory specified by the
     * user.
     *
     * @var string
     */
    protected $backupDir;

    /**
     * Default command options.
     * r - recursive
     * p - preserve permissions
     * K - treat symlinked dir on receiver as dir
     * z - compress
     * l - copy symlinks as symlinks.
     *
     * @var string
     */
    protected $defaultOptions = '-rpKzl';

    /**
     * Command options.
     *
     * @var string
     */
    protected $options;

    /**
     * Connection type.
     *
     * @var bool
     */
    protected $isRemoteConnection = false;

    /**
     * This option increases the amount of information you are given during the
     * transfer. The verbose option set to true will give you information about
     * what files are being transferred and a brief summary at the end.
     *
     * @var bool
     */
    protected $verbose = true;

    /**
     * This option makes rsync perform a trial run that doesn’t make any changes
     * (and produces mostly the same output as a real run).
     *
     * @var bool
     */
    protected $dryRun = false;

    /**
     * This option makes requests a simple itemized list of the changes that are
     * being made to each file, including attribute changes.
     *
     * @var bool
     */
    protected $itemizeChanges = false;

    /**
     * This option will cause rsync to skip files based on checksum, not mod-time & size.
     *
     * @var bool
     */
    protected $checksum = false;

    /**
     * This option deletes files that don't exist on sender.
     *
     * @var bool
     */
    protected $delete = false;

    /**
     * Identity file.
     *
     * @var string
     */
    protected $identityFile;

    /**
     * Remote port for syncing via SSH.
     *
     * @var int
     */
    protected $remotePort = 22;

    /**
     * Phing's main method. Wraps the executeCommand() method.
     */
    public function main()
    {
        $this->executeCommand();
    }

    /**
     * Executes the rsync command and returns the exit code.
     *
     * @throws BuildException
     *
     * @return int return code from execution
     */
    public function executeCommand()
    {
        if (null === $this->rsyncPath) {
            throw new BuildException('The "rsyncPath" attribute is missing or undefined.');
        }

        if (null === $this->sourceDir) {
            throw new BuildException('The "sourcedir" attribute is missing or undefined.');
        }

        if (null === $this->destinationDir) {
            throw new BuildException('The "destinationdir" attribute is missing or undefined.');
        }

        if (strpos($this->destinationDir, ':')) {
            $this->setIsRemoteConnection(true);
        }

        if (strpos($this->sourceDir, ':')) {
            if ($this->isRemoteConnection) {
                throw new BuildException('The source and destination cannot both be remote.');
            }
            $this->setIsRemoteConnection(true);
        } else {
            if (!(is_dir($this->sourceDir) && is_readable($this->sourceDir))) {
                throw new BuildException('No such file or directory: ' . $this->sourceDir);
            }
        }

        if (null !== $this->backupDir && $this->backupDir == $this->destinationDir) {
            throw new BuildException('Invalid backup directory: ' . $this->backupDir);
        }

        $command = $this->getCommand();

        $output = [];
        $return = null;
        exec($command, $output, $return);

        $lines = '';
        foreach ($output as $line) {
            if (!empty($line)) {
                $lines .= "\r\n\t\t\t" . $line;
            }
        }

        $this->log($command);

        if (0 != $return) {
            $this->log('Task exited with code: ' . $return, Project::MSG_ERR);
            $this->log(
                'Task exited with message: (' . $return . ') ' . $this->getErrorMessage($return),
                Project::MSG_ERR
            );

            throw new BuildException($return . ': ' . $this->getErrorMessage($return));
        }

        $this->log($lines, Project::MSG_INFO);

        return $return;
    }

    /**
     * Returns the rsync command line options.
     *
     * @return string
     */
    public function getCommand()
    {
        $options = $this->defaultOptions;

        if (null !== $this->options) {
            $options = $this->options;
        }

        if (true === $this->verbose) {
            $options .= ' --verbose';
        }

        if (true === $this->checksum) {
            $options .= ' --checksum';
        }

        if (null !== $this->identityFile) {
            $options .= ' -e "ssh -i ' . $this->identityFile . ' -p' . $this->remotePort . '"';
        } else {
            if (null !== $this->remoteShell) {
                $options .= ' -e "' . $this->remoteShell . '"';
            }
        }

        if (true === $this->dryRun) {
            $options .= ' --dry-run';
        }

        if (true === $this->delete) {
            $options .= ' --delete-after --ignore-errors --force';
        }

        if (true === $this->itemizeChanges) {
            $options .= ' --itemize-changes';
        }
        if (null !== $this->backupDir) {
            $options .= ' -b --backup-dir="' . $this->backupDir . '"';
        }

        if (null !== $this->exclude) {
            //remove trailing comma if any
            $this->exclude = trim($this->exclude, ',');
            $options .= ' --exclude="' . str_replace(',', '" --exclude="', $this->exclude) . '"';
        }

        if (null !== $this->excludeFile) {
            $options .= ' --exclude-from="' . $this->excludeFile . '"';
        }

        $this->setOptions($options);

        $options .= ' "' . $this->sourceDir . '" "' . $this->destinationDir . '"';

        escapeshellcmd($options);
        $options .= ' 2>&1';

        return $this->rsyncPath . ' ' . $options;
    }

    /**
     * Returns an error message based on a given error code.
     *
     * @param int $code Error code
     *
     * @return null|string
     */
    public function getErrorMessage($code)
    {
        $error[0] = 'Success';
        $error[1] = 'Syntax or usage error';
        $error[2] = 'Protocol incompatibility';
        $error[3] = 'Errors selecting input/output files, dirs';
        $error[4] = 'Requested action not supported: an attempt was made to manipulate '
            . '64-bit files on a platform that cannot support them; or an option '
            . 'was specified that is supported by the client and not by the server';
        $error[5] = 'Error starting client-server protocol';
        $error[10] = 'Error in socket I/O';
        $error[11] = 'Error in file I/O';
        $error[12] = 'Error in rsync protocol data stream';
        $error[13] = 'Errors with program diagnostics';
        $error[14] = 'Error in IPC code';
        $error[20] = 'Received SIGUSR1 or SIGINT';
        $error[21] = 'Some error returned by waitpid()';
        $error[22] = 'Error allocating core memory buffers';
        $error[23] = 'Partial transfer due to error';
        $error[24] = 'Partial transfer due to vanished source files';
        $error[30] = 'Timeout in data send/receive';

        if (array_key_exists($code, $error)) {
            return $error[$code];
        }

        return null;
    }

    /**
     * Sets the path to the rsync command.
     *
     * @param string $path
     */
    public function setRsyncPath($path)
    {
        $this->rsyncPath = $path;
    }

    /**
     * Sets the source directory.
     *
     * @param string $dir
     */
    public function setSourceDir($dir)
    {
        $this->sourceDir = $dir;
    }

    /**
     * Sets the command options.
     *
     * @param string $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Sets the destination directory. If the option remotehost is not included
     * in the build.xml file, rsync will point to a local directory instead.
     *
     * @param string $dir
     */
    public function setDestinationDir($dir)
    {
        $this->destinationDir = $dir;
    }

    /**
     * Sets the remote host.
     *
     * @param string $host
     */
    public function setRemoteHost($host)
    {
        $this->remoteHost = $host;
    }

    /**
     * Specifies the user to log in as on the remote machine. This also may be
     * specified in the properties file.
     *
     * @param string $user
     */
    public function setRemoteUser($user)
    {
        $this->remoteUser = $user;
    }

    /**
     * This option allows you to provide a password for accessing a remote rsync
     * daemon. Note that this option is only useful when accessing an rsync daemon
     * using the built in transport, not when using a remote shell as the transport.
     *
     * @param string $pass
     */
    public function setRemotePass($pass)
    {
        $this->remotePass = $pass;
    }

    /**
     * Allows the user to choose an alternative remote shell program to use for
     * communication between the local and remote copies of rsync. Typically,
     * rsync is configured to use ssh by default, but you may prefer to use rsh
     * on a local network.
     *
     * @param string $shell
     */
    public function setRemoteShell($shell)
    {
        $this->remoteShell = $shell;
    }

    /**
     * Increases the amount of information you are given during the
     * transfer. By default, rsync works silently. A single -v will give you
     * information about what files are being transferred and a brief summary at
     * the end.
     */
    public function setVerbose(bool $verbose)
    {
        $this->verbose = $verbose;
    }

    /**
     * This changes the way rsync checks if the files have been changed and are in need of a transfer.
     * Without this option, rsync  uses  a "quick  check"  that  (by  default)  checks if each file’s
     * size and time of last modification match between the sender and receiver.
     * This option changes this to compare a 128-bit checksum for each file that has a matching size.
     */
    public function setChecksum(bool $checksum)
    {
        $this->checksum = $checksum;
    }

    /**
     * This makes rsync perform a trial run that doesn’t make any changes (and produces mostly the same
     * output as a real run).  It is  most commonly used in combination with the -v, --verbose and/or
     * -i, --itemize-changes options to see what an rsync command is going to do before one actually runs it.
     */
    public function setDryRun(bool $dryRun)
    {
        $this->dryRun = $dryRun;
    }

    /**
     * Requests a simple itemized list of the changes that are being made to each file, including attribute changes.
     */
    public function setItemizeChanges(bool $itemizeChanges)
    {
        $this->itemizeChanges = $itemizeChanges;
    }

    /**
     * Tells rsync to delete extraneous files from the receiving side, but only
     * for the directories that are being synchronized. Files that are excluded
     * from transfer are also excluded from being deleted.
     */
    public function setDelete(bool $delete)
    {
        $this->delete = $delete;
    }

    /**
     * Exclude files matching patterns from $file, Blank lines in $file and
     * lines starting with ';' or '#' are ignored.
     *
     * @param string $file
     */
    public function setExcludeFile($file)
    {
        $this->excludeFile = $file;
    }

    /**
     * Makes backups into hierarchy based in $dir.
     *
     * @param string dir
     * @param mixed $dir
     */
    public function setBackupDir($dir)
    {
        $this->backupDir = $dir;
    }

    /**
     * Sets the identity file for public key transfers.
     *
     * @param string location of ssh identity file
     * @param mixed $identity
     */
    public function setIdentityFile($identity)
    {
        $this->identityFile = $identity;
    }

    /**
     * Sets the port of the remote computer.
     *
     * @param int $remotePort
     */
    public function setRemotePort($remotePort)
    {
        $this->remotePort = $remotePort;
    }

    /**
     * Sets exclude matching pattern.
     *
     * @param string $exclude
     */
    public function setExclude($exclude)
    {
        $this->exclude = $exclude;
    }

    /**
     * Sets the isRemoteConnection property.
     *
     * @param bool $isRemote
     */
    protected function setIsRemoteConnection($isRemote)
    {
        $this->isRemoteConnection = $isRemote;
    }
}
