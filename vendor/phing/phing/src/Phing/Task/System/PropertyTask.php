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

use Exception;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileParserFactory;
use Phing\Io\FileParserFactoryInterface;
use Phing\Io\FileReader;
use Phing\Io\FileUtils;
use Phing\Io\IOException;
use Phing\Io\StringReader;
use Phing\Project;
use Phing\PropertyHelper;
use Phing\Task;
use Phing\Type\Element\FilterChainAware;
use Phing\Type\Reference;
use Phing\Util\Properties;
use Phing\Util\StringHelper;

/**
 * Task for setting properties in buildfiles.
 *
 * @author  Andreas Aderhold <andi@binarycloud.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class PropertyTask extends Task
{
    use FilterChainAware;

    /**
     * @var string name of the property
     */
    protected $name;

    /**
     * @var string of the property
     */
    protected $value;

    /**
     * @var Reference
     */
    protected $reference;

    /**
     * @var string environment
     */
    protected $env;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var Project
     */
    protected $fallback;

    /**
     * Whether to force overwrite of existing property.
     */
    protected $override = false;

    /**
     * Whether property should be treated as "user" property.
     */
    protected $userProperty = false;

    /**
     * Whether to log messages as INFO or VERBOSE.
     */
    protected $logOutput = true;

    /**
     * @var FileParserFactoryInterface
     */
    private $fileParserFactory;

    /**
     * Whether a warning should be displayed when the property ismissing.
     */
    private $quiet = false;

    /**
     * Whether the task should fail when the property file is not found.
     */
    private $required = false;

    /**
     * @param FileParserFactoryInterface $fileParserFactory
     */
    public function __construct(FileParserFactoryInterface $fileParserFactory = null)
    {
        parent::__construct();
        $this->fileParserFactory = $fileParserFactory ?? new FileParserFactory();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * File required or not.
     *
     * @param string $d
     */
    public function setRequired($d)
    {
        $this->required = $d;
    }

    /**
     * @return string
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Sets a the name of current property component.
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get property component name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets a the value of current property component.
     *
     * @param string $value Value of name, all scalars allowed
     */
    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    /**
     * Sets value of property to CDATA tag contents.
     *
     * @since    2.2.0
     */
    public function addText(string $value): void
    {
        $this->setValue($value);
    }

    /**
     * Get the value of current property component.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set a file to use as the source for properties.
     *
     * @param File|string $file
     *
     * @throws IOException
     */
    public function setFile($file)
    {
        if (is_string($file)) {
            $file = new File($file);
        }
        $this->file = $file;
    }

    /**
     * Get the PhingFile that is being used as property source.
     */
    public function getFile()
    {
        return $this->file;
    }

    public function setRefid(Reference $ref): void
    {
        $this->reference = $ref;
    }

    public function getRefid()
    {
        return $this->reference;
    }

    /**
     * Prefix to apply to properties loaded using <code>file</code>.
     * A "." is appended to the prefix if not specified.
     *
     * @param string $prefix prefix string
     *
     * @since  2.0
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
        if (!StringHelper::endsWith('.', $prefix)) {
            $this->prefix .= '.';
        }
    }

    /**
     * @return string
     *
     * @since 2.0
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * the prefix to use when retrieving environment variables.
     * Thus if you specify environment="myenv"
     * you will be able to access OS-specific
     * environment variables via property names "myenv.PATH" or
     * "myenv.TERM".
     * <p>
     * Note that if you supply a property name with a final
     * "." it will not be doubled. ie environment="myenv." will still
     * allow access of environment variables through "myenv.PATH" and
     * "myenv.TERM". This functionality is currently only implemented
     * on select platforms. Feel free to send patches to increase the number of platforms
     * this functionality is supported on ;).<br>
     * Note also that properties are case sensitive, even if the
     * environment variables on your operating system are not, e.g. it
     * will be ${env.Path} not ${env.PATH} on Windows 2000.
     */
    public function setEnvironment(string $env): void
    {
        $this->env = $env;
    }

    public function getEnvironment()
    {
        return $this->env;
    }

    /**
     * Set whether this is a user property (ro).
     * This is deprecated in Ant 1.5, but the userProperty attribute
     * of the class is still being set via constructor, so Phing will
     * allow this method to function.
     */
    public function setUserProperty(bool $v): void
    {
        $this->userProperty = $v;
    }

    /**
     * @return bool
     */
    public function getUserProperty()
    {
        return $this->userProperty;
    }

    public function setOverride(bool $override): void
    {
        $this->override = $override;
    }

    /**
     * @return bool
     */
    public function getOverride()
    {
        return $this->override;
    }

    /**
     * @param Project $p
     */
    public function setFallback($p): void
    {
        $this->fallback = $p;
    }

    public function getFallback()
    {
        return $this->fallback;
    }

    public function setLogoutput(bool $logOutput): void
    {
        $this->logOutput = $logOutput;
    }

    /**
     * @return bool
     */
    public function getLogoutput()
    {
        return $this->logOutput;
    }

    /**
     * Set quiet mode, which suppresses warnings if chmod() fails.
     *
     * @see   setFailonerror()
     */
    public function setQuiet(bool $bool): void
    {
        $this->quiet = $bool;
    }

    public function getQuiet(): bool
    {
        return $this->quiet;
    }

    /**
     * set the property in the project to the value.
     * if the task was give a file or env attribute
     * here is where it is loaded.
     */
    public function main()
    {
        $this->validate();

        if (null !== $this->name && null !== $this->value) {
            $this->addProperty($this->name, $this->value);
        }

        if (null !== $this->file) {
            $this->loadFile($this->file);
        }

        if (null !== $this->env) {
            $this->loadEnvironment($this->env);
        }

        if (null !== $this->name && null !== $this->reference) {
            // get the refereced property
            try {
                $referencedObject = $this->reference->getReferencedObject($this->project);

                if ($referencedObject instanceof Exception) {
                    $reference = $referencedObject->getMessage();
                } else {
                    $reference = (string) $referencedObject;
                }

                $this->addProperty($this->name, $reference);
            } catch (BuildException $be) {
                if (null !== $this->fallback) {
                    $referencedObject = $this->reference->getReferencedObject($this->fallback);

                    if ($referencedObject instanceof Exception) {
                        $reference = $referencedObject->getMessage();
                    } else {
                        $reference = (string) $referencedObject;
                    }
                    $this->addProperty($this->name, $reference);
                } else {
                    throw $be;
                }
            }
        }
    }

    /**
     * load the environment values.
     *
     * @param string $prefix prefix to place before them
     */
    protected function loadEnvironment(string $prefix)
    {
        $props = new Properties();
        if ('.' === substr($prefix, strlen($prefix) - 1)) {
            $prefix .= '.';
        }
        $this->log("Loading Environment {$prefix}", Project::MSG_VERBOSE);
        foreach ($_ENV as $key => $value) {
            $props->setProperty($prefix . '.' . $key, $value);
        }
        $this->addProperties($props);
    }

    /**
     * iterate through a set of properties,
     * resolve them then assign them.
     *
     * @param Properties $props
     *
     * @throws BuildException
     */
    protected function addProperties($props)
    {
        $this->resolveAllProperties($props);
        foreach ($props->keys() as $name) {
            $value = $props->getProperty($name);
            $v = $this->project->replaceProperties($value);
            if (null !== $this->prefix) {
                $name = $this->prefix . $name;
            }
            $this->addProperty($name, $v);
        }
    }

    /**
     * add a name value pair to the project property set.
     *
     * @param string $name  name of property
     * @param string $value value to set
     */
    protected function addProperty($name, $value)
    {
        if (null === $this->file && count($this->filterChains) > 0) {
            $in = FileUtils::getChainedReader(new StringReader($value), $this->filterChains, $this->project);
            $value = $in->read();
        }

        $ph = PropertyHelper::getPropertyHelper($this->getProject());
        if ($this->userProperty) {
            if (null === $ph->getUserProperty(null, $name) || $this->override) {
                $ph->setInheritedProperty(null, $name, $value);
            } else {
                $this->log('Override ignored for ' . $name, Project::MSG_VERBOSE);
            }
        } else {
            if ($this->override) {
                $ph->setProperty(null, $name, $value, true);
            } else {
                $ph->setNewProperty(null, $name, $value);
            }
        }
    }

    /**
     * load properties from a file.
     *
     * @throws BuildException
     */
    protected function loadFile(File $file)
    {
        $fileParser = $this->fileParserFactory->createParser($file->getFileExtension());
        $props = new Properties(null, $fileParser);
        $this->log('Loading ' . $file->getAbsolutePath(), $this->logOutput ? Project::MSG_INFO : Project::MSG_VERBOSE);

        try { // try to load file
            if ($file->exists()) {
                $value = null;
                if (count($this->filterChains) > 0) {
                    $in = FileUtils::getChainedReader(new FileReader($file), $this->filterChains, $this->project);
                    $value = $in->read();
                }
                if ($value) {
                    foreach (array_filter(explode(PHP_EOL, $value)) as $line) {
                        [$key, $prop] = explode('=', $line);
                        $props->setProperty($key, $prop);
                    }
                } else {
                    $props->load($file);
                }
                $this->addProperties($props);
            } else {
                if ($this->required) {
                    throw new BuildException('Unable to find property file: ' . $file->getAbsolutePath());
                }

                $this->log(
                    'Unable to find property file: ' . $file->getAbsolutePath() . '... skipped',
                    $this->quiet ? Project::MSG_VERBOSE : Project::MSG_WARN
                );
            }
        } catch (IOException $ioe) {
            throw new BuildException('Could not load properties from file.', $ioe);
        }
    }

    /**
     * Given a Properties object, this method goes through and resolves
     * any references to properties within the object.
     *
     * @param Properties $props the collection of Properties that need to be resolved
     *
     * @throws BuildException
     */
    protected function resolveAllProperties(Properties $props)
    {
        foreach ($props->keys() as $name) {
            // There may be a nice regex/callback way to handle this
            // replacement, but at the moment it is pretty complex, and
            // would probably be a lot uglier to work into a preg_replace_callback()
            // system.  The biggest problem is the fact that a resolution may require
            // multiple passes.

            $value = $props->getProperty($name);
            $resolved = false;
            $resolveStack = [];

            while (!$resolved) {
                $fragments = [];
                $propertyRefs = [];

                PropertyHelper::getPropertyHelper($this->project)->parsePropertyString(
                    $value,
                    $fragments,
                    $propertyRefs
                );

                $resolved = true;
                if (0 === count($propertyRefs)) {
                    continue;
                }

                $sb = '';

                $j = $propertyRefs;

                foreach ($fragments as $fragment) {
                    if (null !== $fragment) {
                        $sb .= $fragment;

                        continue;
                    }

                    $propertyName = array_shift($j);
                    if (in_array($propertyName, $resolveStack)) {
                        // Should we maybe just log this as an error & move on?
                        // $this->log("Property ".$name." was circularly defined.", Project::MSG_ERR);
                        throw new BuildException('Property ' . $propertyName . ' was circularly defined.');
                    }

                    $fragment = $this->getProject()->getProperty($propertyName);
                    if (null !== $fragment) {
                        $sb .= $fragment;

                        continue;
                    }

                    if ($props->containsKey($propertyName)) {
                        $fragment = $props->getProperty($propertyName);
                        if (false !== strpos($fragment, '${')) {
                            $resolveStack[] = $propertyName;
                            $resolved = false; // parse again (could have been replaced w/ another var)
                        }
                    } else {
                        $fragment = '${' . $propertyName . '}';
                    }

                    $sb .= $fragment;
                }

                $this->log("Resolved Property \"{$value}\" to \"{$sb}\"", Project::MSG_DEBUG);
                $value = $sb;
                $props->setProperty($name, $value);
            } // while (!$resolved)
        } // while (count($keys)
    }

    /**
     * @throws BuildException
     */
    private function validate(): void
    {
        if (null !== $this->name) {
            if (null === $this->value && null === $this->reference) {
                throw new BuildException(
                    'You must specify value or refid with the name attribute',
                    $this->getLocation()
                );
            }
        } elseif (null === $this->file && null === $this->env) {
            throw new BuildException(
                'You must specify file or environment when not using the name attribute',
                $this->getLocation()
            );
        }

        if (null === $this->file && null !== $this->prefix) {
            throw new BuildException('Prefix is only valid when loading from a file.', $this->getLocation());
        }
    }
}
