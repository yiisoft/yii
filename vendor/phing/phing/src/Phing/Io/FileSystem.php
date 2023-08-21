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

namespace Phing\Io;

use Exception;
use FilesystemIterator;
use Phing\Phing;

/**
 * This is an abstract class for platform specific filesystem implementations
 * you have to implement each method in the platform specific filesystem implementation
 * classes Your local filesytem implementation must extend this class.
 * You should also use this class as a template to write your local implementation
 * Some native PHP filesystem specific methods are abstracted here as well. Anyway
 * you _must_ always use this methods via a PhingFile object (that by nature uses the
 * *FileSystem drivers to access the real filesystem via this class using natives.
 *
 * FIXME:
 *  - Error handling reduced to min fallthrough runtime exceptions
 *    more precise errorhandling is done by the PhingFile class
 *
 * @author Charlie Killian <charlie@tizac.com>
 * @author Hans Lellelid <hans@xmpl.org>
 */
abstract class FileSystem
{
    /**
     * @var int
     */
    public const BA_EXISTS = 0x01;

    /**
     * @var int
     */
    public const BA_REGULAR = 0x02;

    /**
     * @var int
     */
    public const BA_DIRECTORY = 0x04;

    /**
     * @var int
     */
    public const BA_HIDDEN = 0x08;

    /**
     * Instance for getFileSystem() method.
     *
     * @var FileSystem
     */
    private static $fs;

    /**
     * @var File[]
     */
    private static $filesToDelete = [];

    /**
     * Static method to return the FileSystem singelton representing
     * this platform's local filesystem driver.
     *
     * @throws IOException
     *
     * @return FileSystem
     */
    public static function getFileSystem()
    {
        if (null === self::$fs) {
            switch (Phing::getProperty('host.fstype')) {
                case 'UNIX':
                    self::$fs = new UnixFileSystem();

                    break;

                case 'WINDOWS':
                    self::$fs = new WindowsFileSystem();

                    break;

                default:
                    throw new IOException('Host uses unsupported filesystem, unable to proceed');
            }
        }

        return self::$fs;
    }

    // -- Normalization and construction --

    /**
     * Return the local filesystem's name-separator character.
     */
    abstract public function getSeparator();

    /**
     * Return the local filesystem's path-separator character.
     */
    abstract public function getPathSeparator();

    /**
     * Convert the given pathname string to normal form.  If the string is
     * already in normal form then it is simply returned.
     *
     * @param string $strPath
     */
    abstract public function normalize($strPath);

    /**
     * Compute the length of this pathname string's prefix.  The pathname
     * string must be in normal form.
     *
     * @param string $pathname
     */
    abstract public function prefixLength($pathname);

    /**
     * Resolve the child pathname string against the parent.
     * Both strings must be in normal form, and the result
     * will be a string in normal form.
     *
     * @param string $parent
     * @param string $child
     */
    abstract public function resolve($parent, $child);

    /**
     * Resolve the given abstract pathname into absolute form.  Invoked by the
     * getAbsolutePath and getCanonicalPath methods in the PhingFile class.
     */
    abstract public function resolveFile(File $f);

    /**
     * Return the parent pathname string to be used when the parent-directory
     * argument in one of the two-argument PhingFile constructors is the empty
     * pathname.
     */
    abstract public function getDefaultParent();

    /**
     * Post-process the given URI path string if necessary.  This is used on
     * win32, e.g., to transform "/c:/foo" into "c:/foo".  The path string
     * still has slash separators; code in the PhingFile class will translate them
     * after this method returns.
     *
     * @param string $path
     */
    abstract public function fromURIPath($path);

    // -- Path operations --

    /**
     * Tell whether or not the given abstract pathname is absolute.
     */
    abstract public function isAbsolute(File $f);

    /**
     * canonicalize filename by checking on disk.
     *
     * @param string $strPath
     *
     * @return mixed canonical path or false if the file doesn't exist
     */
    public function canonicalize($strPath)
    {
        return @realpath($strPath);
    }

    // -- Attribute accessors --

