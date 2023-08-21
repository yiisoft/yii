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

namespace Phing\Mapper;

use Phing\Exception\BuildException;
use Phing\Io\FileUtils;

/**
 * A mapper that strips of the a configurable number of leading
 * directories from a file name.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class CutDirsMapper implements FileNameMapper
{
    private $dirs = 0;

    /**
     * Empty implementation.
     *
     * @param mixed $ignore ignored
     */
    public function setFrom($ignore)
    {
    }

    /**
     * The number of leading directories to cut.
     *
     * @param int $dirs
     */
    public function setTo($dirs)
    {
        $this->dirs = (int) $dirs;
    }

    /**
     * {@inheritDoc}.
     */
    public function main($sourceFileName)
    {
        if ($this->dirs <= 0) {
            throw new BuildException('dirs must be set to a positive number');
        }
        $fileSep = FileUtils::getSeparator();
        $fileSepCorrected = str_replace(['/', '\\'], $fileSep, $sourceFileName);
        $nthMatch = strpos($fileSepCorrected, $fileSep);

        for ($n = 1; $nthMatch > -1 && $n < $this->dirs; ++$n) {
            $nthMatch = strpos($fileSepCorrected, $fileSep, $nthMatch + 1);
        }

        if (false === $nthMatch) {
            return null;
        }

        return [substr($sourceFileName, $nthMatch + 1)];
    }
}
