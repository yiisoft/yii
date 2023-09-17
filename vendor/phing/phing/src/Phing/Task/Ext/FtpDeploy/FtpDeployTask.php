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

namespace Phing\Task\Ext\FtpDeploy;

use Phing\Exception\BuildException;
use Phing\ExceptionBuildException;
use Phing\Io\File;
use Phing\Io\FileSystem;
use Phing\Phing;
use Phing\Task;
use Phing\Task\System\Element\LogLevelAware;
use Phing\Type\Element\FileSetAware;
use Phing\Util\StringHelper;

/**
 * FtpDeployTask
 *
 * Deploys a set of files to a remote FTP server.
 *
 *
 * Example usage:
 * <ftpdeploy host="host" port="21" username="user" password="password" dir="public_html" mode="ascii" clearfirst="true" depends="false" filemode="" dirmode="">
 *   <fileset dir=".">
 *     <include name="**"/>
 *     <exclude name="phing"/>
 *     <exclude name="build.xml"/>
 *     <exclude name="images/**.png"/>
 *     <exclude name="images/**.gif"/>
 *     <exclude name="images/**.jpg"/>
 *   </fileset>
 * </ftpdeploy>
 *
 * @author      Jorrit Schippers <jorrit at ncode dot nl>
 * @contributor Steffen Sørensen <steffen@sublife.dk>
 * @since       2.3.1
 * @package     phing.tasks.ext
 */
class FtpDeployTask extends Task
{
    use FileSetAware;
    use LogLevelAware;

    private $host = null;
    private $port = 21;
    private $ssl = false;
    private $username;
    private $password;
    private $dir;
    private $mode = FTP_BINARY;
    private $clearFirst = false;
    private $passive = false;
    private $depends = false;
    private $dirmode = false;
    private $filemode = false;
    private $rawDataFallback = false;
    private $skipOnSameSize = false;

