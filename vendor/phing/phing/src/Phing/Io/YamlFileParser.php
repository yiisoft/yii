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
use Phing\Exception\BuildException;
use Symfony\Component\Yaml\Parser;

/**
 * Implements a YamlFileParser to parse yaml-files as array.
 *
 * @author  Mike Lohmann <mike.lohmann@deck36.de>
 */
class YamlFileParser implements FileParserInterface
{
    /**
     * {@inheritDoc}
     */
    public function parseFile(File $file)
    {
        if (!$file->canRead()) {
            throw new IOException('Unable to read file: ' . $file);
        }

        try {
            if (!class_exists('\Symfony\Component\Yaml\Parser')) {
                throw new BuildException(
                    get_class($this)
                    . ' depends on \Symfony\Component\Yaml\Parser '
                    . 'being installed and on include_path.'
                );
            }

            $parser = new Parser();
            // Cast properties to array in case parse() returns null.
            $properties = (array) $parser->parse(file_get_contents($file->getAbsolutePath()));
        } catch (Exception $e) {
            if (is_a($e, '\Symfony\Component\Yaml\Exception\ParseException')) {
                throw new IOException('Unable to parse contents of ' . $file . ': ' . $e->getMessage());
            }

            throw $e;
        }

        $flattenedProperties = $this->flattenArray($properties);
        foreach ($flattenedProperties as $key => $flattenedProperty) {
            if (is_array($flattenedProperty)) {
                $flattenedProperties[$key] = implode(',', $flattenedProperty);
            }
        }

        return $flattenedProperties;
    }

    /**
     * Flattens an array to key => value.
     *
     * @todo: milo - 20142901 - If you plan to extend phing and add a new fileparser, please move this to an abstract
     * class.
     *
     * @param mixed $separator
     * @param mixed $flattenedKey
     */
    private function flattenArray(array $arrayToFlatten, $separator = '.', $flattenedKey = '')
    {
        $flattenedArray = [];
        foreach ($arrayToFlatten as $key => $value) {
            $tmpFlattendKey = (!empty($flattenedKey) ? $flattenedKey . $separator : '') . $key;
            // only append next value if is array and is an associative array
            if (is_array($value) && array_keys($value) !== range(0, count($value) - 1)) {
                $flattenedArray = array_merge(
                    $flattenedArray,
                    $this->flattenArray(
                        $value,
                        $separator,
                        $tmpFlattendKey
                    )
                );
            } else {
                $flattenedArray[$tmpFlattendKey] = $value;
            }
        }

        return $flattenedArray;
    }
}
