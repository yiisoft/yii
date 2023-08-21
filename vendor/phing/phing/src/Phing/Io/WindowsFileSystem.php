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

use InvalidArgumentException;
use Phing\Phing;
use Phing\Util\StringHelper;

class WindowsFileSystem extends FileSystem
{
    protected $slash;
    protected $altSlash;
    protected $semicolon;

    private static $driveDirCache = [];

    public function __construct()
    {
        $this->slash = self::getSeparator();
        $this->semicolon = self::getPathSeparator();
        $this->altSlash = ('\\' === $this->slash) ? '/' : '\\';
    }

    /**
     * @param $c
     *
     * @return bool
     */
    public function isSlash($c)
    {
        return ('\\' == $c) || ('/' == $c);
    }

    /**
     * @param $c
     *
     * @return bool
     */
    public function isLetter($c)
    {
        return ((ord($c) >= ord('a')) && (ord($c) <= ord('z')))
            || ((ord($c) >= ord('A')) && (ord($c) <= ord('Z')));
    }

    /**
     * @param $p
     *
     * @return string
     */
    public function slashify($p)
    {
        if ((strlen($p) > 0) && ($p[0] != $this->slash)) {
            return $this->slash . $p;
        }

        return $p;
    }

    // -- Normalization and construction --

    /**
     * @return string
     */
    public function getSeparator()
    {
        // the ascii value of is the \
        return chr(92);
    }

    /**
     * @return string
     */
    public function getPathSeparator()
    {
        return ';';
    }

    /**
     * A normal Win32 pathname contains no duplicate slashes, except possibly
     * for a UNC prefix, and does not end with a slash.  It may be the empty
     * string.  Normalized Win32 pathnames have the convenient property that
     * the length of the prefix almost uniquely identifies the type of the path
     * and whether it is absolute or relative:.
     *
     *    0  relative to both drive and directory
     *    1  drive-relative (begins with '\\')
     *    2  absolute UNC (if first char is '\\'), else directory-relative (has form "z:foo")
     *    3  absolute local pathname (begins with "z:\\")
     *
     * @param  $strPath
     * @param  $len
     * @param  $sb
     *
     * @return int
     */
    public function normalizePrefix($strPath, $len, &$sb)
    {
        $src = 0;
        while (($src < $len) && $this->isSlash($strPath[$src])) {
            ++$src;
        }
        $c = '';
        if (
            ($len - $src >= 2)
            && $this->isLetter($c = $strPath[$src])
            && ':' === $strPath[$src + 1]
        ) {
            /* Remove leading slashes if followed by drive specifier.
             * This hack is necessary to support file URLs containing drive
             * specifiers (e.g., "file://c:/path").  As a side effect,
             * "/c:/path" can be used as an alternative to "c:/path". */
            $sb .= $c;
            $sb .= ':';
            $src += 2;
        } else {
            $src = 0;
            if (
                ($len >= 2)
                && $this->isSlash($strPath[0])
                && $this->isSlash($strPath[1])
            ) {
                /* UNC pathname: Retain first slash; leave src pointed at
                 * second slash so that further slashes will be collapsed
                 * into the second slash.  The result will be a pathname
                 * beginning with "\\\\" followed (most likely) by a host
                 * name. */
                $src = 1;
                $sb .= $this->slash;
            }
        }

        return $src;
    }

    /**
     * Check that the given pathname is normal.  If not, invoke the real
     * normalizer on the part of the pathname that requires normalization.
     * This way we iterate through the whole pathname string only once.
     *
     * @param string $strPath
     *
     * @return string
     */
    public function normalize($strPath)
    {
        $strPath = $this->fixEncoding($strPath);

        if ($this->isPharArchive($strPath)) {
            return str_replace('\\', '/', $strPath);
        }

        $n = strlen($strPath);
        $slash = $this->slash;
        $altSlash = $this->altSlash;
        $prev = 0;
        for ($i = 0; $i < $n; ++$i) {
            $c = $strPath[$i];
            if ($c === $altSlash) {
                return $this->normalizer($strPath, $n, ($prev === $slash) ? $i - 1 : $i);
            }
            if (($c === $slash) && ($prev === $slash) && ($i > 1)) {
                return $this->normalizer($strPath, $n, $i - 1);
            }
            if ((':' === $c) && ($i > 1)) {
                return $this->normalizer($strPath, $n, 0);
            }
            $prev = $c;
        }
        if ($prev === $slash) {
            return $this->normalizer($strPath, $n, $n - 1);
        }

        return $strPath;
    }

