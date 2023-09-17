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
use Phing\Exception\BuildException;
use Phing\Filter\ChainReaderHelper;
use Phing\Phing;
use Phing\Project;
use Phing\Util\Character;
use Phing\Util\StringHelper;

/**
 * File utility class.
 * - handles os independent stuff etc
 * - mapper stuff
 * - filter stuff.
 */
class FileUtils
{
    /**
     * path separator string, static, obtained from FileSystem (; or :).
     */
    private static $pathSeparator;

    /**
     * separator string, static, obtained from FileSystem.
     */
    private static $separator;

    /**
     * @var false
     */
    private $dosWithDrive;

    /**
     * @throws IOException
     */
    public static function getPathSeparator(): string
    {
        if (null === self::$pathSeparator) {
            self::$pathSeparator = FileSystem::getFileSystem()->getPathSeparator();
        }

        return self::$pathSeparator;
    }

    /**
     * @throws IOException
     */
    public static function getSeparator(): string
    {
        if (null === self::$separator) {
            self::$separator = FileSystem::getFileSystem()->getSeparator();
        }

        return self::$separator;
    }

    /**
     * Returns the path to the temp directory.
     *
     * @return string
     */
    public static function getTempDir()
    {
        return Phing::getProperty('php.tmpdir');
    }

    /**
     * Returns the default file/dir creation mask value
     * (The mask value is prepared w.r.t the current user's file-creation mask value).
     *
     * @param bool $dirmode Directory creation mask to select
     *
     * @return int Creation Mask in octal representation
     */
    public static function getDefaultFileCreationMask($dirmode = false): int
    {
        // Preparing the creation mask base permission
        $permission = (true === $dirmode) ? 0777 : 0666;

        // Default mask information
        $defaultmask = sprintf('%03o', ($permission & ($permission - (int) sprintf('%04o', umask()))));

        return octdec($defaultmask);
    }

    /**
     * Returns a new Reader with filterchains applied.  If filterchains are empty,
     * simply returns passed reader.
     *
     * @param Reader $in            reader to modify (if appropriate)
     * @param array  &$filterChains filter chains to apply
     *
     * @return Reader assembled Reader (w/ filter chains)
     */
    public static function getChainedReader(Reader $in, &$filterChains, Project $project)
    {
        if (!empty($filterChains)) {
            $crh = new ChainReaderHelper();
            $crh->setBufferSize(65536); // 64k buffer, but isn't being used (yet?)
            $crh->setPrimaryReader($in);
            $crh->setFilterChains($filterChains);
            $crh->setProject($project);

            return $crh->getAssembledReader();
        }

        return $in;
    }

    /**
     * Copies a file using filter chains.
     *
     * @param bool  $overwrite
     * @param bool  $preserveLastModified
     * @param array $filterChains
     * @param int   $mode
     * @param bool  $preservePermissions
     *
     * @throws IOException
     */
    public function copyFile(
        File $sourceFile,
        File $destFile,
        Project $project,
        $overwrite = false,
        $preserveLastModified = true,
        &$filterChains = null,
        $mode = 0755,
        $preservePermissions = true,
        int $granularity = 0
    ) {
        if (
            $overwrite
            || !$destFile->exists()
            || $destFile->lastModified() < $sourceFile->lastModified() - $granularity
        ) {
            if ($destFile->exists() && ($destFile->isFile() || $destFile->isLink())) {
                $destFile->delete();
            }

            // ensure that parent dir of dest file exists!
            $parent = $destFile->getParentFile();
            if (null !== $parent && !$parent->exists()) {
                // Setting source directory permissions to target
                // (On permissions preservation, the target directory permissions
                // will be inherited from the source directory, otherwise the 'mode'
                // will be used)
                $dirMode = ($preservePermissions ? $sourceFile->getParentFile()->getMode() : $mode);

                $parent->mkdirs($dirMode);
            }

            if ((is_array($filterChains)) && (!empty($filterChains))) {
                $in = self::getChainedReader(new BufferedReader(new FileReader($sourceFile)), $filterChains, $project);
                $out = new BufferedWriter(new FileWriter($destFile));

                // New read() methods returns a big buffer.
                while (-1 !== ($buffer = $in->read())) { // -1 indicates EOF
                    $out->write($buffer);
                }

                if (null !== $in) {
                    $in->close();
                }
                if (null !== $out) {
                    $out->close();
                }

                // Set/Copy the permissions on the target
                if (true === $preservePermissions) {
                    $destFile->setMode($sourceFile->getMode());
                }
            } else {
                // simple copy (no filtering)
                $sourceFile->copyTo($destFile);

                // By default, PHP::Copy also copies the file permissions. Therefore,
                // re-setting the mode with the "user file-creation mask" information.
                if (false === $preservePermissions) {
                    $destFile->setMode(FileUtils::getDefaultFileCreationMask());
                }
            }

            if ($preserveLastModified && !$destFile->isLink()) {
                $destFile->setLastModified($sourceFile->lastModified());
            }
        }
    }

