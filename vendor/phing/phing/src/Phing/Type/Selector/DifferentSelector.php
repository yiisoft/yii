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

namespace Phing\Type\Selector;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileUtils;
use Phing\Io\IOException;

/**
 * This selector selects files against a mapped set of target files, selecting
 * all those files which are different.
 * Files with different lengths are deemed different
 * automatically
 * Files with identical timestamps are viewed as matching by
 * default, unless you specify otherwise.
 * Contents are compared if the lengths are the same
 * and the timestamps are ignored or the same,
 * except if you decide to ignore contents to gain speed.
 * <p>
 * This is a useful selector to work with programs and tasks that don't handle
 * dependency checking properly; Even if a predecessor task always creates its
 * output files, followup tasks can be driven off copies made with a different
 * selector, so their dependencies are driven on the absolute state of the
 * files, not a timestamp.
 * <p>
 * Clearly, however, bulk file comparisons is inefficient; anything that can
 * use timestamps is to be preferred. If this selector must be used, use it
 * over as few files as possible, perhaps following it with an &lt;uptodate;&gt
 * to keep the descendant routines conditional.
 */
class DifferentSelector extends MappingSelector
{
    /**
     * @var bool
     */
    private $ignoreFileTimes = true;

    /**
     * @var bool
     */
    private $ignoreContents = false;

    /**
     * This flag tells the selector to ignore file times in the comparison.
     *
     * @param bool $ignoreFileTimes if true ignore file times
     */
    public function setIgnoreFileTimes($ignoreFileTimes)
    {
        $this->ignoreFileTimes = $ignoreFileTimes;
    }

    /**
     * This flag tells the selector to ignore contents.
     *
     * @param bool $ignoreContents if true ignore contents
     */
    public function setIgnoreContents($ignoreContents)
    {
        $this->ignoreContents = $ignoreContents;
    }

    /**
     * This test is our selection test that compared the file with the destfile.
     *
     * @param File $srcfile  the source file
     * @param File $destfile the destination file
     *
     * @throws BuildException
     *
     * @return bool true if the files are different
     */
    protected function selectionTest(File $srcfile, File $destfile)
    {
        try {
            // if either of them is missing, they are different
            if ($srcfile->exists() !== $destfile->exists()) {
                return true;
            }

            if ($srcfile->length() !== $destfile->length()) {
                // different size => different files
                return true;
            }

            if (!$this->ignoreFileTimes) {
                // different dates => different files
                if ($destfile->lastModified() !== $srcfile->lastModified()) {
                    return true;
                }
            }

            if (!$this->ignoreContents) {
                //here do a bulk comparison
                $fu = new FileUtils();

                return !$fu->contentEquals($srcfile, $destfile);
            }
        } catch (IOException $e) {
            throw new BuildException("while comparing {$srcfile} and {$destfile}", $e);
        }

        return false;
    }
}
