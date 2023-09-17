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

namespace Phing\Type\Selector\Modified;

use Phing\Exception\BuildException;
use Phing\Io\File;

/**
 * Class ChecksumAlgorithm.
 */
class HashfileAlgorithm implements Algorithm
{
    /**
     * Checksum algorithm to be used.
     */
    private $algorithm = 'md5';

    /**
     * @return string some information about this algorithm
     */
    public function __toString(): string
    {
        return sprintf('<%s:algorithm=%s>', __CLASS__, $this->algorithm);
    }

    /**
     * Specifies the algorithm to be used to compute the checksum.
     * Defaults to "CRC". Other popular algorithms like "ADLER" may be used as well.
     *
     * @param string $algorithm the digest algorithm to use
     */
    public function setAlgorithm(string $algorithm): void
    {
        $this->algorithm = strtolower($algorithm);
    }

    /**
     * This algorithm supports `hash_algos()`.
     *
     * @return bool <i>true</i> if all is ok, otherwise <i>false</i>
     */
    public function isValid(): bool
    {
        return in_array($this->algorithm, hash_algos(), true);
    }

    /**
     * Computes a value for a file content with the specified checksum algorithm.
     *
     * @param File $file file object for which the value should be evaluated
     *
     * @return null|string The value for that file
     */
    public function getValue(File $file): ?string
    {
        if (!$this->isValid()) {
            throw new BuildException('Wrong hash algorithm.');
        }

        if ($file->canRead()) {
            return hash_file($this->algorithm, $file->getAbsolutePath());
        }

        return null;
    }
}
