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

namespace Phing;

use Exception;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Parser\CustomChildCreator;
use Phing\Parser\DynamicAttribute;
use Phing\Type\Path;
use Phing\Type\Reference;
use Phing\Util\Register;
use Phing\Util\StringHelper;
use ReflectionClass;
use ReflectionType;

/**
 * Helper class that collects the methods that a task or nested element
 * holds to set attributes, create nested elements or hold PCDATA
 * elements.
 *
 *<ul>
 * <li><strong>SMART-UP INLINE DOCS</strong></li>
 * <li><strong>POLISH-UP THIS CLASS</strong></li>
 *</ul>
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @author    Hans Lellelid <hans@xmpl.org>
 * @copyright 2001,2002 THYRELL. All rights reserved
 */
class IntrospectionHelper
{
    /**
     * Holds the attribute setter methods.
     *
     * @var array string[]
     */
    private $attributeSetters = [];

    /**
     * Holds methods to create nested elements.
     *
     * @var array string[]
     */
    private $nestedCreators = [];

    /**
     * Holds methods to store configured nested elements.
     *
     * @var array string[]
     */
    private $nestedStorers = [];

    /**
     * Map from attribute names to nested types.
     */
    private $nestedTypes = [];

    /**
     * New idea in phing: any class can register certain
     * keys -- e.g. "task.current_file" -- which can be used in
     * task attributes, if supported.  In the build XML these
     * are referred to like this:
     *         <regexp pattern="\n" replace="%{task.current_file}"/>
     * In the type/task a listener method must be defined:
     *         function setListeningReplace($slot) {}.
     *
     * @var array string[]
     */
    private $slotListeners = [];

    /**
     * The method to add PCDATA stuff.
     *
     * @var string Method name of the addText (redundant?) method, if class supports it :)
     */
    private $methodAddText;

    /**
     * The Class that's been introspected.
     *
     * @var object
     */
    private $bean;

    /**
     * The cache of IntrospectionHelper classes instantiated by getHelper().
     *
     * @var array IntrospectionHelpers[]
     */
    private static $helpers = [];

    /**
     * This function constructs a new introspection helper for a specific class.
     *
     * This method loads all methods for the specified class and categorizes them
     * as setters, creators, slot listeners, etc.  This way, the setAttribue() doesn't
     * need to perform any introspection -- either the requested attribute setter/creator
     * exists or it does not & a BuildException is thrown.
     *
     * @param string $class the classname for this IH
     *
     * @throws BuildException
     */
    public function __construct($class)
    {
        $this->bean = new ReflectionClass($class);

        //$methods = get_class_methods($bean);
        foreach ($this->bean->getMethods() as $method) {
            if ($method->isPublic()) {
                // We're going to keep case-insensitive method names
                // for as long as we're allowed :)  It makes it much
                // easier to map XML attributes to PHP class method names.
                $name = strtolower($method->getName());

                // There are a few "reserved" names that might look like attribute setters
                // but should actually just be skipped.  (Note: this means you can't ever
                // have an attribute named "location" or "tasktype" or a nested element container
                // named "task" [TaskContainer::addTask(Task)].)
                if (
                    'setlocation' === $name
                    || 'settasktype' === $name
                    || ('addtask' === $name
                        && $this->isContainer()
                        && 1 === count($method->getParameters())
                        && Task::class === $method->getParameters()[0])
                ) {
                    continue;
                }

                if ('addtext' === $name) {
                    $this->methodAddText = $method;
                } elseif (0 === strpos($name, 'setlistening')) {
                    // Phing supports something unique called "RegisterSlots"
                    // These are dynamic values that use a basic slot system so that
                    // classes can register to listen to specific slots, and the value
                    // will always be grabbed from the slot (and never set in the project
                    // component).  This is useful for things like tracking the current
                    // file being processed by a filter (e.g. AppendTask sets an append.current_file
                    // slot, which can be ready by the XSLTParam type.)

                    if (1 !== count($method->getParameters())) {
                        throw new BuildException(
                            $method->getDeclaringClass()->getName() . '::' . $method->getName() . '() must take exactly one parameter.'
                        );
                    }

                    $this->slotListeners[$name] = $method;
                } elseif (0 === strpos($name, 'set') && 1 === count($method->getParameters())) {
                    $this->attributeSetters[$name] = $method;
                } elseif (0 === strpos($name, 'create')) {
                    if ($method->getNumberOfRequiredParameters() > 0) {
                        throw new BuildException(
                            $method->getDeclaringClass()->getName() . '::' . $method->getName() . '() may not take any parameters.'
                        );
                    }

                    if ($method->hasReturnType()) {
                        $this->nestedTypes[$name] = $method->getReturnType();
                    } else {
                        preg_match('/@return[\s]+([\w]+)/', $method->getDocComment(), $matches);
                        if (!empty($matches[1]) && class_exists($matches[1], false)) {
                            $this->nestedTypes[$name] = $matches[1];
                        } else {
                            // assume that method createEquals() creates object of type "Equals"
                            // (that example would be false, of course)
                            $this->nestedTypes[$name] = $this->getPropertyName($name, 'create');
                        }
                    }

                    $this->nestedCreators[$name] = $method;
                } elseif (0 === strpos($name, 'addconfigured')) {
                    // *must* use class hints if using addConfigured ...

                    // 1 param only
                    $params = $method->getParameters();

                    if (count($params) < 1) {
                        throw new BuildException(
                            $method->getDeclaringClass()->getName() . '::' . $method->getName() . '() must take at least one parameter.'
                        );
                    }

                    if (count($params) > 1) {
                        $this->warn(
                            $method->getDeclaringClass()->getName() . '::' . $method->getName() . '() takes more than one parameter. (IH only uses the first)'
                        );
                    }

                    $classname = $this->getClassnameFromParameter($params[0]);

                    if (null === $classname) {
                        throw new BuildException(
                            $method->getDeclaringClass()->getName() . '::' . $method->getName() . '() method MUST use a class hint to indicate the class type of parameter.'
                        );
                    }

                    $this->nestedTypes[$name] = $classname;

                    $this->nestedStorers[$name] = $method;
                } elseif (0 === strpos($name, 'add')) {
                    // *must* use class hints if using add ...

                    // 1 param only
                    $params = $method->getParameters();
                    if (count($params) < 1) {
                        throw new BuildException(
                            $method->getDeclaringClass()->getName() . '::' . $method->getName() . '() must take at least one parameter.'
                        );
                    }

                    if (count($params) > 1) {
                        $this->warn(
                            $method->getDeclaringClass()->getName() . '::' . $method->getName() . '() takes more than one parameter. (IH only uses the first)'
                        );
                    }

                    $classname = $this->getClassnameFromParameter($params[0]);

                    // we don't use the classname here, but we need to make sure it exists before
                    // we later try to instantiate a non-existent class
                    if (null === $classname) {
                        throw new BuildException(
                            $method->getDeclaringClass()->getName() . '::' . $method->getName() . '() method MUST use a class hint to indicate the class type of parameter.'
                        );
                    }

                    $this->nestedCreators[$name] = $method;
                }
            } // if $method->isPublic()
        } // foreach
    }

