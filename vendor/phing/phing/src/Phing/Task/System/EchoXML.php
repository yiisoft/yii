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

namespace Phing\Task\System;

use Phing\Exception\BuildException;
use Phing\Util\XMLFragment;

/**
 * Echos a message to the logging system or to a file.
 *
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class EchoXML extends XMLFragment
{
    private $file;

    private $append = false;

    /**
     * setter for file.
     */
    public function setFile(string $file): void
    {
        $this->file = $file;
    }

    /**
     * setter for append.
     */
    public function setAppend(bool $append): void
    {
        $this->append = $append;
    }

    public function main()
    {
        $n = $this->getFragment()->firstChild;
        if (null === $n) {
            throw new BuildException('No nested XML specified');
        }
        $doc = $this->getDoc();
        $doc->formatOutput = true;
        $xml = $doc->saveXML($n);
        if (false === $xml) {
            throw new BuildException('Error in xml detected');
        }
        if (empty($this->file)) {
            $this->log($xml);
        } else {
            if ($this->append) {
                $handle = fopen($this->file, 'ab');
            } else {
                $handle = fopen($this->file, 'wb');
            }

            fwrite($handle, $xml);

            fclose($handle);
        }
    }
}
