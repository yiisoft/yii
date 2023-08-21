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

namespace Phing\Task\System\Property;

use Exception;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\IOException;
use Phing\Project;
use Phing\PropertyHelper;
use Phing\Task\System\PropertyTask;
use Phing\Util\Properties;
use ReflectionObject;
use ReflectionProperty;

/**
 * Variable Task.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class Variable extends PropertyTask
{
    private $remove = false;

    /**
     * Determines whether the property should be removed from the project.
     * Default is false. Once  removed, conditions that check for property
     * existence will find this property does not exist.
     *
     * @param bool $b set to true to remove the property from the project
     */
    public function setUnset($b)
    {
        $this->remove = $b;
    }

    /**
     * Execute this task.
     *
     * @throws BuildException Description of the Exception
     */
    public function main()
    {
        if ($this->remove) {
            if (null === $this->name || '' === $this->name) {
                throw new BuildException("The 'name' attribute is required with 'unset'.");
            }
            $this->removeProperty($this->name);

            return;
        }
        if (null === $this->file) {
            // check for the required name attribute
            if (null === $this->name || '' === $this->name) {
                throw new BuildException("The 'name' attribute is required.");
            }

            // adjust the property value if necessary -- is this necessary?
            // Doesn't Ant do this automatically?
            $this->value = $this->getProject()->replaceProperties($this->value);

            // set the property
            $this->forceProperty($this->name, $this->value);
        } else {
            if (!$this->file->exists()) {
                throw new BuildException($this->file->getAbsolutePath() . ' does not exists.');
            }
            $this->loadFile($this->file);
        }
    }

    /**
     * load variables from a file.
     *
     * @param File $file file to load
     *
     * @throws BuildException
     */
    protected function loadFile(File $file)
    {
        $props = new Properties();

        try {
            if ($file->exists()) {
                $props->load($file);

                $this->addProperties($props);
            } else {
                $this->log(
                    'Unable to find property file: ' . $file->getAbsolutePath(),
                    Project::MSG_VERBOSE
                );
            }
        } catch (IOException $ex) {
            throw new BuildException($ex, $this->getLocation());
        }
    }

    /**
     * iterate through a set of properties, resolve them, then assign them.
     *
     * @param Properties $props The feature to be added to the Properties attribute
     */
    protected function addProperties($props)
    {
        $this->resolveAllProperties($props);
        foreach ($props->keys() as $name) {
            $this->forceProperty($name, $props->getProperty($name));
        }
    }

    /**
     * resolve properties inside a properties hashtable.
     *
     * @param Properties $props properties object to resolve
     *
     * @throws BuildException Description of the Exception
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

            $ih = PropertyHelper::getPropertyHelper($this->project);

            while (!$resolved) {
                $fragments = [];
                $propertyRefs = [];

                // [HL] this was ::parsePropertyString($this->value ...) ... this seems wrong
                $ih->parsePropertyString($value, $fragments, $propertyRefs);

                $resolved = true;
                if (0 == count($propertyRefs)) {
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

                $this->getProject()->setProperty($name, $value);
            }
        }
    }

    /**
     * Remove a property from the project's property table and the userProperty table.
     * Note that Ant 1.6 uses a helper for this.
     *
     * @param mixed $name
     */
    private function removeProperty($name)
    {
        $properties = null;

        try {
            $properties = $this->getPropValue($this->getProject(), 'properties');
            if (null !== $properties) {
                unset($properties[$name]);
                $this->setPropValue($properties, $this->getProject(), 'properties');
            }
        } catch (Exception $e) {
        }

        try {
            $properties = $this->getPropValue($this->getProject(), 'userProperties');
            if (null !== $properties) {
                unset($properties[$name]);
                $this->setPropValue($properties, $this->getProject(), 'userProperties');
            }
        } catch (Exception $e) {
        }
    }

    private function forceProperty($name, $value)
    {
        try {
            $properties = $this->getPropValue($this->getProject(), 'properties');
            if (null === $properties) {
                $this->getProject()->setUserProperty($name, $value);
            } else {
                $properties[$name] = $value;
                $this->setPropValue($properties, $this->getProject(), 'properties');
            }
        } catch (Exception $e) {
            $this->getProject()->setUserProperty($name, $value);
        }
    }

    /**
     * Get a private property of a class.
     *
     * @param mixed  $thisClass The class
     * @param string $fieldName The property to get
     *
     * @throws Exception
     *
     * @return ReflectionProperty The property value
     */
    private function getField($thisClass, $fieldName)
    {
        $refClazz = new ReflectionObject($thisClass);
        if (!$refClazz->hasProperty($fieldName)) {
            throw new Exception("Invalid field : {$fieldName}");
        }

        return $refClazz->getProperty($fieldName);
    }

    /**
     * Get a private property of an object.
     *
     * @param mixed  $instance  the object instance
     * @param string $fieldName the name of the field
     *
     * @throws Exception
     *
     * @return mixed an object representing the value of the field
     */
    private function getPropValue($instance, $fieldName)
    {
        $field = $this->getField($instance, $fieldName);
        $field->setAccessible(true);

        return $field->getValue($instance);
    }

    private function setPropValue($value, $instance, $fieldName)
    {
        $field = $this->getField($instance, $fieldName);
        $field->setAccessible(true);
        $field->setValue($instance, $value);
    }
}