    /**
     * Factory method for helper objects.
     *
     * @param string $class The class to create a Helper for
     *
     * @return IntrospectionHelper
     */
    public static function getHelper($class)
    {
        if (!isset(self::$helpers[$class])) {
            self::$helpers[$class] = new IntrospectionHelper($class);
        }

        return self::$helpers[$class];
    }

    /**
     * Indicates whether the introspected class is a task container, supporting arbitrary nested tasks/types.
     *
     * @return bool true if the introspected class is a container; false otherwise
     */
    public function isContainer()
    {
        return $this->bean->implementsInterface(TaskContainer::class);
    }

    /**
     * Sets the named attribute.
     *
     * @param object $element
     * @param string $attributeName
     * @param mixed  $value
     *
     * @throws BuildException
     */
    public function setAttribute(Project $project, $element, $attributeName, &$value)
    {
        // we want to check whether the value we are setting looks like
        // a slot-listener variable:  %{task.current_file}
        //
        // slot-listener variables are not like properties, in that they cannot be mixed with
        // other text values.  The reason for this disparity is that properties are only
        // set when first constructing objects from XML, whereas slot-listeners are always dynamic.
        //
        // This is made possible by PHP5 (objects automatically passed by reference) and PHP's loose
        // typing.
        if (StringHelper::isSlotVar($value)) {
            $as = 'setlistening' . strtolower($attributeName);

            if (!isset($this->slotListeners[$as])) {
                $msg = $this->getElementName(
                    $project,
                    $element
                ) . " doesn't support a slot-listening '{$attributeName}' attribute.";

                throw new BuildException($msg);
            }

            $method = $this->slotListeners[$as];

            $key = StringHelper::slotVar($value);
            $value = Register::getSlot(
                $key
            ); // returns a RegisterSlot object which will hold current value of that register (accessible using getValue())
        } else {
            // Traditional value options

            $as = 'set' . strtolower($attributeName);

            if (!isset($this->attributeSetters[$as])) {
                if ($element instanceof DynamicAttribute) {
                    $element->setDynamicAttribute($attributeName, (string) $value);

                    return;
                }
                $msg = $this->getElementName($project, $element) . " doesn't support the '{$attributeName}' attribute.";

                throw new BuildException($msg);
            }

            $method = $this->attributeSetters[$as];

            if ('setrefid' == $as) {
                $value = new Reference($project, $value);
            } else {
                $params = $method->getParameters();

                /** @var ReflectionType $hint */
                $reflectedAttr = ($hint = $params[0]->getType()) ? $hint->getName() : null;

                // value is a string representation of a bool type,
                // convert it to primitive
                if ('bool' === $reflectedAttr || ('string' !== $reflectedAttr && StringHelper::isBoolean($value))) {
                    $value = StringHelper::booleanValue($value);
                }

                // there should only be one param; we'll just assume ....
                if (null !== $reflectedAttr) {
                    switch ($reflectedAttr) {
                        case File::class:
                            $value = $project->resolveFile($value);

                            break;

                        case Path::class:
                            $value = new Path($project, $value);

                            break;

                        case Reference::class:
                            $value = new Reference($project, $value);

                            break;
                        // any other object params we want to support should go here ...
                    }
                } // if hint !== null
            } // if not setrefid
        } // if is slot-listener

        try {
            $project->log(
                '    -calling setter ' . $method->getDeclaringClass()->getName() . '::' . $method->getName() . '()',
                Project::MSG_DEBUG
            );
            $method->invoke($element, $value);
        } catch (Exception $exc) {
            throw new BuildException($exc->getMessage(), $exc);
        }
    }