    /**
     * Attempts to rename a file from a source to a destination.
     * If overwrite is set to true, this method overwrites existing file even if the destination file is newer.
     * Otherwise, the source file is renamed only if the destination file is older than it.
     *
     * @param mixed $overwrite
     *
     * @throws IOException
     */
    public function renameFile(File $sourceFile, File $destFile, $overwrite = false): void
    {
        // ensure that parent dir of dest file exists!
        $parent = $destFile->getParentFile();
        if (null !== $parent) {
            if (!$parent->exists()) {
                $parent->mkdirs();
            }
        }

        if ($overwrite || !$destFile->exists() || $destFile->lastModified() < $sourceFile->lastModified()) {
            if ($destFile->exists()) {
                try {
                    $destFile->delete();
                } catch (Exception $e) {
                    throw new BuildException(
                        'Unable to remove existing file ' . $destFile->__toString() . ': ' . $e->getMessage()
                    );
                }
            }
        }

        $sourceFile->renameTo($destFile);
    }

    /**
     * Interpret the filename as a file relative to the given file -
     * unless the filename already represents an absolute filename.
     *
     * @param File   $file     the "reference" file for relative paths. This
     *                         instance must be an absolute file and must
     *                         not contain ./ or ../ sequences (same for \
     *                         instead of /).
     * @param string $filename a file name
     *
     * @throws IOException
     *
     * @return File A PhingFile object pointing to an absolute file that doesn't contain ./ or ../ sequences
     *              and uses the correct separator for the current platform.
     */
    public function resolveFile(File $file, string $filename): File
    {
        // remove this and use the static class constant File::separator
        // as soon as ZE2 is ready
        $fs = FileSystem::getFileSystem();

        $filename = str_replace(['\\', '/'], $fs->getSeparator(), $filename);

        // deal with absolute files
        if (
            StringHelper::startsWith($fs->getSeparator(), $filename)
            || (strlen($filename) >= 2
                && Character::isLetter($filename[0])
                && ':' === $filename[1])
        ) {
            return new File($this->normalize($filename));
        }

        if (strlen($filename) >= 2 && Character::isLetter($filename[0]) && ':' === $filename[1]) {
            return new File($this->normalize($filename));
        }

        $helpFile = new File($file->getAbsolutePath());

        $tok = strtok($filename, $fs->getSeparator());
        while (false !== $tok) {
            $part = $tok;
            if ('..' === $part) {
                $parentFile = $helpFile->getParent();
                if (null === $parentFile) {
                    $msg = "The file or path you specified ({$filename}) is invalid relative to " . $file->getPath();

                    throw new IOException($msg);
                }
                $helpFile = new File($parentFile);
            } elseif ('.' !== $part) {
                $helpFile = new File($helpFile, $part);
            }
            $tok = strtok($fs->getSeparator());
        }

        return new File($helpFile->getAbsolutePath());
    }

    /**
     * Normalize the given absolute path.
     *
     * This includes:
     *   - Uppercase the drive letter if there is one.
     *   - Remove redundant slashes after the drive spec.
     *   - resolve all ./, .\, ../ and ..\ sequences.
     *   - DOS style paths that start with a drive letter will have
     *     \ as the separator.
     *
     * @param string $path path to normalize
     *
     * @throws IOException
     * @throws BuildException
     */
    public function normalize(string $path): string
    {
        $dissect = $this->dissect($path);
        $sep = self::getSeparator();

        $s = [];
        $s[] = $dissect[0];
        $tok = strtok($dissect[1], $sep);
        while (false !== $tok) {
            $thisToken = $tok;
            if ('.' === $thisToken) {
                $tok = strtok($sep);

                continue;
            }

            if ('..' === $thisToken) {
                if (count($s) < 2) {
                    // using '..' in path that is too short
                    throw new IOException("Cannot resolve path: {$path}");
                }

                array_pop($s);
            } else { // plain component
                $s[] = $thisToken;
            }
            $tok = strtok($sep);
        }

        $sb = '';
        foreach ($s as $i => $v) {
            if ($i > 1) {
                // not before the filesystem root and not after it, since root
                // already contains one
                $sb .= $sep;
            }
            $sb .= $v;
        }

        $path = $sb;
        if (true === $this->dosWithDrive) {
            $path = str_replace('/', '\\', $path);
        }

        return $path;
    }

