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
use Phing\Util\StringHelper;

/**
 * An abstract representation of file and directory pathnames.
 */
class File
{
    /**
     * This abstract pathname's normalized pathname string.  A normalized
     * pathname string uses the default name-separator character and does not
     * contain any duplicate or redundant separators.
     */
    private $path = '';

    /**
     * The length of this abstract pathname's prefix, or zero if it has no prefix.
     *
     * @var int
     */
    private $prefixLength = 0;

    /**
     * constructor.
     *
     * @param null|mixed $arg1
     * @param null|mixed $arg2
     *
     * @throws IOException
     * @throws \InvalidArgumentException
     */
    public function __construct($arg1 = null, $arg2 = null)
    {
        // simulate signature identified constructors
        if ($arg1 instanceof File && is_string($arg2)) {
            $this->constructFileParentStringChild($arg1, $arg2);
        } elseif (is_string($arg1) && (null === $arg2)) {
            $this->constructPathname($arg1);
        } elseif (is_string($arg1) && is_string($arg2)) {
            $this->constructStringParentStringChild($arg1, $arg2);
        } else {
            if (null === $arg1) {
                throw new \InvalidArgumentException('Argument1 to function must not be null');
            }
            $this->path = (string) $arg1;
            $this->prefixLength = (int) $arg2;
        }
    }

    /**
     * Return string representation of the object.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPath();
    }

    /**
     * Returns the length of this abstract pathname's prefix.
     *
     * @return int
     */
    public function getPrefixLength()
    {
        return (int) $this->prefixLength;
    }

    // -- Path-component accessors --

    /**
     * Returns the name of the file or directory denoted by this abstract
     * pathname.  This is just the last name in the pathname's name
     * sequence.  If the pathname's name sequence is empty, then the empty
     * string is returned.
     *
     * @return string The name of the file or directory denoted by this abstract
     *                pathname, or the empty string if this pathname's name sequence
     *                is empty
     */
    public function getName()
    {
        // that's a lastIndexOf
        $index = ((($res = strrpos($this->path, FileUtils::getSeparator())) === false) ? -1 : $res);
        if ($index < $this->prefixLength) {
            return substr($this->path, $this->prefixLength);
        }

        return substr($this->path, $index + 1);
    }

    /**
     * Returns the pathname string of this abstract pathname's parent, or
     * null if this pathname does not name a parent directory.
     *
     * The parent of an abstract pathname consists of the pathname's prefix,
     * if any, and each name in the pathname's name sequence except for the last.
     * If the name sequence is empty then the pathname does not name a parent
     * directory.
     *
     * @return string $pathname string of the parent directory named by this
     *                abstract pathname, or null if this pathname does not name a parent
     */
    public function getParent()
    {
        // that's a lastIndexOf
        $index = ((($res = strrpos($this->path, FileUtils::getSeparator())) === false) ? -1 : $res);
        if ($index < $this->prefixLength) {
            if (($this->prefixLength > 0) && (strlen($this->path) > $this->prefixLength)) {
                return substr($this->path, 0, $this->prefixLength);
            }

            return null;
        }

        return substr($this->path, 0, $index);
    }

    /**
     * Returns the abstract pathname of this abstract pathname's parent,
     * or null if this pathname does not name a parent directory.
     *
     * The parent of an abstract pathname consists of the pathname's prefix,
     * if any, and each name in the pathname's name sequence except for the
     * last.  If the name sequence is empty then the pathname does not name
     * a parent directory.
     *
     * @return null|File The abstract pathname of the parent directory named by this
     *                   abstract pathname, or null if this pathname
     *                   does not name a parent
     */
    public function getParentFile()
    {
        $p = $this->getParent();
        if (null === $p) {
            return null;
        }

        return new File((string) $p, (int) $this->prefixLength);
    }

    /**
     * Converts this abstract pathname into a pathname string.  The resulting
     * string uses the default name-separator character to separate the names
     * in the name sequence.
     *
     * @return string The string form of this abstract pathname
     */
    public function getPath()
    {
        return (string) $this->path;
    }

    /**
     * Returns path without leading basedir.
     *
     * @param string $basedir Base directory to strip
     *
     * @return string Path without basedir
     *
     * @uses getPath()
     */
    public function getPathWithoutBase($basedir)
    {
        if (!StringHelper::endsWith(FileUtils::getSeparator(), $basedir)) {
            $basedir .= FileUtils::getSeparator();
        }
        $path = $this->getPath();
        if (substr($path, 0, strlen($basedir)) != $basedir) {
            //path does not begin with basedir, we don't modify it
            return $path;
        }

        return substr($path, strlen($basedir));
    }

