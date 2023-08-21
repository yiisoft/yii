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

namespace Phing\Task\Ext\ApiGen;

use Phing\Exception\BuildException;
use Phing\Project;
use Phing\Task;

/**
 * ApiGen task (http://apigen.org).
 *
 * @package phing.tasks.ext.apigen
 * @author  Martin Srank <martin@smasty.net>
 * @author  Jaroslav Hanslík <kukulich@kukulich.cz>
 * @author  Lukáš Homza <lukashomza@gmail.com>
 */
class ApiGenTask extends Task
{
    /**
     * Default ApiGen executable name.
     *
     * @var string
     */
    private $executable = 'apigen';

    /**
     * Default ApiGen action.
     *
     * @var string
     */
    private $action = 'generate';

    /**
     * Default ApiGen options.
     *
     * @var array
     */
    private $options = [];

    /**
     * Sets the ApiGen executable name.
     *
     * @param string $executable
     */
    public function setExecutable(string $executable): void
    {
        $this->executable = $executable;
    }

    /**
     * Sets the ApiGen action to be executed.
     *
     * @param string $action
     */
    public function setAction(string $action): void
    {
        $this->action = $action;
    }

    /**
     * Sets the config file name.
     *
     * @param string $config
     */
    public function setConfig(string $config): void
    {
        $this->options['config'] = $config;
    }

    /**
     * Sets source files or directories.
     *
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->options['source'] = explode(',', $source);
    }

    /**
     * Sets the destination directory.
     *
     * @param string $destination
     */
    public function setDestination(string $destination): void
    {
        $this->options['destination'] = $destination;
    }

    /**
     * Sets list of allowed file extensions.
     *
     * @param string $extensions
     */
    public function setExtensions(string $extensions): void
    {
        $this->options['extensions'] = explode(',', $extensions);
    }

    /**
     * Sets masks (case sensitive) to exclude files or directories from processing.
     *
     * @param string $exclude
     */
    public function setExclude(string $exclude): void
    {
        $this->options['exclude'] = explode(',', $exclude);
    }

    /**
     * Sets masks to exclude elements from documentation generating.
     *
     * @param string $skipDocPath
     */
    public function setSkipDocPath(string $skipDocPath): void
    {
        $this->options['skip-doc-path'] = explode(',', $skipDocPath);
    }

    /**
     * Sets the character set of source files.
     *
     * @param string $charset
     */
    public function setCharset(string $charset): void
    {
        $this->options['charset'] = explode(',', $charset);
    }

    /**
     * Sets the main project name prefix.
     *
     * @param string $main
     */
    public function setMain(string $main): void
    {
        $this->options['main'] = $main;
    }

    /**
     * Sets the title of generated documentation.
     *
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->options['title'] = $title;
    }

    /**
     * Sets the documentation base URL.
     *
     * @param string $baseUrl
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->options['base-url'] = $baseUrl;
    }

    /**
     * Sets the Google Custom Search ID.
     *
     * @param string $googleCseId
     */
    public function setGoogleCseId(string $googleCseId): void
    {
        $this->options['google-cse-id'] = $googleCseId;
    }

    /**
     * Sets the Google Custom Search label.
     *
     * @param string $googleCseLabel
     */
    public function setGoogleCseLabel(string $googleCseLabel): void
    {
        $this->options['google-cse-label'] = $googleCseLabel;
    }

    /**
     * Sets the Google Analytics tracking code.
     *
     * @param string $googleAnalytics
     */
    public function setGoogleAnalytics(string $googleAnalytics): void
    {
        $this->options['google-analytics'] = $googleAnalytics;
    }

    /**
     * Sets the template config file name.
     *
     * @param string $templateConfig
     */
    public function setTemplateConfig(string $templateConfig): void
    {
        $this->options['template-config'] = $templateConfig;
    }

    /**
     * Sets the template config file name.
     *
     * @param string $templateTheme
     */
    public function setTemplateTheme(string $templateTheme): void
    {
        $this->options['template-theme'] = $templateTheme;
    }

    /**
     * Sets how elements should be grouped in the menu.
     *
     * @param string $groups
     */
    public function setGroups(string $groups): void
    {
        $this->options['groups'] = $groups;
    }

    /**
     * Sets the element access levels.
     *
     * Documentation only for methods and properties with the given access level will be generated.
     *
     * @param string $accessLevels
     */
    public function setAccessLevels(string $accessLevels): void
    {
        $this->options['access-levels'] = $accessLevels;
    }

