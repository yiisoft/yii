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

namespace Phing\Parser;

/**
 * Stores the file name and line number of a XML file.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 */
class Location
{
    /**
     * @var null|string
     */
    private $fileName;

    /**
     * @var null|int
     */
    private $lineNumber;

    /**
     * @var null|int
     */
    private $columnNumber;

    /**
     * Constructs the location consisting of a file name and line number.
     *
     * @param string $fileName     the filename
     * @param int    $lineNumber   the line number
     * @param int    $columnNumber the column number
     */
    public function __construct($fileName = null, $lineNumber = null, $columnNumber = null)
    {
        $this->fileName = $fileName;
        $this->lineNumber = $lineNumber;
        $this->columnNumber = $columnNumber;
    }

    /**
     * Returns the file name, line number and a trailing space.
     *
     * An error message can be appended easily. For unknown locations,
     * returns empty string.
     *
     * @return string the string representation of this Location object
     */
    public function __toString(): string
    {
        $buf = '';
        if (null !== $this->getFileName()) {
            $buf .= $this->getFileName();
            if (null !== $this->getLineNumber()) {
                $buf .= ':' . $this->getLineNumber();
            }
            $buf .= ':' . $this->getColumnNumber();
        }

        return $buf;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function getLineNumber(): ?int
    {
        return $this->lineNumber;
    }

    public function getColumnNumber(): ?int
    {
        return $this->columnNumber;
    }
}