    /**
     * Tests whether this abstract pathname is absolute.  The definition of
     * absolute pathname is system dependent.  On UNIX systems, a pathname is
     * absolute if its prefix is "/".  On Win32 systems, a pathname is absolute
     * if its prefix is a drive specifier followed by "\\", or if its prefix
     * is "\\".
     *
     * @return bool true if this abstract pathname is absolute, false otherwise
     */
    public function isAbsolute()
    {
        return 0 !== $this->prefixLength;
    }

    /**
     * Returns the file extension for a given file. For example test.php would be returned as php.
     *
     * @return string the name of the extension
     */
    public function getFileExtension()
    {
        return pathinfo((string) $this->getAbsolutePath(), PATHINFO_EXTENSION);
    }

    /**
     * Returns the absolute pathname string of this abstract pathname.
     *
     * If this abstract pathname is already absolute, then the pathname
     * string is simply returned as if by the getPath method.
     * If this abstract pathname is the empty abstract pathname then
     * the pathname string of the current user directory, which is named by the
     * system property user.dir, is returned.  Otherwise this
     * pathname is resolved in a system-dependent way.  On UNIX systems, a
     * relative pathname is made absolute by resolving it against the current
     * user directory.  On Win32 systems, a relative pathname is made absolute
     * by resolving it against the current directory of the drive named by the
     * pathname, if any; if not, it is resolved against the current user
     * directory.
     *
     * @return string The absolute pathname string denoting the same file or
     *                directory as this abstract pathname
     *
     * @see    #isAbsolute()
     */
    public function getAbsolutePath()
    {
        $fs = FileSystem::getFileSystem();

        return $fs->resolveFile($this);
    }

    /**
     * Returns the absolute form of this abstract pathname.  Equivalent to
     * getAbsolutePath.
     *
     * @return File The absolute abstract pathname denoting the same file or
     *              directory as this abstract pathname
     */
    public function getAbsoluteFile()
    {
        return new File((string) $this->getAbsolutePath());
    }

    /**
     * Returns the canonical pathname string of this abstract pathname.
     *
     * A canonical pathname is both absolute and unique. The precise
     * definition of canonical form is system-dependent. This method first
     * converts this pathname to absolute form if necessary, as if by invoking the
     * getAbsolutePath() method, and then maps it to its unique form in a
     * system-dependent way.  This typically involves removing redundant names
     * such as "." and .. from the pathname, resolving symbolic links
     * (on UNIX platforms), and converting drive letters to a standard case
     * (on Win32 platforms).
     *
     * Every pathname that denotes an existing file or directory has a
     * unique canonical form.  Every pathname that denotes a nonexistent file
     * or directory also has a unique canonical form.  The canonical form of
     * the pathname of a nonexistent file or directory may be different from
     * the canonical form of the same pathname after the file or directory is
     * created.  Similarly, the canonical form of the pathname of an existing
     * file or directory may be different from the canonical form of the same
     * pathname after the file or directory is deleted.
     *
     * @return string The canonical pathname string denoting the same file or
     *                directory as this abstract pathname
     */
    public function getCanonicalPath()
    {
        $fs = FileSystem::getFileSystem();

        return $fs->canonicalize($this->path);
    }

    /**
     * Returns the canonical form of this abstract pathname.  Equivalent to
     * getCanonicalPath(.
     *
     * @return File The canonical pathname string denoting the same file or
     *              directory as this abstract pathname
     */
    public function getCanonicalFile()
    {
        return new File($this->getCanonicalPath());
    }

    // -- Attribute accessors --

    /**
     * Tests whether the application can read the file denoted by this
     * abstract pathname.
     *
     * @return bool true if and only if the file specified by this
     *              abstract pathname exists and can be read by the
     *              application; false otherwise
     */
    public function canRead()
    {
        $fs = FileSystem::getFileSystem();

        if ($fs->checkAccess($this)) {
            return (bool) @is_link($this->getAbsolutePath()) || @is_readable($this->getAbsolutePath());
        }

        return false;
    }

    /**
     * Tests whether the application can modify to the file denoted by this
     * abstract pathname.
     *
     * @return bool true if and only if the file system actually
     *              contains a file denoted by this abstract pathname and
     *              the application is allowed to write to the file;
     *              false otherwise
     */
    public function canWrite()
    {
        $fs = FileSystem::getFileSystem();

        return $fs->checkAccess($this, true);
    }