    /**
     * @param string $strPath
     *
     * @return int
     */
    public function prefixLength($strPath)
    {
        if ($this->isPharArchive($strPath)) {
            return 0;
        }

        $path = (string) $strPath;
        $slash = (string) $this->slash;
        $n = (int) strlen($path);
        if (0 === $n) {
            return 0;
        }
        $c0 = $path[0];
        $c1 = ($n > 1) ? $path[1] :
            0;
        if ($c0 === $slash) {
            if ($c1 === $slash) {
                return 2; // absolute UNC pathname "\\\\foo"
            }

            return 1; // drive-relative "\\foo"
        }

        if ($this->isLetter($c0) && (':' === $c1)) {
            if (($n > 2) && ($path[2]) === $slash) {
                return 3; // Absolute local pathname "z:\\foo" */
            }

            return 2; // Directory-relative "z:foo"
        }

        return 0; // Completely relative
    }

    /**
     * @param string $parent
     * @param string $child
     *
     * @return string
     */
    public function resolve($parent, $child)
    {
        $parent = (string) $parent;
        $child = (string) $child;
        $slash = (string) $this->slash;

        $pn = (int) strlen($parent);
        if (0 === $pn) {
            return $child;
        }
        $cn = (int) strlen($child);
        if (0 === $cn) {
            return $parent;
        }

        $c = $child;
        if (($cn > 1) && ($c[0] === $slash)) {
            if ($c[1] === $slash) {
                // drop prefix when child is a UNC pathname
                $c = substr($c, 2);
            } else {
                //Drop prefix when child is drive-relative */
                $c = substr($c, 1);
            }
        }

        $p = $parent;
        if ($p[$pn - 1] === $slash) {
            $p = substr($p, 0, $pn - 1);
        }

        return $p . $this->slashify($c);
    }

    /**
     * @return string
     */
    public function getDefaultParent()
    {
        return (string) ('' . $this->slash);
    }

    /**
     * @param string $strPath
     *
     * @return string
     */
    public function fromURIPath($strPath)
    {
        $p = (string) $strPath;
        if ((strlen($p) > 2) && (':' === $p[2])) {
            // "/c:/foo" --> "c:/foo"
            $p = substr($p, 1);

            // "c:/foo/" --> "c:/foo", but "c:/" --> "c:/"
            if ((strlen($p) > 3) && StringHelper::endsWith('/', $p)) {
                $p = substr($p, 0, strlen($p) - 1);
            }
        } elseif ((strlen($p) > 1) && StringHelper::endsWith('/', $p)) {
            // "/foo/" --> "/foo"
            $p = substr($p, 0, strlen($p) - 1);
        }

        return (string) $p;
    }

    // -- Path operations --

    /**
     * @return bool
     */
    public function isAbsolute(File $f)
    {
        $pl = (int) $f->getPrefixLength();
        $p = (string) $f->getPath();

        return ((2 === $pl) && ($p[0] === $this->slash)) || (3 === $pl) || (1 === $pl && $p[0] === $this->slash);
    }

    public function resolveFile(File $f)
    {
        $path = $f->getPath();
        $pl = (int) $f->getPrefixLength();

        if ((2 === $pl) && ($path[0] === $this->slash)) {
            return $path; // UNC
        }

        if (3 === $pl) {
            return $path; // Absolute local
        }

        if (0 === $pl) {
            if ($this->isPharArchive($path)) {
                return $path;
            }

            return (string) ($this->getUserPath() . $this->slashify($path)); //Completely relative
        }

        if (1 === $pl) { // Drive-relative
            $up = (string) $this->getUserPath();
            $ud = (string) $this->getDrive($up);
            if (null !== $ud) {
                return (string) $ud . $path;
            }

            return (string) $up . $path; //User dir is a UNC path
        }

        if (2 === $pl) { // Directory-relative
            $up = (string) $this->getUserPath();
            $ud = (string) $this->getDrive($up);
            if ((null !== $ud) && StringHelper::startsWith($ud, $path)) {
                return (string) ($up . $this->slashify(substr($path, 2)));
            }
            $drive = (string) $path[0];
            $dir = (string) $this->getDriveDirectory($drive);

            if (null !== $dir) {
                /* When resolving a directory-relative path that refers to a
                drive other than the current drive, insist that the caller
                have read permission on the result */
                $p = (string) $drive . (':' . $dir . $this->slashify(substr($path, 2)));

                if (!$this->checkAccess(new File($p))) {
                    throw new IOException("Can't resolve path {$p}");
                }

                return $p;
            }

            return (string) $drive . ':' . $this->slashify(substr($path, 2)); //fake it
        }

        throw new InvalidArgumentException('Unresolvable path: ' . $path);
    }

    // -- most of the following is mapped to the functions mapped th php natives in FileSystem

    // -- Basic infrastructure --