    /**
     * Sets the element access levels.
     *
     * Documentation only for methods and properties with the given access level will be generated.
     *
     * @param string $annotationGroups
     */
    public function setAnnotationGroups(string $annotationGroups): void
    {
        $this->options['annotation-groups'] = $annotationGroups;
    }

    /**
     * Sets if documentation for elements marked as internal and internal documentation parts should be generated.
     *
     * @param boolean $internal
     */
    public function setInternal(bool $internal): void
    {
        if ($internal) {
            $this->options['internal'] = null;
        }
    }

    /**
     * Sets if documentation for PHP internal classes should be generated.
     *
     * @param boolean $php
     */
    public function setPhp(bool $php): void
    {
        if ($php) {
            $this->options['php'] = null;
        }
    }

    /**
     * Sets if tree view of classes, interfaces, traits and exceptions should be generated.
     *
     * @param boolean $tree
     */
    public function setTree(bool $tree): void
    {
        if ($tree) {
            $this->options['tree'] = null;
        }
    }

    /**
     * Sets if documentation for deprecated elements should be generated.
     *
     * @param boolean $deprecated
     */
    public function setDeprecated(bool $deprecated): void
    {
        if ($deprecated) {
            $this->options['deprecated'] = null;
        }
    }

    /**
     * Sets if documentation of tasks should be generated.
     *
     * @param boolean $todo
     */
    public function setTodo(bool $todo): void
    {
        if ($todo) {
            $this->options['todo'] = null;
        }
    }

    /**
     * Sets if highlighted source code files should not be generated.
     *
     * @param boolean $noSourceCode
     * @deprecated
     */
    public function setNoSourceCode(bool $noSourceCode): void
    {
        $this->setSourceCode(!$noSourceCode);
    }

    /**
     * Sets if highlighted source code files should be generated.
     *
     * @param boolean $noSourceCode
     */
    public function setSourceCode(bool $noSourceCode): void
    {
        if (!$noSourceCode) {
            $this->options['no-source-code'] = null;
        }
    }

    /**
     * Sets if a link to download documentation as a ZIP archive should be generated.
     *
     * @param boolean $download
     */
    public function setDownload(bool $download): void
    {
        if ($download) {
            $this->options['download'] = null;
        }
    }

    /**
     * Enables/disables the debug mode.
     *
     * @param boolean $debug
     */
    public function setDebug(bool $debug): void
    {
        if ($debug) {
            $this->options['debug'] = null;
        }
    }

    /**
     * Runs ApiGen.
     *
     * @throws BuildException If something is wrong.
     * @see    Task::main()
     */
    public function main()
    {
        if ('apigen' !== $this->executable && !is_file($this->executable)) {
            throw new BuildException(sprintf('Executable %s not found', $this->executable), $this->getLocation());
        }

        if (!empty($this->options['config'])) {
            // Config check
            if (!is_file($this->options['config'])) {
                throw new BuildException(
                    sprintf(
                        'Config file %s doesn\'t exist',
                        $this->options['config']
                    ),
                    $this->getLocation()
                );
            }
        } else {
            // Source check
            if (empty($this->options['source'])) {
                throw new BuildException('Source is not set', $this->getLocation());
            }
            // Destination check
            if (empty($this->options['destination'])) {
                throw new BuildException('Destination is not set', $this->getLocation());
            }
        }

        // Source check
        if (!empty($this->options['source'])) {
            foreach ($this->options['source'] as $source) {
                if (!file_exists($source)) {
                    throw new BuildException(sprintf('Source %s doesn\'t exist', $source), $this->getLocation());
                }
            }
        }

        // Execute ApiGen
        exec(
            escapeshellcmd($this->executable) . ' ApiGenTask.php' . escapeshellcmd($this->action) . ' ' . $this->constructArguments(),
            $output,
            $return
        );

        $logType = 0 === $return ? Project::MSG_INFO : Project::MSG_ERR;
        foreach ($output as $line) {
            $this->log($line, $logType);
        }
    }

    /**
     * Generates command line arguments for the ApiGen executable.
     *
     * @return string
     */
    protected function constructArguments(): string
    {
        $args = [];
        foreach ($this->options as $option => $value) {
            if (is_bool($value)) {
                $args[] = '--' . $option . '=' . ($value ? 'yes' : 'no');
            } elseif (is_array($value)) {
                foreach ($value as $v) {
                    $args[] = '--' . $option . '=' . escapeshellarg($v);
                }
            } else {
                $args[] = '--' . $option . '=' . escapeshellarg($value);
            }
        }

        return implode(' ', $args);
    }
}