    /**
     * Adds PCDATA areas.
     *
     * @param string $element
     * @param string $text
     *
     * @throws BuildException
     */
    public function addText(Project $project, $element, $text)
    {
        if (null === $this->methodAddText) {
            $msg = $this->getElementName($project, $element) . " doesn't support nested text data.";

            throw new BuildException($msg);
        }

        try {
            $method = $this->methodAddText;
            $method->invoke($element, $text);
        } catch (Exception $exc) {
            throw new BuildException($exc->getMessage(), $exc);
        }
    }

    /**
     * Creates a named nested element.
     *
     * Valid creators can be in the form createFoo() or addFoo(Bar).
     *
     * @param object $element     Object the XML tag is child of.
     *                            Often a task object.
     * @param string $elementName XML tag name
     *
     * @throws BuildException
     *
     * @return object returns the nested element
     */
    public function createElement(Project $project, $element, $elementName)
    {
        $addMethod = 'add' . strtolower($elementName);
        $createMethod = 'create' . strtolower($elementName);
        $nestedElement = null;

        if (isset($this->nestedCreators[$createMethod])) {
            $method = $this->nestedCreators[$createMethod];

            try { // try to invoke the creator method on object
                $project->log(
                    '    -calling creator ' . $method->getDeclaringClass()->getName() . '::' . $method->getName() . '()',
                    Project::MSG_DEBUG
                );
                $nestedElement = $method->invoke($element);
            } catch (Exception $exc) {
                throw new BuildException($exc->getMessage(), $exc);
            }
        } elseif (isset($this->nestedCreators[$addMethod])) {
            $method = $this->nestedCreators[$addMethod];

            // project components must use class hints to support the add methods

            try { // try to invoke the adder method on object
                $project->log(
                    '    -calling adder ' . $method->getDeclaringClass()->getName() . '::' . $method->getName() . '()',
                    Project::MSG_DEBUG
                );
                // we've already assured that correct num of params
                // exist and that method is using class hints
                $params = $method->getParameters();

                $classname = $this->getClassnameFromParameter($params[0]);

                // create a new instance of the object and add it via $addMethod
                $clazz = new ReflectionClass($classname);
                if (null !== $clazz->getConstructor() && $clazz->getConstructor()->getNumberOfRequiredParameters() >= 1) {
                    $nestedElement = new $classname(Phing::getCurrentProject() ?? $project);
                } else {
                    $nestedElement = new $classname();
                }

                if ($nestedElement instanceof Task && $element instanceof Task) {
                    $nestedElement->setOwningTarget($element->getOwningTarget());
                }

                $method->invoke($element, $nestedElement);
            } catch (Exception $exc) {
                throw new BuildException($exc->getMessage(), $exc);
            }
        } elseif ($this->bean->implementsInterface(CustomChildCreator::class)) {
            $method = $this->bean->getMethod('customChildCreator');

            try {
                $nestedElement = $method->invoke($element, strtolower($elementName), $project);
            } catch (Exception $exc) {
                throw new BuildException($exc->getMessage(), $exc);
            }
        } else {
            //try the add method for the element's parent class
            $typedefs = $project->getDataTypeDefinitions();
            if (isset($typedefs[$elementName])) {
                $elementClass = Phing::import($typedefs[$elementName]);
                $parentClass = get_parent_class($elementClass);
                $addMethod = 'add' . strtolower($parentClass);

                if (isset($this->nestedCreators[$addMethod])) {
                    $method = $this->nestedCreators[$addMethod];

                    try {
                        $project->log(
                            '    -calling parent adder '
                            . $method->getDeclaringClass()->getName() . '::' . $method->getName() . '()',
                            Project::MSG_DEBUG
                        );
                        $nestedElement = new $elementClass();
                        $method->invoke($element, $nestedElement);
                    } catch (Exception $exc) {
                        throw new BuildException($exc->getMessage(), $exc);
                    }
                }
            }
            if (null === $nestedElement) {
                $msg = $this->getElementName($project, $element) . " doesn't support the '{$elementName}' creator/adder.";

                throw new BuildException($msg);
            }
        }

        if ($nestedElement instanceof ProjectComponent) {
            $nestedElement->setProject($project);
        }

        return $nestedElement;
    }

