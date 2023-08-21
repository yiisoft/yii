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

use Phing\Util\Properties;
use SimpleXMLElement;

/**
 * Implements an XmlFileParser.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class XmlFileParser implements FileParserInterface
{
    private $keepRoot = true;
    private $collapseAttr = true;
    private $delimiter = ',';

    public function setKeepRoot(bool $keepRoot): void
    {
        $this->keepRoot = $keepRoot;
    }

    public function setCollapseAttr(bool $collapseAttr): void
    {
        $this->collapseAttr = $collapseAttr;
    }

    public function setDelimiter(string $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    /**
     * {@inheritDoc}
     */
    public function parseFile(File $file)
    {
        $properties = $this->getProperties($file);

        return $properties->getProperties();
    }

    /**
     * Parses an XML file and returns properties.
     *
     * @throws IOException
     *
     * @return Properties
     */
    private function getProperties(File $file)
    {
        // load() already made sure that file is readable
        // but we'll double check that when reading the file into
        // an array

        if ((@file($file)) === false) {
            throw new IOException("Unable to parse contents of {$file}");
        }

        $prop = new Properties();

        $xml = simplexml_load_string(file_get_contents($file));

        if (false === $xml) {
            throw new IOException("Unable to parse XML file {$file}");
        }

        $path = [];

        if ($this->keepRoot) {
            $path[] = dom_import_simplexml($xml)->tagName;

            $prefix = implode('.', $path);

            if (strlen($prefix) > 0) {
                $prefix .= '.';
            }

            // Check for attributes
            foreach ($xml->attributes() as $attribute => $val) {
                if ($this->collapseAttr) {
                    $prop->setProperty($prefix . (string) $attribute, (string) $val);
                } else {
                    $prop->setProperty($prefix . "({$attribute})", (string) $val);
                }
            }
        }

        $this->addNode($xml, $path, $prop);

        return $prop;
    }

    /**
     * Adds an XML node.
     *
     * @param SimpleXMLElement $node
     * @param array            $path Path to this node
     * @param Properties       $prop Properties will be added as they are found (by reference here)
     */
    private function addNode($node, $path, $prop)
    {
        foreach ($node as $tag => $value) {
            $prefix = implode('.', $path);

            if ('' !== $prefix) {
                $prefix .= '.';
            }

            // Check for attributes
            foreach ($value->attributes() as $attribute => $val) {
                if ($this->collapseAttr) {
                    $prop->setProperty($prefix . "{$tag}.{$attribute}", (string) $val);
                } else {
                    $prop->setProperty($prefix . "{$tag}({$attribute})", (string) $val);
                }
            }

            // Add tag
            if (count($value->children())) {
                $this->addNode($value, array_merge($path, [$tag]), $prop);
            } else {
                $val = (string) $value;

                /* Check for * and ** on 'exclude' and 'include' tag / ant seems to do this? could use FileSet here
                if ($tag == 'exclude') {
                }*/

                // When property already exists, i.e. multiple xml tag
                // <project>
                //    <exclude>file/a.php</exclude>
                //    <exclude>file/a.php</exclude>
                // </project>
                //
                // Would be come project.exclude = file/a.php,file/a.php
                $p = empty($prefix) ? $tag : $prefix . $tag;
                $prop->append($p, (string) $val, $this->delimiter);
            }
        }
    }
}
