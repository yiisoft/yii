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

/**
 * Implements an IniFileParser. The logic is coming from th Properties.php, but I don't know who's the author.
 *
 * FIXME
 *  - Add support for arrays (separated by ',')
 *
 * @author  Mike Lohmann <mike.lohmann@deck36.de>
 */
class IniFileParser implements FileParserInterface
{
    /**
     * {@inheritDoc}
     */
    public function parseFile(File $file)
    {
        if (($lines = @file($file, FILE_IGNORE_NEW_LINES)) === false) {
            throw new IOException("Unable to parse contents of {$file}");
        }

        // concatenate lines ending with backslash
        $linesCount = count($lines);
        for ($i = 0; $i < $linesCount; ++$i) {
            if ('\\' === substr((string) $lines[$i], -1, 1)) {
                $lines[$i + 1] = substr((string) $lines[$i], 0, -1) . ltrim($lines[$i + 1]);
                $lines[$i] = '';
            }
        }

        $properties = [];
        foreach ($lines as $line) {
            // strip comments and leading/trailing spaces
            $line = trim(preg_replace('/\\s+[;#]\\s.+$/', '', $line));

            if (empty($line) || ';' == $line[0] || '#' == $line[0]) {
                continue;
            }

            $pos = strpos((string) $line, '=');
            if (false === $pos) {
                continue;
            }
            $property = trim(substr((string) $line, 0, $pos));
            $value = trim(substr((string) $line, $pos + 1));
            $properties[$property] = $value;
        } // for each line

        return $properties;
    }
}
