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

namespace Phing\Task\Ext\Analyzer\Sonar;

use Phing\Exception\BuildException;
use Phing\Project;

/**
 *
 * @author Bernhard Mendl <mail@bernhard-mendl.de>
 * @package phing.tasks.ext.sonar
 */
class SonarConfigurationFileParser
{
    /**
     *
     * @var Project
     */
    private $project;

    /**
     *
     * @var string
     */
    private $file;

    /**
     * This map holds the properties read from the configuration file.
     *
     * @var array
     */
    private $properties;

    /**
     * Name of currently parsed property.
     *
     * @var string|null
     */
    private $name;

    /**
     * Value of currently parsed property.
     *
     * @var string
     */
    private $value;

    /**
     *
     * @param string $file
     *            The properties file.
     */
    public function __construct($file, Project $project)
    {
        if (($file === null) || ($file === '')) {
            throw new BuildException('File name must not be null or empty.');
        }

        $this->file = $file;
        $this->project = $project;
    }

    /**
     *
     * @throws BuildException
     * @return array
     */
    public function parse()
    {
        $this->properties = [];

        $contents = @file_get_contents($this->file);

        if ($contents === false) {
            $message = sprintf('Could not read file [%s].', $this->file);
            throw new BuildException($message);
        }

        $lines = preg_split("/\r?\n/", $contents);
        $count = count($lines);
        $isMultiLine = false;
        for ($i = 0; $i < $count; $i++) {
            $line = $lines[$i];

            if ($isMultiLine) {
                $isMultiLine = $this->extractContinuedValue($line);
            } else {
                $this->name = null;
                $this->value = '';

                $isMultiLine = $this->extractNameAndValue($line);
            }

            if (($this->name !== null) && (! $isMultiLine)) {
                if (array_key_exists($this->name, $this->properties)) {
                    $message = sprintf(
                        'Property [%s] overwritten: old value [%s], new value [%s].',
                        $this->name,
                        $this->properties[$this->name],
                        $this->value
                    );
                    $this->project->log($message, Project::MSG_WARN);
                }

                // Unescape backslashes.
                $this->value = str_replace('\\\\', '\\', $this->value);

                $this->properties[$this->name] = $this->value;
            }
        }

        if ($isMultiLine) {
            $message = sprintf('Last property looks like a multi-lined value, but end of file found. Name = [%s].', $this->name);
            throw new BuildException($message);
        }

        return $this->properties;
    }

    /**
     *
     * @param string $line
     * @return boolean
     */
    private function extractNameAndValue($line)
    {
        $isMultiLine = false;

        if ($this->isCommentLine($line)) {
            return $isMultiLine;
        }

        // Find key and value.
        $hasMatch = preg_match('/\\s*([^=:]*[^=:\\s]+)\\s*[=:]\\s*(.*)$/s', $line, $matches);

        if (($hasMatch === 1) && (count($matches) === 3)) {
            $this->name = $matches[1];
            $this->value = $matches[2];

            $isMultiLine = $this->checkMultiLine();
        }

        return $isMultiLine;
    }

    /**
     *
     * @param string $line
     * @return boolean
     */
    private function extractContinuedValue($line)
    {
        $isMultiLine = false;

        if ($this->isCommentLine($line)) {
            return $isMultiLine;
        }

        // Find continued value.
        $hasMatch = preg_match('/\\s*(.*)$/s', $line, $matches);

        if (($hasMatch === 1) && (count($matches) === 2)) {
            $this->value .= $matches[1];

            $isMultiLine = $this->checkMultiLine();
        }

        return $isMultiLine;
    }

    /**
     *
     * @return boolean
     */
    private function checkMultiLine()
    {
        $isMultiLine = false;

        // Is there a single(!) backslash at the end of the line?
        if (preg_match('/[^\\\]\\\$/', $this->value) === 1) {
            // Remove last char, i.e. the backslash.
            $this->value = substr($this->value, 0, -1);
            $isMultiLine = true;
        }

        return $isMultiLine;
    }

    /**
     *
     * @param string $line
     * @return boolean
     */
    private function isCommentLine($line)
    {
        return preg_match('/^\\s*[!#]/', $line) === 1;
    }
}
