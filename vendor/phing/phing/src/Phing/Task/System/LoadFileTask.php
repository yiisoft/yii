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
use Phing\Io\File;
use Phing\Io\FileReader;
use Phing\Io\FileUtils;
use Phing\Io\IOException;
use Phing\Project;
use Phing\Task;
use Phing\Type\Element\FilterChainAware;

/**
 * LoadFileTask.
 *
 * Loads a (text) file and stores the contents in a property.
 * Supports filterchains.
 *
 * @author  Michiel Rook <mrook@php.net>
 */
class LoadFileTask extends Task
{
    use FilterChainAware;

    /**
     * File to read.
     *
     * @var File file
     */
    private $file;

    /**
     * Property to be set.
     *
     * @var string
     */
    private $property;

    private $failOnError = true;

    private $quiet = false;

    /**
     * @param bool $fail
     */
    public function setFailOnError($fail)
    {
        $this->failOnError = $fail;
    }

    /**
     * @param bool $quiet
     */
    public function setQuiet($quiet)
    {
        $this->quiet = $quiet;
        if ($quiet) {
            $this->failOnError = false;
        }
    }

    /**
     * Set file to read.
     */
    public function setFile(File $file)
    {
        $this->file = $file;
    }

    /**
     * Convenience setter to maintain Ant compatibility (@see setFile()).
     */
    public function setSrcFile(File $srcFile)
    {
        $this->file = $srcFile;
    }

    /**
     * Set name of property to be set.
     *
     * @param string $property
     */
    public function setProperty($property)
    {
        $this->property = $property;
    }

    /**
     * Main method.
     *
     * @throws BuildException
     */
    public function main()
    {
        if (empty($this->file)) {
            throw new BuildException("Attribute 'file' required", $this->getLocation());
        }

        if (empty($this->property)) {
            throw new BuildException("Attribute 'property' required", $this->getLocation());
        }
        if ($this->quiet && $this->failOnError) {
            throw new BuildException('quiet and failonerror cannot both be set to true');
        }

        try {
            if (is_string($this->file)) {
                $this->file = new File($this->file);
            }
            if (!$this->file->exists()) {
                $message = (string) $this->file . ' doesn\'t exist';
                if ($this->failOnError) {
                    throw new BuildException($message);
                }

                $this->log($message, $this->quiet ? Project::MSG_WARN : Project::MSG_ERR);

                return;
            }

            $this->log('loading ' . (string) $this->file . ' into property ' . $this->property, Project::MSG_VERBOSE);
            // read file (through filterchains)
            $contents = '';

            if ($this->file->length() > 0) {
                $reader = FileUtils::getChainedReader(new FileReader($this->file), $this->filterChains, $this->project);
                while (-1 !== ($buffer = $reader->read())) {
                    $contents .= $buffer;
                }
                $reader->close();
            } else {
                $this->log(
                    'Do not set property ' . $this->property . ' as its length is 0.',
                    $this->quiet ? Project::MSG_VERBOSE : Project::MSG_INFO
                );
            }

            // publish as property
            if ('' !== $contents) {
                $this->project->setNewProperty($this->property, $contents);
                $this->log('loaded ' . strlen($contents) . ' characters', Project::MSG_VERBOSE);
                $this->log($this->property . ' := ' . $contents, Project::MSG_DEBUG);
            }
        } catch (IOException $ioe) {
            $message = 'Unable to load resource: ' . $ioe->getMessage();
            if ($this->failOnError) {
                throw new BuildException($message, $ioe, $this->getLocation());
            }

            $this->log($message, $this->quiet ? Project::MSG_VERBOSE : Project::MSG_ERR);
        } catch (BuildException $be) {
            if ($this->failOnError) {
                throw $be;
            }

            $this->log($be->getMessage(), $this->quiet ? Project::MSG_VERBOSE : Project::MSG_ERR);
        }
    }
}
