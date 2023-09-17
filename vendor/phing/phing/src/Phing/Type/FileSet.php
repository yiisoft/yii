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

namespace Phing\Type;

use Exception;

/**
 * Moved out of MatchingTask to make it a standalone object that could
 * be referenced (by scripts for example).
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Arnout J. Kuiper <ajkuiper@wxs.nl> (Ant)
 * @author  Stefano Mazzocchi <stefano@apache.org> (Ant)
 * @author  Sam Ruby <rubys@us.ibm.com> (Ant)
 * @author  Jon S. Stevens <jon@clearink.com> (Ant)
 * @author  Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 * @author  Magesh Umasankar (Ant)
 */
class FileSet extends AbstractFileSet
{
    /**
     * @throws Exception
     *
     * @return array
     */
    protected function getFiles(...$options)
    {
        $directoryScanner = $this->getDirectoryScanner($this->getProject());
        $files = $directoryScanner->getIncludedFiles();

        $baseDirectory = $directoryScanner->getBasedir();
        foreach ($files as $index => $file) {
            $files[$index] = realpath($baseDirectory . '/' . $file);
        }

        return array_filter($files);
    }
}