    /**
     * Dissect the specified absolute path.
     *
     * @throws BuildException
     * @throws IOException
     *
     * @return array {root, remainig path}
     */
    public function dissect(string $path): array
    {
        $sep = self::getSeparator();
        $path = str_replace(['\\', '/'], $sep, $path);

        // make sure we are dealing with an absolute path
        if (
            !StringHelper::startsWith($sep, $path)
            && !(strlen($path) >= 2
                && Character::isLetter($path[0])
                && ':' === $path[1])
        ) {
            throw new BuildException("{$path} is not an absolute path");
        }

        $this->dosWithDrive = false;
        $root = null;

        // Eliminate consecutive slashes after the drive spec

        if (strlen($path) >= 2 && Character::isLetter($path[0]) && ':' === $path[1]) {
            $this->dosWithDrive = true;

            $ca = str_replace('/', '\\', $path);

            $path = strtoupper($ca[0]) . ':';

            for ($i = 2, $_i = strlen($ca); $i < $_i; ++$i) {
                if (
                    ('\\' !== $ca[$i])
                    || ('\\' === $ca[$i]
                        && '\\' !== $ca[$i - 1])
                ) {
                    $path .= $ca[$i];
                }
            }

            $path = str_replace('\\', $sep, $path);

            if (2 === strlen($path)) {
                $root = $path;
                $path = '';
            } else {
                $root = substr($path, 0, 3);
                $path = substr($path, 3);
            }
        } else {
            if (1 === strlen($path)) {
                $root = $sep;
                $path = '';
            } else {
                if ($path[1] === $sep) {
                    // UNC drive
                    $root = $sep . $sep;
                    $path = substr($path, 2);
                } else {
                    $root = $sep;
                    $path = substr($path, 1);
                }
            }
        }

        return [$root, $path];
    }

    /**
     * Create a temporary file in a given directory.
     *
     * <p>The file denoted by the returned abstract pathname did not
     * exist before this method was invoked, any subsequent invocation
     * of this method will yield a different file name.</p>
     *
     * @param string $prefix       prefix before the random number
     * @param string $suffix       file extension; include the '.'.
     * @param File   $parentDir    directory to create the temporary file in;
     *                             sys_get_temp_dir() used if not specified
     * @param bool   $deleteOnExit whether to set the tempfile for deletion on
     *                             normal exit
     * @param bool   $createFile   true if the file must actually be created. If false
     *                             chances exist that a file with the same name is
     *                             created in the time between invoking this method
     *                             and the moment the file is actually created. If
     *                             possible set to true.
     *
     * @throws BuildException
     *
     * @return File a File reference to the new temporary file
     */
    public function createTempFile(
        $prefix,
        $suffix,
        File $parentDir = null,
        $deleteOnExit = false,
        $createFile = false
    ): File {
        $result = null;
        $parent = (null === $parentDir) ? self::getTempDir() : $parentDir->getPath();

        if ($createFile) {
            try {
                $directory = new File($parent);
                // quick but efficient hack to create a unique filename ;-)
                $result = null;
                do {
                    $result = new File($directory, $prefix . substr(md5(time()), 0, 8) . $suffix);
                } while (file_exists($result->getPath()));

                $fs = FileSystem::getFileSystem();
                $fs->createNewFile($result->getPath());
                $fs->lock($result);
            } catch (IOException $e) {
                throw new BuildException('Could not create tempfile in ' . $parent, $e);
            }
        } else {
            do {
                $result = new File($parent, $prefix . substr(md5((string) time()), 0, 8) . $suffix);
            } while ($result->exists());
        }

        if ($deleteOnExit) {
            $result->deleteOnExit();
        }

        return $result;
    }

    /**
     * @throws IOException
     *
     * @return bool whether contents of two files is the same
     */
    public function contentEquals(File $file1, File $file2): bool
    {
        if (!($file1->exists() && $file2->exists())) {
            return false;
        }

        if (!($file1->canRead() && $file2->canRead())) {
            return false;
        }

        if ($file1->isDirectory() || $file2->isDirectory()) {
            return false;
        }

        $c1 = file_get_contents($file1->getAbsolutePath());
        $c2 = file_get_contents($file2->getAbsolutePath());

        return trim($c1) === trim($c2);
    }
}
