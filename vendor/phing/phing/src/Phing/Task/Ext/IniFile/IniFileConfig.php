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

namespace Phing\Task\Ext\IniFile;

use RuntimeException;

/**
 * Class for reading/writing ini config file
 *
 * This preserves comments etc, unlike parse_ini_file and is based heavily on
 * a solution provided at:
 * stackoverflow.com/questions/9594238/good-php-classes-that-manipulate-ini-files
 *
 * @author   Ken Guest <kguest@php.net>
 */
class IniFileConfig
{
    /**
     * Lines of ini file
     *
     * @var array
     */
    protected $lines = [];

    /**
     * Read ini file
     *
     * @param string $file filename
     */
    public function read(string $file): void
    {
        $this->lines = [];

        $section = '';

        foreach (file($file) as $line) {
            if (preg_match('/^\s*(;.*)?$/', $line)) {
                // comment or whitespace
                $this->lines[] = [
                    'type' => 'comment',
                    'data' => $line,
                    'section' => $section
                ];
            } elseif (preg_match('/^\s?\[(.*)\]/', $line, $match)) {
                // section
                $section = $match[1];
                $this->lines[] = [
                    'type' => 'section',
                    'data' => $line,
                    'section' => $section
                ];
            } elseif (preg_match('/^\s*(.*?)\s*=\s*(.*?)\s*$/', $line, $match)) {
                // entry
                $this->lines[] = [
                    'type' => 'entry',
                    'data' => $line,
                    'section' => $section,
                    'key' => $match[1],
                    'value' => $match[2]
                ];
            }
        }
    }

    /**
     * Get value of given key in specified section
     *
     * @param string $section Section
     * @param string $key Key
     *
     * @return string
     */
    public function get(string $section, string $key): string
    {
        foreach ($this->lines as $line) {
            if ($line['type'] !== 'entry') {
                continue;
            }
            if ($line['section'] !== $section) {
                continue;
            }
            if ($line['key'] !== $key) {
                continue;
            }
            return $line['value'];
        }

        throw new RuntimeException('Missing Section or Key');
    }

    /**
     * Set key to value in specified section
     *
     * @param string $section Section
     * @param string $key Key
     * @param string $value Value
     */
    public function set(string $section, string $key, string $value): void
    {
        foreach ($this->lines as &$line) {
            if ($line['type'] !== 'entry') {
                continue;
            }
            if ($line['section'] !== $section) {
                continue;
            }
            if ($line['key'] !== $key) {
                continue;
            }
            $line['value'] = $value;
            $line['data'] = $key . " = " . $value . PHP_EOL;
            return;
        }

        throw new RuntimeException('Missing Section or Key');
    }

    /**
     * Remove key/section from file.
     *
     * If key is not specified, then the entire section will be removed.
     *
     * @param string $section Section to manipulate/remove
     * @param string|null $key Name of key to remove, might be null/empty
     */
    public function remove(string $section, ?string $key): void
    {
        if ($section === '') {
            throw new RuntimeException("Section not set.");
        }
        if (null === $key || ($key === '')) {
            // remove entire section
            foreach ($this->lines as $linenum => $line) {
                if ($line['section'] === $section) {
                    unset($this->lines[$linenum]);
                }
            }
        } else {
            foreach ($this->lines as $linenum => $line) {
                if (
                    ($line['section'] === $section)
                    && (isset($line['key']))
                    && ($line['key'] === $key)
                ) {
                    unset($this->lines[$linenum]);
                }
            }
        }
    }

    /**
     * Write contents out to file
     *
     * @param string $file filename
     *
     * @return void
     */
    public function write(string $file): void
    {
        if (file_exists($file) && !is_writable($file)) {
            throw new RuntimeException("$file is not writable");
        }
        $fp = fopen($file, 'w');
        foreach ($this->lines as $line) {
            fwrite($fp, $line['data']);
        }
        fclose($fp);
    }
}