    public function __construct()
    {
        parent::__construct();
        $this->filesets = [];
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @param bool $ssl
     */
    public function setSsl(bool $ssl): void
    {
        $this->ssl = $ssl;
    }

    /**
     * @param string $username
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * @param $dir
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    /**
     * @param $mode
     */
    public function setMode($mode)
    {
        switch (strtolower($mode)) {
            case 'ascii':
                $this->mode = FTP_ASCII;
                break;
            case 'binary':
            case 'bin':
                $this->mode = FTP_BINARY;
                break;
        }
    }

    /**
     * @param bool $passive
     */
    public function setPassive(bool $passive): void
    {
        $this->passive = $passive;
    }

    /**
     * @param bool $clearFirst
     */
    public function setClearFirst(bool $clearFirst): void
    {
        $this->clearFirst = $clearFirst;
    }

    /**
     * @param bool $depends
     */
    public function setDepends(bool $depends): void
    {
        $this->depends = $depends;
    }

    /**
     * @param string $filemode
     */
    public function setFilemode($filemode): void
    {
        $this->filemode = octdec(str_pad($filemode, 4, '0', STR_PAD_LEFT));
    }

    /**
     * @param $dirmode
     */
    public function setDirmode($dirmode): void
    {
        $this->dirmode = octdec(str_pad($dirmode, 4, '0', STR_PAD_LEFT));
    }

    /**
     * @param $fallback
     */
    public function setRawdatafallback(bool $fallback): void
    {
        $this->rawDataFallback = $fallback;
    }

    /**
     * @param bool|string|int $skipOnSameSize
     */
    public function setSkipOnSameSize($skipOnSameSize): void
    {
        $this->skipOnSameSize = StringHelper::booleanValue($skipOnSameSize);
    }

    /**
     * The init method: check if Net_FTP is available
     */
    public function init()
    {
        $paths = Phing::explodeIncludePath();
        foreach ($paths as $path) {
            if (file_exists($path . DIRECTORY_SEPARATOR . 'Net' . DIRECTORY_SEPARATOR . 'FTP.php')) {
                return true;
            }
        }
        throw new BuildException('The FTP Deploy task requires the Net_FTP PEAR package.');
    }

    /**
     * The main entry point method.
     */
    public function main()
    {
        $project = $this->getProject();

        $ftp = new \Net_FTP($this->host, $this->port);
        if ($this->ssl) {
            $ret = $ftp->setSsl();
            if (@\PEAR::isError($ret)) {
                throw new BuildException(
                    'SSL connection not supported by php: ' . $ret->getMessage()
                );
            }

            $this->log('Use SSL connection', $this->logLevel);
        }
        $ret = $ftp->connect();
        if (@\PEAR::isError($ret)) {
            throw new BuildException(
                'Could not connect to FTP server ' . $this->host . ' on port ' . $this->port . ': ' . $ret->getMessage()
            );
        }

        $this->log('Connected to FTP server ' . $this->host . ' on port ' . $this->port, $this->logLevel);

        $ret = $ftp->login($this->username, $this->password);
        if (@\PEAR::isError($ret)) {
            throw new BuildException(
                'Could not login to FTP server ' . $this->host . ' on port ' . $this->port . ' with username ' . $this->username . ': ' . $ret->getMessage()
            );
        }

        $this->log('Logged in to FTP server with username ' . $this->username, $this->logLevel);

        if ($this->passive) {
            $this->log('Setting passive mode', $this->logLevel);
            $ret = $ftp->setPassive();
            if (@\PEAR::isError($ret)) {
                $ftp->disconnect();
                throw new BuildException('Could not set PASSIVE mode: ' . $ret->getMessage());
            }
        }

        // append '/' to the end if necessary
        $dir = substr($this->dir, -1) === '/' ? $this->dir : $this->dir . '/';

        if ($this->clearFirst) {
            // TODO change to a loop through all files and directories within current directory
            $this->log('Clearing directory ' . $dir, $this->logLevel);
            $ftp->rm($dir, true);
        }

        // Create directory just in case
        $ret = $ftp->mkdir($dir, true);
        if (@\PEAR::isError($ret)) {
            $ftp->disconnect();
            throw new BuildException('Could not create directory ' . $dir . ': ' . $ret->getMessage());
        }

        $ret = $ftp->cd($dir);
        if (@\PEAR::isError($ret)) {
            $ftp->disconnect();
            throw new BuildException('Could not change to directory ' . $dir . ': ' . $ret->getMessage());
        }

        $this->log('Changed directory ' . $dir, $this->logLevel);

        $fs = FileSystem::getFileSystem();
        $convert = $fs->getSeparator() === '\\';

        foreach ($this->filesets as $fs) {
            // Array for holding directory content informations
            $remoteFileInformations = [];

            $ds = $fs->getDirectoryScanner($project);
            $fromDir = $fs->getDir($project);
            $srcFiles = $ds->getIncludedFiles();
            $srcDirs = $ds->getIncludedDirectories();

            foreach ($srcDirs as $dirname) {
                if ($convert) {
                    $dirname = str_replace('\\', '/', $dirname);
                }

                // Read directory informations, if file exists, else create the directory
                if (!$this->directoryInformations($ftp, $remoteFileInformations, $dirname)) {
                    $this->log('Will create directory ' . $dirname, $this->logLevel);
                    $ret = $ftp->mkdir($dirname, true);
                    if (@\PEAR::isError($ret)) {
                        $ftp->disconnect();
                        throw new BuildException('Could not create directory ' . $dirname . ': ' . $ret->getMessage());
                    }
                }
                if ($this->dirmode) {
                    if ($this->dirmode === 'inherit') {
                        $mode = fileperms($dirname);
                    } else {
                        $mode = $this->dirmode;
                    }
                    // Because Net_FTP does not support a chmod call we call ftp_chmod directly
                    ftp_chmod($ftp->_handle, $mode, $dirname);
                }
            }

            foreach ($srcFiles as $filename) {
                $file = new File($fromDir->getAbsolutePath(), $filename);
                if ($convert) {
                    $filename = str_replace('\\', '/', $filename);
                }

                $local_filemtime = filemtime($file->getCanonicalPath());
                $remoteFileModificationTime = $remoteFileInformations[$filename]['stamp'] ?? 0;

                if (!$this->depends || ($local_filemtime > $remoteFileModificationTime)) {
                    if ($this->skipOnSameSize === true && $file->length() === $ftp->size($filename)) {
                        $this->log('Skipped ' . $file->getCanonicalPath(), $this->logLevel);
                        continue;
                    }

                    $this->log('Will copy ' . $file->getCanonicalPath() . ' to ' . $filename, $this->logLevel);
                    $ret = $ftp->put($file->getCanonicalPath(), $filename, true, $this->mode);
                    if (@\PEAR::isError($ret)) {
                        $ftp->disconnect();
                        throw new BuildException('Could not deploy file ' . $filename . ': ' . $ret->getMessage());
                    }
                }
                if ($this->filemode) {
                    if ($this->filemode === 'inherit') {
                        $mode = fileperms($filename);
                    } else {
                        $mode = $this->filemode;
                    }
                    // Because Net_FTP does not support a chmod call we call ftp_chmod directly
                    ftp_chmod($ftp->_handle, $mode, $filename);
                }
            }
        }

        $ftp->disconnect();
        $this->log('Disconnected from FTP server', $this->logLevel);
    }

    /**
     * @param \Net_FTP $ftp
     * @param $remoteFileInformations
     * @param $directory
     * @return bool
     */
    private function directoryInformations(\Net_FTP $ftp, &$remoteFileInformations, $directory)
    {
        $content = $ftp->ls($directory);
        if (@\PEAR::isError($content)) {
            if ($this->rawDataFallback) {
                $content = $ftp->ls($directory, NET_FTP_RAWLIST);
            }
            if (@\PEAR::isError($content)) {
                return false;
            }
            $content = $this->parseRawFtpContent($content, $directory);
        }

        if (count($content) == 0) {
            return false;
        }

        if (!empty($directory)) {
            $directory .= '/';
        }
        foreach ($content as $val) {
            if ($val['name'] !== '.' && $val['name'] !== '..') {
                $remoteFileInformations[$directory . $val['name']] = $val;
            }
        }

        return true;
    }

    /**
     * @param $content
     * @param null $directory
     * @return array
     */
    private function parseRawFtpContent($content, $directory = null): array
    {
        if (!is_array($content)) {
            return [];
        }

        $this->log('Start parsing FTP_RAW_DATA in ' . $directory);
        $retval = [];
        foreach ($content as $rawInfo) {
            $rawInfo = explode(' ', $rawInfo);
            $rawInfo2 = [];
            foreach ($rawInfo as $part) {
                $part = trim($part);
                if (!empty($part)) {
                    $rawInfo2[] = $part;
                }
            }

            $date = date_parse($rawInfo2[5] . ' ' . $rawInfo2[6] . ' ' . $rawInfo2[7]);
            if ($date['year'] === false) {
                $date['year'] = date('Y');
            }
            $date = mktime(
                $date['hour'],
                $date['minute'],
                $date['second'],
                $date['month'],
                $date['day'],
                $date['year']
            );

            $retval[] = [
                'name' => $rawInfo2[8],
                'rights' => substr($rawInfo2[0], 1),
                'user' => $rawInfo2[2],
                'group' => $rawInfo2[3],
                'date' => $date,
                'stamp' => $date,
                'is_dir' => strpos($rawInfo2[0], 'd') === 0,
                'files_inside' => (int) $rawInfo2[1],
                'size' => (int) $rawInfo2[4],
            ];
        }

        $this->log('Finished parsing FTP_RAW_DATA in ' . $directory);

        return $retval;
    }
}
