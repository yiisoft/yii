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

namespace Phing\Task\System\Append;

use Phing\Exception\BuildException;
use Phing\Io\BufferedReader;
use Phing\Io\File;
use Phing\Io\FileInputStream;
use Phing\Io\FileReader;
use Phing\Io\InputStreamReader;
use Phing\Io\IOException;
use Phing\ProjectComponent;

/**
 * Text element points to a file or contains text.
 */
class TextElement extends ProjectComponent
{
    public $value = '';
    public $trimLeading = false;
    public $trim = false;
    public $filtering = true;
    public $encoding;

    /**
     * whether to filter the text in this element
     * or not.
     */
    public function setFiltering(bool $filtering)
    {
        $this->filtering = $filtering;
    }

    /**
     * The encoding of the text element.
     *
     * @param string $encoding the name of the charset used to encode
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /**
     * set the text using a file.
     *
     * @param File $file the file to use
     *
     * @throws BuildException if the file does not exist, or cannot be
     *                        read
     */
    public function setFile(File $file)
    {
        // non-existing files are not allowed
        if (!$file->exists()) {
            throw new BuildException('File ' . $file . ' does not exist.');
        }

        $reader = null;

        try {
            if (null == $this->encoding) {
                $reader = new BufferedReader(new FileReader($file));
            } else {
                $reader = new BufferedReader(
                    new InputStreamReader(new FileInputStream($file))
                );
            }
            $this->value = $reader->read();
        } catch (IOException $ex) {
            $reader->close();

            throw new BuildException($ex);
        }
        $reader->close();
    }

    /**
     * set the text using inline.
     *
     * @param string $value the text to place inline
     */
    public function addText($value)
    {
        $this->value .= $this->getProject()->replaceProperties($value);
    }

    /**
     * s:^\s*:: on each line of input.
     *
     * @param bool $trimLeading if true do the trim
     */
    public function setTrimLeading($trimLeading)
    {
        $this->trimLeading = $trimLeading;
    }

    /**
     * whether to call text.trim().
     *
     * @param bool $trim if true trim the text
     */
    public function setTrim($trim)
    {
        $this->trim = $trim;
    }

    /**
     * @return string the text, after possible trimming
     */
    public function getValue()
    {
        if (null == $this->value) {
            $this->value = '';
        }
        if ('' === trim($this->value)) {
            $this->value = '';
        }
        if ($this->trimLeading) {
            $current = str_split($this->value);
            $b = '';
            $startOfLine = true;
            $pos = 0;
            while ($pos < count($current)) {
                $ch = $current[$pos++];
                if ($startOfLine) {
                    if (' ' == $ch || "\t" == $ch) {
                        continue;
                    }
                    $startOfLine = false;
                }
                $b .= $ch;
                if ("\n" == $ch || "\r" == $ch) {
                    $startOfLine = true;
                }
            }
            $this->value = $b;
        }
        if ($this->trim) {
            $this->value = trim($this->value);
        }

        return $this->value;
    }
}