    /**
     * Check whether the file or directory denoted by the given abstract
     * pathname may be accessed by this process.  If the second argument is
     * false, then a check for read access is made; if the second
     * argument is true, then a check for write (not read-write)
     * access is made.  Return false if access is denied or an I/O error
     * occurs.
     *
     * @param bool $write
     *
     * @return bool
     */
    public function checkAccess(File $f, $write = false)
    {
        // we clear stat cache, its expensive to look up from scratch,
        // but we need to be sure
        @clearstatcache();

        // Shouldn't this be $f->GetAbsolutePath() ?
        // And why doesn't GetAbsolutePath() work?

        $strPath = (string) $f->getPath();

        // FIXME
        // if file object does denote a file that yet not existst
        // path rights are checked
        if (!@file_exists($strPath) && !is_dir($strPath)) {
            $strPath = $f->getParent();
            if (null === $strPath || !is_dir($strPath)) {
                $strPath = Phing::getProperty('user.dir');
            }
            //$strPath = dirname($strPath);
        }

        if (!$write) {
            return (bool) @is_readable($strPath);
        }

        return (bool) @is_writable($strPath);
    }

    /**
     * Whether file can be deleted.
     *
     * @return bool
     */
    public function canDelete(File $f)
    {
        clearstatcache();
        $dir = dirname($f->getAbsolutePath());

        return @is_writable($dir);
    }

    /**
     * Return the time at which the file or directory denoted by the given
     * abstract pathname was last modified, or zero if it does not exist or
     * some other I/O error occurs.
     *
     * @throws IOException
     *
     * @return int
     */
    public function getLastModifiedTime(File $f)
    {
        if (!$f->exists()) {
            return 0;
        }

        @clearstatcache();
        error_clear_last();
        $strPath = (string) $f->getPath();

        if (@is_link($strPath)) {
            $stats = @lstat($strPath);

            if (!isset($stats['mtime'])) {
                $mtime = false;
            } else {
                $mtime = $stats['mtime'];
            }
        } else {
            $mtime = @filemtime($strPath);
        }

        if (false === $mtime) {
            $lastError = error_get_last();
            $errormsg = $lastError['message'] ?? 'unknown error';
            $msg = "FileSystem::getLastModifiedTime() FAILED. Can not get modified time of {$strPath}. {$errormsg}";

            throw new IOException($msg);
        }

        return (int) $mtime;
    }

    /**
     * Return the length in bytes of the file denoted by the given abstract
     * pathname, or zero if it does not exist, is a directory, or some other
     * I/O error occurs.
     *
     * @throws IOException
     *
     * @return int
     */
    public function getLength(File $f)
    {
        error_clear_last();
        $strPath = (string) $f->getAbsolutePath();
        $fs = filesize((string) $strPath);
        if (false !== $fs) {
            return $fs;
        }

        $lastError = error_get_last();
        $errormsg = $lastError['message'] ?? 'unknown error';
        $msg = "FileSystem::Read() FAILED. Cannot get filesize of {$strPath}. {$errormsg}";

        throw new IOException($msg);
    }

    // -- File operations --

    /**
     * Create a new empty file with the given pathname.  Return
     * true if the file was created and false if a
     * file or directory with the given pathname already exists.  Throw an
     * IOException if an I/O error occurs.
     *
     * @param string $strPathname path of the file to be created
     *
     * @throws IOException
     *
     * @return bool
     */
    public function createNewFile($strPathname)
    {
        if (@file_exists($strPathname)) {
            return false;
        }

        // Create new file
        $fp = @fopen($strPathname, 'w');
        if (false === $fp) {
            $error = error_get_last();

            throw new IOException(
                "The file \"{$strPathname}\" could not be created: " . $error['message'] ?? 'unknown error'
            );
        }
        @fclose($fp);

        return true;
    }

    /**
     * Delete the file or directory denoted by the given abstract pathname,
     * returning true if and only if the operation succeeds.
     *
     * @param bool $recursive
     *
     * @throws IOException
     */
    public function delete(File $f, $recursive = false)
    {
        if ($f->isDirectory()) {
            $this->rmdir($f->getPath(), $recursive);
        } else {
            $this->unlink($f->getPath());
        }
    }