    /**
     * Tests whether the file denoted by this abstract pathname exists.
     *
     * @return bool true if and only if the file denoted by this
     *              abstract pathname exists; false otherwise
     */
    public function exists()
    {
        clearstatcache();

        if (is_link($this->path)) {
            return true;
        }

        if ($this->isDirectory()) {
            return true;
        }

        return @file_exists($this->path) || is_link($this->path);
    }

    /**
     * Tests whether the file denoted by this abstract pathname is a
     * directory.
     *
     * @throws IOException
     *
     * @return bool true if and only if the file denoted by this
     *              abstract pathname exists and is a directory;
     *              false otherwise
     */
    public function isDirectory()
    {
        clearstatcache();
        $fs = FileSystem::getFileSystem();
        if (true !== $fs->checkAccess($this)) {
            throw new IOException('No read access to ' . $this->path);
        }

        return @is_dir($this->path) && !@is_link($this->path);
    }

    /**
     * Tests whether the file denoted by this abstract pathname is a normal
     * file.  A file is normal if it is not a directory and, in
     * addition, satisfies other system-dependent criteria.  Any non-directory
     * file created by a Java application is guaranteed to be a normal file.
     *
     * @return bool true if and only if the file denoted by this
     *              abstract pathname exists and is a normal file;
     *              false otherwise
     */
    public function isFile()
    {
        clearstatcache();
        //$fs = FileSystem::getFileSystem();
        return @is_file($this->path);
    }

    /**
     * Tests whether the file denoted by this abstract pathname is a symbolic link.
     *
     * @throws IOException
     *
     * @return bool true if and only if the file denoted by this
     *              abstract pathname exists and is a symbolic link;
     *              false otherwise
     */
    public function isLink()
    {
        clearstatcache();
        $fs = FileSystem::getFileSystem();
        if (true !== $fs->checkAccess($this)) {
            throw new IOException('No read access to ' . $this->path);
        }

        return @is_link($this->path);
    }

    /**
     * Tests whether the file denoted by this abstract pathname is executable.
     *
     * @throws IOException
     *
     * @return bool true if and only if the file denoted by this
     *              abstract pathname exists and is a symbolic link;
     *              false otherwise
     */
    public function isExecutable()
    {
        clearstatcache();
        $fs = FileSystem::getFileSystem();
        if (true !== $fs->checkAccess($this)) {
            throw new IOException('No read access to ' . $this->path);
        }

        return @is_executable($this->path);
    }

    /**
     * Returns the target of the symbolic link denoted by this abstract pathname.
     *
     * @return string the target of the symbolic link denoted by this abstract pathname
     */
    public function getLinkTarget()
    {
        return @readlink($this->path);
    }

    /**
     * Returns the time that the file denoted by this abstract pathname was
     * last modified.
     *
     * @throws IOException
     *
     * @return int An int value representing the time the file was
     *             last modified, measured in seconds since the epoch
     *             (00:00:00 GMT, January 1, 1970), or 0 if the
     *             file does not exist or if an I/O error occurs
     */
    public function lastModified()
    {
        $fs = FileSystem::getFileSystem();
        if (true !== $fs->checkAccess($this)) {
            throw new IOException('No read access to ' . $this->path);
        }

        return $fs->getLastModifiedTime($this);
    }

    /**
     * Returns the length of the file denoted by this abstract pathname.
     * The return value is unspecified if this pathname denotes a directory.
     *
     * @throws IOException
     *
     * @return int The length, in bytes, of the file denoted by this abstract
     *             pathname, or 0 if the file does not exist
     */
    public function length()
    {
        $fs = FileSystem::getFileSystem();
        if (true !== $fs->checkAccess($this)) {
            throw new IOException('No read access to ' . $this->path . "\n");
        }

        return $fs->getLength($this);
    }

    /**
     * Convenience method for returning the contents of this file as a string.
     * This method uses file_get_contents() to read file in an optimized way.
     *
     * @throws Exception - if file cannot be read
     *
     * @return string
     */
    public function contents()
    {
        if (!$this->canRead() || !$this->isFile()) {
            throw new IOException('Cannot read file contents!');
        }

        return file_get_contents($this->getAbsolutePath());
    }

    // -- File operations --

