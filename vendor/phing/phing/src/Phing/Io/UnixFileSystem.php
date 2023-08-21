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
use Phar;
use Phing\Phing;
use Phing\Util\StringHelper;

/**
 * UnixFileSystem class. This class encapsulates the basic file system functions
 * for platforms using the unix (posix)-stylish filesystem. It wraps php native
 * functions suppressing normal PHP error reporting and instead uses Exception
 * to report and error.
 *
 * This class is part of a oop based filesystem abstraction and targeted to run
 * on all supported php platforms.
 *
 * Note: For debugging turn track_errors on in the php.ini. The error messages
 * and log messages from this class will then be clearer because $php_errormsg
 * is passed as part of the message.
 *
 * FIXME:
 *  - Comments
 *  - Error handling reduced to min, error are handled by PhingFile mainly
 *
 * @author Andreas Aderhold, andi@binarycloud.com
 */
class UnixFileSystem extends FileSystem
{
    /**
     * returns OS dependent path separator char.
     *
     * @return string
     */
    public function getSeparator()
    {
        return '/';
    }

    /**
     * returns OS dependent directory separator char.
     *
     * @return string
     */
    public function getPathSeparator()
    {
        return ':';
    }

    /**
     * A normal Unix pathname contains no duplicate slashes and does not end
     * with a slash.  It may be the empty string.
     *
     * Check that the given pathname is normal.  If not, invoke the real
     * normalizer on the part of the pathname that requires normalization.
     * This way we iterate through the whole pathname string only once.
     *
     * NOTE: this method no longer expands the tilde (~) character!
     *
     * @param string $strPathname
     *
     * @return string
     */
    public function normalize($strPathname)
    {
        if (!strlen($strPathname)) {
            return '';
        }

        // Start normalising after any scheme that is present.
        // This prevents phar:///foo being normalised into phar:/foo
        // Use a regex as some paths may not by parsed by parse_url().
        if (preg_match('{^[a-z][a-z0-9+\-\.]+://}', $strPathname)) {
            $i = strpos($strPathname, '://') + 3;
        } else {
            $i = 0;
        }

        $n = strlen($strPathname);
        $prevChar = 0;
        for (; $i < $n; ++$i) {
            $c = $strPathname[$i];
            if (('/' === $prevChar) && ('/' === $c)) {
                return self::normalizer($strPathname, $n, $i - 1);
            }
            $prevChar = $c;
        }
        if ('/' === $prevChar) {
            return self::normalizer($strPathname, $n, $n - 1);
        }

        return $strPathname;
    }

    /**
     * Compute the length of the pathname string's prefix.  The pathname
     * string must be in normal form.
     *
     * @param string $pathname
     *
     * @return int
     */
    public function prefixLength($pathname)
    {
        if (0 === strlen($pathname)) {
            return 0;
        }

        if (class_exists('Phar', false) && method_exists('Phar', 'running')) {
            $phar = Phar::running();
            $pharAlias = 'phar://' . Phing::PHAR_ALIAS;

            if ($phar && 0 === strpos($pathname, $phar)) {
                return strlen($phar);
            }

            if ($phar && 0 === strpos($pathname, $pharAlias)) {
                return strlen($pharAlias);
            }
        }

        return ('/' === $pathname[0]) ? 1 : 0;
    }

    /**
     * Resolve the child pathname string against the parent.
     * Both strings must be in normal form, and the result
     * will be in normal form.
     *
     * @param string $parent
     * @param string $child
     *
     * @return string
     */
    public function resolve($parent, $child)
    {
        if ('' === $child) {
            return $parent;
        }

        if ('/' === $child[0]) {
            if ('/' === $parent) {
                return $child;
            }

            return $parent . $child;
        }

        if ('/' === $parent) {
            return $parent . $child;
        }

        return $parent . '/' . $child;
    }

    /**
     * @return string
     */
    public function getDefaultParent()
    {
        return '/';
    }

    /**
     * @return bool
     */
    public function isAbsolute(File $f)
    {
        return 0 !== $f->getPrefixLength();
    }

    /**
     * the file resolver.
     *
     * @return string
     */
    public function resolveFile(File $f)
    {
        // resolve if parent is a file oject only
        if ($this->isAbsolute($f)) {
            return $f->getPath();
        }

        return $this->resolve(Phing::getProperty('user.dir'), $f->getPath());
    }

    // -- most of the following is mapped to the php natives wrapped by FileSystem

    // -- Attribute accessors --

    /**
     * compares file paths lexicographically.
     *
     * @return int
     */
    public function compare(File $f1, File $f2)
    {
        $f1Path = $f1->getPath();
        $f2Path = $f2->getPath();

        return strcmp((string) $f1Path, (string) $f2Path);
    }

    /**
     * Copy a file, takes care of symbolic links.
     *
     * @param File $src  source path and name file to copy
     * @param File $dest destination path and name of new file
     *
     * @throws Exception if file cannot be copied
     */
    public function copy(File $src, File $dest)
    {
        if (!$src->isLink()) {
            parent::copy($src, $dest);

            return;
        }

        $srcPath = $src->getAbsolutePath();
        $destPath = $dest->getAbsolutePath();

        $linkTarget = $src->getLinkTarget();
        if (false === @symlink($linkTarget, $destPath)) {
            $msg = "FileSystem::copy() FAILED. Cannot create symlink from {$destPath} to {$linkTarget}.";

            throw new Exception($msg);
        }
    }

    /**
     * @param string $p
     *
     * @return string
     */
    public function fromURIPath($p)
    {
        if (StringHelper::endsWith('/', $p) && (strlen($p) > 1)) {
            // "/foo/" --> "/foo", but "/" --> "/"
            $p = substr($p, 0, strlen($p) - 1);
        }

        return $p;
    }

    /**
     * Whether file can be deleted.
     *
     * @return bool
     */
    public function canDelete(File $f)
    {
        @clearstatcache();
        $dir = dirname($f->getAbsolutePath());

        return @is_writable($dir);
    }

    /**
     * Normalize the given pathname, whose length is $len, starting at the given
     * $offset; everything before this offset is already normal.
     *
     * @param string $pathname
     * @param int    $len
     * @param int    $offset
     *
     * @return string
     */
    protected function normalizer($pathname, $len, $offset)
    {
        if (0 === $len) {
            return $pathname;
        }
        $n = (int) $len;
        while (($n > 0) && ('/' === $pathname[$n - 1])) {
            --$n;
        }
        if (0 === $n) {
            return '/';
        }
        $sb = '';

        if ($offset > 0) {
            $sb .= substr($pathname, 0, $offset);
        }
        $prevChar = 0;
        for ($i = $offset; $i < $n; ++$i) {
            $c = $pathname[$i];
            if (('/' === $prevChar) && ('/' === $c)) {
                continue;
            }
            $sb .= $c;
            $prevChar = $c;
        }

        return $sb;
    }
}