    /**
     * Arrange for the file or directory denoted by the given abstract
     * pathname to be deleted when Phing::shutdown is called, returning
     * true if and only if the operation succeeds.
     *
     * @throws IOException
     */
    public function deleteOnExit(File $f)
    {
        self::$filesToDelete[] = $f;
    }

    public static function deleteFilesOnExit()
    {
        foreach (self::$filesToDelete as $file) {
            $file->delete();
        }
    }

    /**
     * Create a new directory denoted by the given abstract pathname,
     * returning true if and only if the operation succeeds.
     *
     * The behaviour is the same as of "mkdir" command in Linux.
     * Without $mode argument, the directory is created with permissions
     * corresponding to the umask setting.
     * If $mode argument is specified, umask setting is ignored and
     * the permissions are set according to the $mode argument using chmod().
     *
     * @param File     $f
     * @param null|int $mode
     *
     * @return bool
     */
    public function createDirectory(&$f, $mode = null)
    {
        if (null === $mode) {
            try {
                $return = @mkdir($f->getAbsolutePath());
            } catch (\Throwable $t) {
                $return = false;
            }
        } else {
            // If the $mode is specified, mkdir() is called with the $mode
            // argument so that the new directory does not temporarily have
            // higher permissions than it should before chmod() is called.
            try {
                $return = @mkdir($f->getAbsolutePath(), $mode);
            } catch (\Throwable $t) {
                $return = false;
            }
            if ($return) {
                chmod($f->getAbsolutePath(), $mode);
            }
        }

        return $return;
    }

    /**
     * Rename the file or directory denoted by the first abstract pathname to
     * the second abstract pathname, returning true if and only if
     * the operation succeeds.
     *
     * @param File $f1 abstract source file
     * @param File $f2 abstract destination file
     *
     * @throws IOException if rename cannot be performed
     */
    public function rename(File $f1, File $f2)
    {
        error_clear_last();
        // get the canonical paths of the file to rename
        $src = $f1->getAbsolutePath();
        $dest = $f2->getAbsolutePath();
        if (false === @rename($src, $dest)) {
            $lastError = error_get_last();
            $errormsg = $lastError['message'] ?? 'unknown error';
            $msg = "Rename FAILED. Cannot rename {$src} to {$dest}. {$errormsg}";

            throw new IOException($msg);
        }
    }

    /**
     * Set the last-modified time of the file or directory denoted by the
     * given abstract pathname returning true if and only if the
     * operation succeeds.
     *
     * @param int $time
     *
     * @throws IOException
     */
    public function setLastModifiedTime(File $f, $time)
    {
        error_clear_last();
        $path = $f->getPath();
        $success = @touch($path, $time);
        if (!$success) {
            $lastError = error_get_last();
            $errormsg = $lastError['message'] ?? 'unknown error';

            throw new IOException("Could not touch '" . $path . "' due to: {$errormsg}");
        }
    }

    // -- Basic infrastructure --

    /**
     * Compare two abstract pathnames lexicographically.
     *
     * @throws IOException
     *
     * @return int
     */
    public function compare(File $f1, File $f2)
    {
        throw new IOException('compare() not implemented by local fs driver');
    }

    /**
     * Copy a file.
     *
     * @param File $src  source path and name file to copy
     * @param File $dest destination path and name of new file
     *
     * @throws IOException if file cannot be copied
     */
    public function copy(File $src, File $dest)
    {
        // Recursively copy a directory
        if ($src->isDirectory()) {
            $this->copyr($src->getAbsolutePath(), $dest->getAbsolutePath());
        }

        $srcPath = $src->getAbsolutePath();
        $destPath = $dest->getAbsolutePath();

        error_clear_last();
        if (false === @copy($srcPath, $destPath)) { // Copy FAILED. Log and return err.
            // Add error from php to end of log message. $errormsg.
            $lastError = error_get_last();
            $errormsg = $lastError['message'] ?? 'unknown error';
            $msg = "FileSystem::copy() FAILED. Cannot copy {$srcPath} to {$destPath}. {$errormsg}";

            throw new IOException($msg);
        }

        $dest->setMode($src->getMode());
    }

