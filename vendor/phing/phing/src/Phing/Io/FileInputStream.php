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

/**
 * Input stream subclass for file streams.
 */
class FileInputStream extends InputStream
{
    /**
     * The associated file.
     *
     * @var File
     */
    protected $file;

    /**
     * Construct a new FileInputStream.
     *
     * @param File|string $file Path to the file
     *
     * @throws Exception   - if invalid argument specified
     * @throws IOException - if unable to open file
     */
    public function __construct($file)
    {
        if ($file instanceof File) {
            $this->file = $file;
        } elseif (is_string($file)) {
            $this->file = new File($file);
        } else {
            throw new Exception('Invalid argument type for $file.');
        }

        if (!$this->file->exists()) {
            throw new IOException('Unable to open ' . $this->file->__toString() . ' for reading. File does not exists.');
        }
        if (!$this->file->canRead()) {
            throw new IOException('Unable to open ' . $this->file->__toString() . ' for reading. File not readable.');
        }
        $stream = @fopen($this->file->getAbsolutePath(), 'rb');
        if (false === $stream) {
            throw new IOException('Unable to open ' . $this->file->__toString() . ' for reading: ' . print_r(
                error_get_last(),
                true
            ));
        }

        parent::__construct($stream);
    }

    /**
     * Returns a string representation of the attached file.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->file->getPath();
    }

    /**
     * Mark is supported by FileInputStream.
     *
     * @return bool TRUE
     */
    public function markSupported()
    {
        return true;
    }
}