    /**
     * Atomically creates a new, empty file named by this abstract pathname if
     * and only if a file with this name does not yet exist.  The check for the
     * existence of the file and the creation of the file if it does not exist
     * are a single operation that is atomic with respect to all other
     * filesystem activities that might affect the file.
     *
     * @param bool $parents
     *
     * @throws IOException
     *
     * @return bool true if the named file does not exist and was
     *              successfully created; <code>false</code> if the named file
     *              already exists
     */
    public function createNewFile($parents = true)
    {
        /**
         * @var File $parent
         */
        $parent = $this->getParentFile();
        if ($parents && null !== $parent && !$parent->exists()) {
            $parent->mkdirs();
        }

        return FileSystem::getFileSystem()->createNewFile($this->path);
    }

    /**
     * Deletes the file or directory denoted by this abstract pathname.  If
     * this pathname denotes a directory, then the directory must be empty in
     * order to be deleted.
     *
     * @param bool $recursive
     *
     * @throws IOException
     */
    public function delete($recursive = false)
    {
        $fs = FileSystem::getFileSystem();
        if (true !== $fs->canDelete($this)) {
            throw new IOException('Cannot delete ' . $this->path . "\n");
        }

        $fs->delete($this, $recursive);
    }

    /**
     * Requests that the file or directory denoted by this abstract pathname
     * be deleted when php terminates.  Deletion will be attempted only for
     * normal termination of php and if and if only Phing::shutdown() is
     * called.
     *
     * Once deletion has been requested, it is not possible to cancel the
     * request.  This method should therefore be used with care.
     */
    public function deleteOnExit()
    {
        $fs = FileSystem::getFileSystem();
        $fs->deleteOnExit($this);
    }

    /**
     * Returns an array of strings naming the files and directories in the
     * directory denoted by this abstract pathname.
     *
     * If this abstract pathname does not denote a directory, then this
     * method returns null  Otherwise an array of strings is
     * returned, one for each file or directory in the directory.  Names
     * denoting the directory itself and the directory's parent directory are
     * not included in the result.  Each string is a file name rather than a
     * complete path.
     *
     * There is no guarantee that the name strings in the resulting array
     * will appear in any specific order; they are not, in particular,
     * guaranteed to appear in alphabetical order.
     *
     * @return null|array An array of strings naming the files and directories in the
     *                    directory denoted by this abstract pathname.  The array will be
     *                    empty if the directory is empty.  Returns null if
     *                    this abstract pathname does not denote a directory, or if an
     *                    I/O error occurs.
     */
    public function listDir(): ?array
    {
        try {
            $elements = FileSystem::getFileSystem()->listContents($this);
        } catch (IOException $e) {
            $elements = null;
        }

        return $elements;
    }

    /**
     * Creates the directory named by this abstract pathname, including any
     * necessary but nonexistent parent directories.  Note that if this
     * operation fails it may have succeeded in creating some of the necessary
     * parent directories.
     *
     * @param null|int $mode
     *
     * @throws IOException
     *
     * @return bool true if and only if the directory was created,
     *              along with all necessary parent directories; false
     *              otherwise
     */
    public function mkdirs($mode = null)
    {
        if ($this->exists()) {
            return false;
        }

        try {
            if ($this->mkdir($mode)) {
                return true;
            }
        } catch (IOException $ioe) {
            // IOException from mkdir() means that directory propbably didn't exist.
        }
        $parentFile = $this->getParentFile();

        return (null !== $parentFile) && ($parentFile->mkdirs() && $this->mkdir($mode));
    }

    /**
     * Creates the directory named by this abstract pathname.
     *
     * @param null|int $mode
     *
     * @throws IOException
     *
     * @return bool true if and only if the directory was created; false otherwise
     */
    public function mkdir($mode = null)
    {
        $fs = FileSystem::getFileSystem();

        if (true !== $fs->checkAccess(new File($this->path), true)) {
            throw new IOException('No write access to ' . $this->getPath());
        }

        return $fs->createDirectory($this, $mode);
    }

    /**
     * Renames the file denoted by this abstract pathname.
     *
     * @param File $destFile The new abstract pathname for the named file
     *
     * @throws IOException
     */
    public function renameTo(File $destFile)
    {
        $fs = FileSystem::getFileSystem();
        if (true !== $fs->checkAccess($this)) {
            throw new IOException('No write access to ' . $this->getPath());
        }

        $fs->rename($this, $destFile);
    }

    /**
     * Simple-copies file denoted by this abstract pathname into another
     * PhingFile.
     *
     * @param File $destFile The new abstract pathname for the named file
     *
     * @throws IOException
     */
    public function copyTo(File $destFile)
    {
        $fs = FileSystem::getFileSystem();

        if (true !== $fs->checkAccess($this)) {
            throw new IOException('No read access to ' . $this->getPath() . "\n");
        }

        if (true !== $fs->checkAccess($destFile, true)) {
            throw new IOException('File::copyTo() No write access to ' . $destFile->getPath());
        }

        $fs->copy($this, $destFile);
    }