    /**
     * compares file paths lexicographically.
     *
     * @return int
     */
    public function compare(File $f1, File $f2)
    {
        $f1Path = $f1->getPath();
        $f2Path = $f2->getPath();

        return strcasecmp((string) $f1Path, (string) $f2Path);
    }

    /**
     * Normalize the given pathname, whose length is len, starting at the given
     * offset; everything before this offset is already normal.
     *
     * @param  $strPath
     * @param  $len
     * @param  $offset
     *
     * @return string
     */
    protected function normalizer($strPath, $len, $offset)
    {
        if (0 == $len) {
            return $strPath;
        }
        if ($offset < 3) {
            $offset = 0; //Avoid fencepost cases with UNC pathnames
        }
        $src = 0;
        $slash = $this->slash;
        $sb = '';

        if (0 == $offset) {
            // Complete normalization, including prefix
            $src = $this->normalizePrefix($strPath, $len, $sb);
        } else {
            // Partial normalization
            $src = $offset;
            $sb .= substr($strPath, 0, $offset);
        }

        // Remove redundant slashes from the remainder of the path, forcing all
        // slashes into the preferred slash
        while ($src < $len) {
            $c = $strPath[$src++];
            if ($this->isSlash($c)) {
                while (($src < $len) && $this->isSlash($strPath[$src])) {
                    ++$src;
                }
                if ($src === $len) {
                    // Check for trailing separator
                    $sn = (int) strlen($sb);
                    if ((2 == $sn) && (':' === $sb[1])) {
                        // "z:\\"
                        $sb .= $slash;

                        break;
                    }
                    if (0 === $sn) {
                        // "\\"
                        $sb .= $slash;

                        break;
                    }
                    if ((1 === $sn) && ($this->isSlash($sb[0]))) {
                        /* "\\\\" is not collapsed to "\\" because "\\\\" marks
                        the beginning of a UNC pathname.  Even though it is
                        not, by itself, a valid UNC pathname, we leave it as
                        is in order to be consistent with the win32 APIs,
                        which treat this case as an invalid UNC pathname
                        rather than as an alias for the root directory of
                        the current drive. */
                        $sb .= $slash;

                        break;
                    }
                    // Path does not denote a root directory, so do not append
                    // trailing slash
                    break;
                }

                $sb .= $slash;
            } else {
                $sb .= $c;
            }
        }

        return (string) $sb;
    }

    /**
     * @param  $d
     *
     * @return int
     */
    private function getDriveIndex($d)
    {
        $d = (string) $d[0];
        if ((ord($d) >= ord('a')) && (ord($d) <= ord('z'))) {
            return ord($d) - ord('a');
        }
        if ((ord($d) >= ord('A')) && (ord($d) <= ord('Z'))) {
            return ord($d) - ord('A');
        }

        return -1;
    }

    /**
     * @param  $strPath
     *
     * @return bool
     */
    private function isPharArchive($strPath)
    {
        return 0 === strpos($strPath, 'phar://');
    }

    /**
     * @param $drive
     *
     * @return null|mixed
     */
    private function getDriveDirectory($drive)
    {
        $drive = (string) $drive[0];
        $i = (int) $this->getDriveIndex($drive);
        if ($i < 0) {
            return null;
        }

        $s = (self::$driveDirCache[$i] ?? null);

        if (null !== $s) {
            return $s;
        }

        $s = $this->getDriveDirectory($i + 1);
        self::$driveDirCache[$i] = $s;

        return $s;
    }

    /**
     * @return string
     */
    private function getUserPath()
    {
        //For both compatibility and security, we must look this up every time
        return (string) $this->normalize(Phing::getProperty('user.dir'));
    }

    /**
     * @param $path
     *
     * @return null|string
     */
    private function getDrive($path)
    {
        $path = (string) $path;
        $pl = $this->prefixLength($path);

        return (3 === $pl) ? substr($path, 0, 2) : null;
    }

    /**
     * On Windows platforms, PHP will mangle non-ASCII characters, see http://bugs.php.net/bug.php?id=47096.
     *
     * @param  $strPath
     *
     * @return mixed|string
     */
    private function fixEncoding($strPath)
    {
        $charSet = trim(strstr(setlocale(LC_CTYPE, ''), '.'), '.');
        if ('utf8' === $charSet) {
            return $strPath;
        }
        $codepage = 'CP' . $charSet;
        if (function_exists('iconv')) {
            $strPath = iconv('UTF-8', $codepage . '//IGNORE', $strPath);
        } elseif (function_exists('mb_convert_encoding')) {
            $strPath = mb_convert_encoding($strPath, $codepage, 'UTF-8');
        }

        return $strPath;
    }
}