    /**
     * Creates a named nested element.
     *
     * @param Project     $project
     * @param string      $element
     * @param string      $child
     * @param null|string $elementName
     *
     * @throws BuildException
     */
    public function storeElement($project, $element, $child, $elementName = null)
    {
        if (null === $elementName) {
            return;
        }

        $storer = 'addconfigured' . strtolower($elementName);

        if (isset($this->nestedStorers[$storer])) {
            $method = $this->nestedStorers[$storer];

            try {
                $project->log(
                    '    -calling storer ' . $method->getDeclaringClass()->getName() . '::' . $method->getName() . '()',
                    Project::MSG_DEBUG
                );
                $method->invoke($element, $child);
            } catch (Exception $exc) {
                throw new BuildException($exc->getMessage(), $exc);
            }
        }
    }

    /**
     * Does the introspected class support PCDATA?
     *
     * @return bool
     */
    public function supportsCharacters()
    {
        return null !== $this->methodAddText;
    }

    /**
     * Return all attribues supported by the introspected class.
     *
     * @return string[]
     */
    public function getAttributes()
    {
        $attribs = [];
        foreach (array_keys($this->attributeSetters) as $setter) {
            $attribs[] = $this->getPropertyName($setter, 'set');
        }

        return $attribs;
    }

    /**
     * Return all nested elements supported by the introspected class.
     *
     * @return string[]
     */
    public function getNestedElements()
    {
        return $this->nestedTypes;
    }

    /**
     * Get the name for an element.
     * When possible the full classnam (phing.tasks.system.PropertyTask) will
     * be returned.  If not available (loaded in taskdefs or typedefs) then the
     * XML element name will be returned.
     *
     * @param object $element the Task or type element
     *
     * @return string fully qualified class name of element when possible
     */
    public function getElementName(Project $project, $element)
    {
        $taskdefs = $project->getTaskDefinitions();
        $typedefs = $project->getDataTypeDefinitions();

        // check if class of element is registered with project (tasks & types)
        // most element types don't have a getTag() method
        $elClass = get_class($element);

        if (!in_array('getTag', get_class_methods($elClass))) {
            // loop through taskdefs and typesdefs and see if the class name
            // matches (case-insensitive) any of the classes in there
            foreach (array_merge($taskdefs, $typedefs) as $elName => $class) {
                if (0 === strcasecmp($elClass, $class)) {
                    return $class;
                }
            }

            return "{$elClass} (unknown)";
        }

        // ->getTag() method does exist, so use it
        $elName = $element->getTag();
        if (isset($taskdefs[$elName])) {
            return $taskdefs[$elName];
        }

        if (isset($typedefs[$elName])) {
            return $typedefs[$elName];
        }

        return "{$elName} (unknown)";
    }

    /**
     * Extract the name of a property from a method name - subtracting  a given prefix.
     *
     * @param string $methodName
     * @param string $prefix
     *
     * @return string
     */
    public function getPropertyName($methodName, $prefix)
    {
        $start = strlen($prefix);

        return strtolower(substr($methodName, $start));
    }

    /**
     * Prints warning message to screen if -debug was used.
     *
     * @param string $msg
     */
    public function warn($msg)
    {
        if (Project::MSG_DEBUG === Phing::getMsgOutputLevel()) {
            echo '[IntrospectionHelper] ' . $msg . "\n";
        }
    }

    /**
     * @param \ReflectionParameter $parameter
     * @return mixed|null
     */
    private function getClassnameFromParameter(\ReflectionParameter $parameter)
    {
        /** @var ReflectionType $reflectionType */
        $reflectionType = $parameter->getType();
        if ($reflectionType instanceof \ReflectionNamedType) {
            return !$reflectionType->isBuiltin() ? $reflectionType->getName() : null;
        }
        return null;
    }
}