    /**
     * Sets the last-modified time of the file or directory named by this
     * abstract pathname.
     *
     * All platforms support file-modification times to the nearest second,
     * but some provide more precision.  The argument will be truncated to fit
     * the supported precision.  If the operation succeeds and no intervening
     * operations on the file take place, then the next invocation of the
     * lastModified method will return the (possibly truncated) time argument
     * that was passed to this method.
     *
     * @param int $time The new last-modified time, measured in milliseconds since
     *                  the epoch (00:00:00 GMT, January 1, 1970)
     *
     * @throws Exception
     */
    public function setLastModified($time)
    {
        $time = (int) $time;
        if ($time < 0) {
            throw new Exception("IllegalArgumentException, Negative {$time}\n");
        }

        $fs = FileSystem::getFileSystem();

        $fs->setLastModifiedTime($this, $time);
    }

    /**
     * Sets the owner of the file.
     *
     * @param mixed $user user name or number
     *
     * @throws IOException
     */
    public function setUser($user)
    {
        $fs = FileSystem::getFileSystem();

        $fs->chown($this->getPath(), $user);
    }

    /**
     * Sets the group of the file.
     *
     * @param string $group
     *
     * @throws IOException
     */
    public function setGroup($group)
    {
        $fs = FileSystem::getFileSystem();

        $fs->chgrp($this->getPath(), $group);
    }

    /**
     * Sets the mode of the file.
     *
     * @param int $mode octal mode
     *
     * @throws IOException
     */
    public function setMode($mode)
    {
        $fs = FileSystem::getFileSystem();

        $fs->chmod($this->getPath(), $mode);
    }

    /**
     * Retrieve the mode of this file.
     *
     * @return int
     */
    public function getMode()
    {
        return @fileperms($this->getPath());
    }

    // -- Basic infrastructure --

    /**
     * Compares two abstract pathnames lexicographically.  The ordering
     * defined by this method depends upon the underlying system.  On UNIX
     * systems, alphabetic case is significant in comparing pathnames; on Win32
     * systems it is not.
     *
     * @param File $file th file whose pathname sould be compared to the pathname of this file
     *
     * @return int Zero if the argument is equal to this abstract pathname, a
     *             value less than zero if this abstract pathname is
     *             lexicographically less than the argument, or a value greater
     *             than zero if this abstract pathname is lexicographically
     *             greater than the argument
     */
    public function compareTo(File $file)
    {
        $fs = FileSystem::getFileSystem();

        return $fs->compare($this, $file);
    }

    /**
     * Tests this abstract pathname for equality with the given object.
     * Returns <code>true</code> if and only if the argument is not
     * <code>null</code> and is an abstract pathname that denotes the same file
     * or directory as this abstract pathname.  Whether or not two abstract
     * pathnames are equal depends upon the underlying system.  On UNIX
     * systems, alphabetic case is significant in comparing pathnames; on Win32
     * systems it is not.
     *
     * @param File $obj
     *
     * @return bool
     */
    public function equals($obj)
    {
        if ((null !== $obj) && ($obj instanceof File)) {
            return 0 === $this->compareTo($obj);
        }

        return false;
    }

    // -- constructors not called by signature match, so we need some helpers --

    /**
     * @throws IOException
     */
    protected function constructPathname(string $pathname): void
    {
        $fs = FileSystem::getFileSystem();

        $this->path = (string) $fs->normalize($pathname);
        $this->prefixLength = (int) $fs->prefixLength($this->path);
    }

    /**
     * @throws IOException
     */
    protected function constructStringParentStringChild(string $parent, string $child): void
    {
        $fs = FileSystem::getFileSystem();

        if ('' === $parent) {
            $this->path = $fs->resolve($fs->getDefaultParent(), $fs->normalize($child));
        } else {
            $this->path = $fs->resolve($fs->normalize($parent), $fs->normalize($child));
        }

        $this->prefixLength = (int) $fs->prefixLength($this->path);
    }

    /**
     * @throws IOException
     */
    protected function constructFileParentStringChild(File $parent, string $child): void
    {
        $fs = FileSystem::getFileSystem();

        if ('' === $parent->path) {
            $this->path = $fs->resolve($fs->getDefaultParent(), $fs->normalize($child));
        } else {
            $this->path = $fs->resolve($parent->path, $fs->normalize($child));
        }

        $this->prefixLength = $fs->prefixLength($this->path);
    }
}