    /**
     * Copy a file, or recursively copy a folder and its contents.
     *
     * @param string $source Source path
     * @param string $dest   Destination path
     *
     * @return bool Returns TRUE on success, FALSE on failure
     *
     * @author  Aidan Lister <aidan@php.net>
     *
     * @version 1.0.1
     *
     * @see    http://aidanlister.com/repos/v/function.copyr.php
     */
    public function copyr($source, $dest)
    {
        // Check for symlinks
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }

        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (!is_dir($dest) && !mkdir($dest) && !is_dir($dest)) {
            return false;
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ('.' == $entry || '..' == $entry) {
                continue;
            }

            // Deep copy directories
            $this->copyr("{$source}/{$entry}", "{$dest}/{$entry}");
        }

        // Clean up
        $dir->close();

        return true;
    }

    /**
     * Change the ownership on a file or directory.
     *
     * @param string $pathname path and name of file or directory
     * @param string $user     The user name or number of the file or directory. See http://us.php.net/chown
     *
     * @throws IOException if operation failed
     */
    public function chown($pathname, $user)
    {
        error_clear_last();
        if (false === @chown($pathname, $user)) { // FAILED.
            $lastError = error_get_last();
            $errormsg = $lastError['message'] ?? 'unknown error';

            throw new IOException("FileSystem::chown() FAILED. Cannot chown {$pathname}. User {$user} {$errormsg}");
        }
    }

    /**
     * Change the group on a file or directory.
     *
     * @param string $pathname path and name of file or directory
     * @param string $group    The group of the file or directory. See http://us.php.net/chgrp
     *
     * @throws IOException if operation failed
     */
    public function chgrp($pathname, $group)
    {
        error_clear_last();
        if (false === @chgrp($pathname, $group)) { // FAILED.
            $lastError = error_get_last();
            $errormsg = $lastError['message'] ?? 'unknown error';

            throw new IOException("FileSystem::chgrp() FAILED. Cannot chown {$pathname}. Group {$group} {$errormsg}");
        }
    }

    /**
     * Change the permissions on a file or directory.
     *
     * @param string $pathname path and name of file or directory
     * @param int    $mode     The mode (permissions) of the file or
     *                         directory. If using octal add leading
     *                         0. eg. 0777. Mode is affected by the
     *                         umask system setting.
     *
     * @throws IOException if operation failed
     */
    public function chmod($pathname, $mode)
    {
        error_clear_last();
        $str_mode = decoct($mode); // Show octal in messages.
        if (false === @chmod($pathname, $mode)) { // FAILED.
            $lastError = error_get_last();
            $errormsg = $lastError['message'] ?? 'unknown error';

            throw new IOException("FileSystem::chmod() FAILED. Cannot chmod {$pathname}. Mode {$str_mode} {$errormsg}");
        }
    }

    /**
     * Locks a file and throws an Exception if this is not possible.
     *
     * @throws IOException
     */
    public function lock(File $f)
    {
        $filename = $f->getPath();
        $fp = @fopen($filename, 'w');
        $result = @flock($fp, LOCK_EX);
        @fclose($fp);
        if (!$result) {
            throw new IOException("Could not lock file '{$filename}'");
        }
    }

    /**
     * Unlocks a file and throws an IO Error if this is not possible.
     *
     * @throws IOException
     */
    public function unlock(File $f)
    {
        $filename = $f->getPath();
        $fp = @fopen($filename, 'w');
        $result = @flock($fp, LOCK_UN);
        fclose($fp);
        if (!$result) {
            throw new IOException("Could not unlock file '{$filename}'");
        }
    }

    /**
     * Delete a file.
     *
     * @param string $file path and/or name of file to delete
     *
     * @throws IOException - if an error is encountered
     */
    public function unlink($file)
    {
        error_clear_last();
        if (false === @unlink($file)) {
            $lastError = error_get_last();
            $errormsg = $lastError['message'] ?? 'unknown error';
            $msg = "FileSystem::unlink() FAILED. Cannot unlink '{$file}'. {$errormsg}";

            throw new IOException($msg);
        }
    }

    /**
     * Symbolically link a file to another name.
     *
     * Currently symlink is not implemented on Windows. Don't use if the application is to be portable.
     *
     * @param string $target path and/or name of file to link
     * @param string $link   path and/or name of link to be created
     *
     * @throws IOException
     */
    public function symlink($target, $link)
    {
        error_clear_last();
        // If Windows OS then symlink() will report it is not supported in
        // the build. Use this error instead of checking for Windows as the OS.

        if (false === @symlink($target, $link)) {
            $lastError = error_get_last();
            $errormsg = $lastError['message'] ?? 'unknown error';
            // Add error from php to end of log message.
            $msg = "FileSystem::Symlink() FAILED. Cannot symlink '{$target}' to '{$link}'. {$errormsg}";

            throw new IOException($msg);
        }
    }

    /**
     * Set the modification and access time on a file to the present time.
     *
     * @param string $file path and/or name of file to touch
     * @param int    $time
     *
     * @throws Exception
     */
    public function touch($file, $time = null)
    {
        error_clear_last();
        if (null === $time) {
            $error = @touch($file);
        } else {
            $error = @touch($file, $time);
        }

        if (false === $error) { // FAILED.
            $lastError = error_get_last();
            $errormsg = $lastError['message'] ?? 'unknown error';
            // Add error from php to end of log message.
            $msg = "FileSystem::touch() FAILED. Cannot touch '{$file}'. {$errormsg}";

            throw new Exception($msg);
        }
    }

    /**
     * Delete an empty directory OR a directory and all of its contents.
     *
     * @param string $dir      path and/or name of directory to delete
     * @param bool   $children False: don't delete directory contents.
     *                         True: delete directory contents.
     *
     * @throws Exception
     */
    public function rmdir($dir, $children = false)
    {
        error_clear_last();

        // If children=FALSE only delete dir if empty.
        if (false === $children) {
            if (false === @rmdir($dir)) { // FAILED.
                $lastError = error_get_last();
                $errormsg = $lastError['message'] ?? 'unknown error';
                // Add error from php to end of log message.
                $msg = "FileSystem::rmdir() FAILED. Cannot rmdir {$dir}. {$errormsg}";

                throw new Exception($msg);
            }
        } else { // delete contents and dir.
            $handle = @opendir($dir);
            $lastError = error_get_last();
            $errormsg = $lastError['message'] ?? 'unknown error';

            if (false === $handle) { // Error.
                $msg = "FileSystem::rmdir() FAILED. Cannot opendir() {$dir}. {$errormsg}";

                throw new Exception($msg);
            }
            // Read from handle.
            // Don't error on readdir().
            while (false !== ($entry = @readdir($handle))) {
                if ('.' != $entry && '..' != $entry) {
                    // Only add / if it isn't already the last char.
                    // This ONLY serves the purpose of making the Logger
                    // output look nice:)

                    if (0 === strpos(strrev($dir), DIRECTORY_SEPARATOR)) { // there is a /
                        $next_entry = $dir . $entry;
                    } else { // no /
                        $next_entry = $dir . DIRECTORY_SEPARATOR . $entry;
                    }

                    // NOTE: As of php 4.1.1 is_dir doesn't return FALSE it
                    // returns 0. So use == not ===.

                    // Don't error on is_dir()
                    if (false == @is_dir($next_entry)) { // Is file.
                        try {
                            $this->unlink($next_entry); // Delete.
                        } catch (Exception $e) {
                            $msg = "FileSystem::Rmdir() FAILED. Cannot FileSystem::Unlink() {$next_entry}. " . $e->getMessage();

                            throw new Exception($msg);
                        }
                    } else { // Is directory.
                        try {
                            $this->rmdir($next_entry, true); // Delete
                        } catch (Exception $e) {
                            $msg = "FileSystem::rmdir() FAILED. Cannot FileSystem::rmdir() {$next_entry}. " . $e->getMessage();

                            throw new Exception($msg);
                        }
                    }
                }
            }

            // Don't error on closedir()
            @closedir($handle);

            error_clear_last();
            if (false === @rmdir($dir)) { // FAILED.
                // Add error from php to end of log message.
                $lastError = error_get_last();
                $errormsg = $lastError['message'] ?? 'unknown error';
                $msg = "FileSystem::rmdir() FAILED. Cannot rmdir {$dir}. {$errormsg}";

                throw new Exception($msg);
            }
        }
    }

    /**
     * Set the umask for file and directory creation.
     *
     * @param int $mode
     *
     * @throws Exception
     *
     * @internal param Int $mode . Permissions usually in ocatal. Use leading 0 for
     *                    octal. Number between 0 and 0777.
     */
    public function umask($mode)
    {
        error_clear_last();
        // CONSIDERME:
        // Throw a warning if mode is 0. PHP converts illegal octal numbers to
        // 0 so 0 might not be what the user intended.

        $str_mode = decoct($mode); // Show octal in messages.

        if (false === @umask($mode)) { // FAILED.
            $lastError = error_get_last();
            $errormsg = $lastError['message'] ?? 'unknown error';
            // Add error from php to end of log message.
            $msg = "FileSystem::Umask() FAILED. Value {$str_mode}. {$errormsg}";

            throw new Exception($msg);
        }
    }

    /**
     * Compare the modified time of two files.
     *
     * @param string $file1 path and name of file1
     * @param string $file2 path and name of file2
     *
     * @throws exception - if cannot get modified time of either file
     *
     * @return int 1 if file1 is newer.
     *             -1 if file2 is newer.
     *             0 if files have the same time.
     *             Err object on failure.
     */
    public function compareMTimes($file1, $file2)
    {
        $mtime1 = filemtime($file1);
        $mtime2 = filemtime($file2);

        if (false === $mtime1) { // FAILED. Log and return err.
            // Add error from php to end of log message.
            $msg = "FileSystem::compareMTimes() FAILED. Cannot can not get modified time of {$file1}.";

            throw new Exception($msg);
        }

        if (false === $mtime2) { // FAILED. Log and return err.
            // Add error from php to end of log message.
            $msg = "FileSystem::compareMTimes() FAILED. Cannot can not get modified time of {$file2}.";

            throw new Exception($msg);
        }

        // Worked. Log and return compare.
        // Compare mtimes.
        if ($mtime1 == $mtime2) {
            return 0;
        }

        return ($mtime1 < $mtime2) ? -1 : 1; // end compare
    }

    /**
     * returns the contents of a directory in an array.
     *
     * @return string[]
     */
    public function listContents(File $f)
    {
        return array_map('strval', array_keys(
            iterator_to_array(
                new FilesystemIterator(
                    $f->getAbsolutePath(),
                    FilesystemIterator::KEY_AS_FILENAME | FilesystemIterator::SKIP_DOTS
                )
            )
        ));
    }

    /**
     * PHP implementation of the 'which' command.
     *
     * Used to retrieve/determine the full path for a command.
     *
     * @param string $executable Executable file to search for
     * @param mixed  $fallback   default to fallback to
     *
     * @return string full path for the specified executable/command
     */
    public function which($executable, $fallback = false)
    {
        if (is_string($executable)) {
            if ('' === trim($executable)) {
                return $fallback;
            }
        } else {
            return $fallback;
        }
        if (basename($executable) === $executable) {
            $path = getenv('PATH');
        } else {
            $path = dirname($executable);
        }
        $dirSeparator = $this->getSeparator();
        $pathSeparator = $this->getPathSeparator();
        $elements = explode($pathSeparator, $path);
        $amount = count($elements);
        $fstype = Phing::getProperty('host.fstype');

        switch ($fstype) {
            case 'UNIX':
                for ($count = 0; $count < $amount; ++$count) {
                    $file = $elements[$count] . $dirSeparator . $executable;
                    if (file_exists($file) && is_executable($file)) {
                        return $file;
                    }
                }

                break;

            case 'WINDOWS':
                $exts = getenv('PATHEXT');
                if (false === $exts) {
                    $exts = ['.exe', '.bat', '.cmd', '.com'];
                } else {
                    $exts = explode($pathSeparator, $exts);
                }
                for ($count = 0; $count < $amount; ++$count) {
                    foreach ($exts as $ext) {
                        $file = $elements[$count] . $dirSeparator . $executable . $ext;
                        // Not all of the extensions above need to be set executable on Windows for them to be executed.
                        // I'm sure there's a joke here somewhere.
                        if (file_exists($file)) {
                            return $file;
                        }
                    }
                }

                break;
        }
        if (file_exists($executable) && is_executable($executable)) {
            return $executable;
        }

        return $fallback;
    }
}
