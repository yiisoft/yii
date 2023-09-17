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
use Phing\Input\InputHandler;
use Phing\Io\File;
use Phing\Io\FileSystem;
use Phing\Io\FileUtils;
use Phing\Io\IOException;
use Phing\Listener\BuildEvent;
use Phing\Listener\BuildListener;
use Phing\Parser\ProjectConfigurator;
use Phing\Task\System\Condition\Condition;
use Phing\Type\Description;
use Phing\Type\PropertyValue;
use Phing\Util\StringHelper;
use ReflectionException;
use ReflectionObject;

/**
 *  The Phing project class. Represents a completely configured Phing project.
 *  The class defines the project and all tasks/targets. It also contains
 *  methods to start a build as well as some properties and FileSystem
 *  abstraction.
 *
 * @author Andreas Aderhold <andi@binarycloud.com>
 * @author Hans Lellelid <hans@xmpl.org>
 */
class Project
{
    // Logging level constants.
    public const MSG_DEBUG = 4;
    public const MSG_VERBOSE = 3;
    public const MSG_INFO = 2;
    public const MSG_WARN = 1;
    public const MSG_ERR = 0;

    /**
     * contains the targets.
     *
     * @var Target[]
     */
    private $targets = [];
    /**
     * global filterset (future use).
     */
    private $globalFilterSet = [];
    /**
     * all globals filters (future use).
     */
    private $globalFilters = [];

    /**
     * holds ref names and a reference to the referred object.
     */
    private $references = [];

    /**
     * The InputHandler being used by this project.
     *
     * @var InputHandler
     */
    private $inputHandler;

    // -- properties that come in via xml attributes --

    /**
     * basedir (PhingFile object).
     */
    private $basedir;

    /**
     * the default target name.
     */
    private $defaultTarget = 'all';

    /**
     * project name (required).
     */
    private $name;

    /**
     * project description.
     */
    private $description;

    /**
     * require phing version.
     */
    private $phingVersion;

    /**
     * project strict mode.
     */
    private $strictMode = false;

    /**
     * a FileUtils object.
     */
    private $fileUtils;

    /**
     * Build listeneers.
     */
    private $listeners = [];

    /**
     * Keep going flag.
     */
    private $keepGoingMode = false;

    /**
     * @var string[]
     */
    private $executedTargetNames = [];

    /**
     *  Constructor, sets any default vars.
     */
    public function __construct()
    {
        $this->fileUtils = new FileUtils();
    }

    /**
     * Sets the input handler.
     *
     * @param InputHandler $handler
     */
    public function setInputHandler($handler)
    {
        $this->inputHandler = $handler;
    }

    /**
     * Retrieves the current input handler.
     *
     * @return InputHandler
     */
    public function getInputHandler()
    {
        return $this->inputHandler;
    }

    /**
     * inits the project, called from main app.
     */
    public function init()
    {
        // set builtin properties
        $this->setSystemProperties();

        $componentHelper = ComponentHelper::getComponentHelper($this);

        $componentHelper->initDefaultDefinitions();
    }

    /**
     * Create and initialize a subproject. By default the subproject will be of
     * the same type as its parent. If a no-arg constructor is unavailable, the
     * <code>Project</code> class will be used.
     *
     * @return Project instance configured as a subproject of this Project
     */
    public function createSubProject(): Project
    {
        try {
            $ref = new ReflectionObject($this);
            $subProject = $ref->newInstance();
        } catch (ReflectionException $e) {
            $subProject = new Project();
        }
        $this->initSubProject($subProject);

        return $subProject;
    }

    /**
     * Initialize a subproject.
     *
     * @param Project $subProject the subproject to initialize
     */
    public function initSubProject(Project $subProject): void
    {
        ComponentHelper::getComponentHelper($subProject)
            ->initSubProject(ComponentHelper::getComponentHelper($this))
        ;
        $subProject->setKeepGoingMode($this->isKeepGoingMode());
        $subProject->setStrictMode($this->strictMode);
    }

    /**
     * returns the global filterset (future use).
     */
    public function getGlobalFilterSet()
    {
        return $this->globalFilterSet;
    }

    // ---------------------------------------------------------
    // Property methods
    // ---------------------------------------------------------

    /**
     * Sets a property. Any existing property of the same name
     * is overwritten, unless it is a user property.
     *
     * @param string $name  The name of property to set.
     *                      Must not be <code>null</code>.
     * @param string $value The new value of the property.
     *                      Must not be <code>null</code>.
     */
    public function setProperty(string $name, $value): void
    {
        PropertyHelper::getPropertyHelper($this)->setProperty(null, $name, $value, true);
    }

    /**
     * Sets a property if no value currently exists. If the property
     * exists already, a message is logged and the method returns with
     * no other effect.
     *
     * @param string $name  The name of property to set.
     *                      Must not be <code>null</code>.
     * @param string $value The new value of the property.
     *                      Must not be <code>null</code>.
     *
     * @since 2.0
     */
    public function setNewProperty(string $name, $value): void
    {
        PropertyHelper::getPropertyHelper($this)->setNewProperty(null, $name, $value);
    }

    /**
     * Sets a user property, which cannot be overwritten by
     * set/unset property calls. Any previous value is overwritten.
     *
     * @param string $name  The name of property to set.
     *                      Must not be <code>null</code>.
     * @param string $value The new value of the property.
     *                      Must not be <code>null</code>.
     *
     * @see   setProperty()
     */
    public function setUserProperty(string $name, $value): void
    {
        PropertyHelper::getPropertyHelper($this)->setUserProperty(null, $name, $value);
    }

    /**
     * Sets a user property, which cannot be overwritten by set/unset
     * property calls. Any previous value is overwritten. Also marks
     * these properties as properties that have not come from the
     * command line.
     *
     * @param string $name  The name of property to set.
     *                      Must not be <code>null</code>.
     * @param string $value The new value of the property.
     *                      Must not be <code>null</code>.
     *
     * @see   setProperty()
     */
    public function setInheritedProperty(string $name, $value): void
    {
        PropertyHelper::getPropertyHelper($this)->setInheritedProperty(null, $name, $value);
    }

    /**
     * Returns the value of a property, if it is set.
     *
     * @param null|string $name The name of the property.
     *                          May be <code>null</code>, in which case
     *                          the return value is also <code>null</code>.
     *
     * @return mixed the property value, or <code>null</code> for no match
     *               or if a <code>null</code> name is provided
     */
    public function getProperty(?string $name)
    {
        return PropertyHelper::getPropertyHelper($this)->getProperty(null, $name);
    }

    /**
     * Replaces ${} style constructions in the given value with the
     * string value of the corresponding data types.
     *
     * @param string $value The value string to be scanned for property references.
     *                      May be <code>null</code>.
     *
     * @throws BuildException if the given value has an unclosed
     *                        property name, e.g. <code>${xxx</code>
     *
     * @return string the given string with embedded property names replaced
     *                by values, or <code>null</code> if the given string is
     *                <code>null</code>
     */
    public function replaceProperties($value)
    {
        return PropertyHelper::getPropertyHelper($this)->replaceProperties($value, $this->getProperties());
    }

    /**
     * Returns the value of a user property, if it is set.
     *
     * @param string $name The name of the property.
     *                     May be <code>null</code>, in which case
     *                     the return value is also <code>null</code>.
     *
     * @return string the property value, or <code>null</code> for no match
     *                or if a <code>null</code> name is provided
     */
    public function getUserProperty($name)
    {
        return PropertyHelper::getPropertyHelper($this)->getUserProperty(null, $name);
    }

    /**
     * Returns a copy of the properties table.
     *
     * @return array a hashtable containing all properties
     *               (including user properties)
     */
    public function getProperties()
    {
        return PropertyHelper::getPropertyHelper($this)->getProperties();
    }

    /**
     * Returns a copy of the user property hashtable.
     *
     * @return array a hashtable containing just the user properties
     */
    public function getUserProperties()
    {
        return PropertyHelper::getPropertyHelper($this)->getUserProperties();
    }

    public function getInheritedProperties()
    {
        return PropertyHelper::getPropertyHelper($this)->getInheritedProperties();
    }

    /**
     * Copies all user properties that have been set on the command
     * line or a GUI tool from this instance to the Project instance
     * given as the argument.
     *
     * <p>To copy all "user" properties, you will also have to call
     * {@link #copyInheritedProperties copyInheritedProperties}.</p>
     *
     * @param Project $other the project to copy the properties to.  Must not be null.
     *
     * @since  phing 2.0
     */
    public function copyUserProperties(Project $other)
    {
        PropertyHelper::getPropertyHelper($this)->copyUserProperties($other);
    }

    /**
     * Copies all user properties that have not been set on the
     * command line or a GUI tool from this instance to the Project
     * instance given as the argument.
     *
     * <p>To copy all "user" properties, you will also have to call
     * {@link #copyUserProperties copyUserProperties}.</p>
     *
     * @param Project $other the project to copy the properties to.  Must not be null.
     *
     * @since phing 2.0
     */
    public function copyInheritedProperties(Project $other)
    {
        PropertyHelper::getPropertyHelper($this)->copyUserProperties($other);
    }

    // ---------------------------------------------------------
    //  END Properties methods
    // ---------------------------------------------------------

    /**
     * Sets default target.
     *
     * @param string $targetName
     */
    public function setDefaultTarget($targetName)
    {
        $this->defaultTarget = (string) trim($targetName);
    }

    /**
     * Returns default target.
     *
     * @return string
     */
    public function getDefaultTarget()
    {
        return (string) $this->defaultTarget;
    }

    /**
     * Sets the name of the current project.
     *
     * @param string $name name of project
     *
     * @author Andreas Aderhold, andi@binarycloud.com
     */
    public function setName($name)
    {
        $this->name = (string) trim($name);
        $this->setUserProperty('phing.project.name', $this->name);
    }

    /**
     * Returns the name of this project.
     *
     * @return string projectname
     *
     * @author Andreas Aderhold, andi@binarycloud.com
     */
    public function getName()
    {
        return (string) $this->name;
    }

    /**
     * Set the projects description.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * return the description, null otherwise.
     *
     * @return null|string
     */
    public function getDescription()
    {
        if (null === $this->description) {
            $this->description = Description::getAll($this);
        }

        return $this->description;
    }

    /**
     * Set the minimum required phing version.
     *
     * @param string $version
     */
    public function setPhingVersion($version)
    {
        $version = str_replace('phing', '', strtolower($version));
        $this->phingVersion = (string) trim($version);
    }

    /**
     * Get the minimum required phing version.
     *
     * @return string
     */
    public function getPhingVersion()
    {
        if (null === $this->phingVersion) {
            $this->setPhingVersion(Phing::getPhingVersion());
        }

        return $this->phingVersion;
    }

    /**
     * Sets the strict-mode (status) for the current project
     * (If strict mode is On, all the warnings would be converted to an error
     * (and the build will be stopped/aborted).
     *
     * @author Utsav Handa, handautsav@hotmail.com
     */
    public function setStrictMode(bool $strictmode)
    {
        $this->strictMode = $strictmode;
        $this->setProperty('phing.project.strictmode', $this->strictMode);
    }

    /**
     * Get the strict-mode status for the project.
     *
     * @return bool
     */
    public function getStrictmode()
    {
        return $this->strictMode;
    }

    /**
     * Set basedir object from xm.
     *
     * @param File|string $dir
     *
     * @throws BuildException
     */
    public function setBasedir($dir)
    {
        if ($dir instanceof File) {
            $dir = $dir->getAbsolutePath();
        }

        $dir = $this->fileUtils->normalize($dir);
        $dir = FileSystem::getFileSystem()->canonicalize($dir);

        $dir = new File((string) $dir);
        if (!$dir->exists()) {
            throw new BuildException('Basedir ' . $dir->getAbsolutePath() . ' does not exist');
        }
        if (!$dir->isDirectory()) {
            throw new BuildException('Basedir ' . $dir->getAbsolutePath() . ' is not a directory');
        }
        $this->basedir = $dir;
        $this->setPropertyInternal('project.basedir', $this->basedir->getPath());
        $this->log('Project base dir set to: ' . $this->basedir->getPath(), Project::MSG_VERBOSE);

        // [HL] added this so that ./ files resolve correctly.  This may be a mistake ... or may be in wrong place.
        chdir($dir->getAbsolutePath());
    }

    /**
     * Returns the basedir of this project.
     *
     * @throws BuildException
     *
     * @return File Basedir PhingFile object
     *
     * @author Andreas Aderhold, andi@binarycloud.com
     */
    public function getBasedir()
    {
        if (null === $this->basedir) {
            try { // try to set it
                $this->setBasedir('.');
            } catch (BuildException $exc) {
                throw new BuildException('Can not set default basedir. ' . $exc->getMessage());
            }
        }

        return $this->basedir;
    }

    /**
     * Set &quot;keep-going&quot; mode. In this mode Ant will try to execute
     * as many targets as possible. All targets that do not depend
     * on failed target(s) will be executed.  If the keepGoing settor/getter
     * methods are used in conjunction with the <code>ant.executor.class</code>
     * property, they will have no effect.
     *
     * @param bool $keepGoingMode &quot;keep-going&quot; mode
     */
    public function setKeepGoingMode($keepGoingMode)
    {
        $this->keepGoingMode = $keepGoingMode;
    }

    /**
     * Return the keep-going mode.  If the keepGoing settor/getter
     * methods are used in conjunction with the <code>phing.executor.class</code>
     * property, they will have no effect.
     *
     * @return bool &quot;keep-going&quot; mode
     */
    public function isKeepGoingMode()
    {
        return $this->keepGoingMode;
    }

    /**
     * Sets system properties and the environment variables for this project.
     */
    public function setSystemProperties()
    {
        // first get system properties
        $systemP = array_merge($this->getProperties(), Phing::getProperties());
        foreach ($systemP as $name => $value) {
            $this->setPropertyInternal($name, $value);
        }

        // and now the env vars
        foreach ($_SERVER as $name => $value) {
            // skip arrays
            if (is_array($value)) {
                continue;
            }
            $this->setPropertyInternal('env.' . $name, $value);
        }
    }

    /**
     * Adds a task definition.
     *
     * @param string $name      name of tag
     * @param string $class     the class path to use
     * @param string $classpath the classpat to use
     */
    public function addTaskDefinition($name, $class, $classpath = null)
    {
        ComponentHelper::getComponentHelper($this)->addTaskDefinition($name, $class, $classpath);
    }

    /**
     * Returns the task definitions.
     *
     * @return array
     */
    public function getTaskDefinitions()
    {
        return ComponentHelper::getComponentHelper($this)->getTaskDefinitions();
    }

    /**
     * Adds a data type definition.
     *
     * @param string $typeName  name of the type
     * @param string $typeClass the class to use
     * @param string $classpath the classpath to use
     */
    public function addDataTypeDefinition($typeName, $typeClass, $classpath = null)
    {
        ComponentHelper::getComponentHelper($this)->addDataTypeDefinition($typeName, $typeClass, $classpath);
    }

    /**
     * Returns the data type definitions.
     *
     * @return array
     */
    public function getDataTypeDefinitions()
    {
        return ComponentHelper::getComponentHelper($this)->getDataTypeDefinitions();
    }

    /**
     * Add a new target to the project.
     *
     * @param string $targetName
     * @param Target $target
     *
     * @throws BuildException
     */
    public function addTarget($targetName, $target)
    {
        if (isset($this->targets[$targetName])) {
            throw new BuildException("Duplicate target: {$targetName}");
        }
        $this->addOrReplaceTarget($targetName, $target);
    }

    /**
     * Adds or replaces a target in the project.
     *
     * @param string $targetName
     * @param Target $target
     */
    public function addOrReplaceTarget($targetName, &$target)
    {
        $this->log("  +Target: {$targetName}", Project::MSG_DEBUG);
        $target->setProject($this);
        $this->targets[$targetName] = $target;

        $ctx = $this->getReference(ProjectConfigurator::PARSING_CONTEXT_REFERENCE);
        $current = $ctx->getCurrentTargets();
        $current[$targetName] = $target;
    }

    /**
     * Returns the available targets.
     *
     * @return Target[]
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * @return string[]
     */
    public function getExecutedTargetNames()
    {
        return $this->executedTargetNames;
    }

    /**
     * Create a new task instance and return reference to it.
     *
     * @param string $taskType Task name
     *
     * @throws BuildException
     *
     * @return Task A task object
     */
    public function createTask($taskType)
    {
        return ComponentHelper::getComponentHelper($this)->createTask($taskType);
    }

    /**
     * Creates a new condition and returns the reference to it.
     *
     * @param string $conditionType
     *
     * @throws BuildException
     *
     * @return Condition
     */
    public function createCondition($conditionType)
    {
        return ComponentHelper::getComponentHelper($this)->createCondition($conditionType);
    }

    /**
     * Create a datatype instance and return reference to it
     * See createTask() for explanation how this works.
     *
     * @param string $typeName Type name
     *
     * @throws BuildException
     *                        Exception
     *
     * @return object A datatype object
     */
    public function createDataType($typeName)
    {
        return ComponentHelper::getComponentHelper($this)->createDataType($typeName);
    }

    /**
     * Executes a list of targets.
     *
     * @param array $targetNames List of target names to execute
     *
     * @throws BuildException
     */
    public function executeTargets($targetNames)
    {
        $this->executedTargetNames = $targetNames;

        foreach ($targetNames as $tname) {
            $this->executeTarget($tname);
        }
    }

    /**
     * Executes a target.
     *
     * @param string $targetName Name of Target to execute
     *
     * @throws BuildException
     */
    public function executeTarget($targetName)
    {
        // complain about executing void
        if (null === $targetName) {
            throw new BuildException('No target specified');
        }

        // invoke topological sort of the target tree and run all targets
        // until targetName occurs.
        $sortedTargets = $this->topoSort($targetName);

        $curIndex = (int) 0;
        $curTarget = null;
        $thrownException = null;
        $buildException = null;
        do {
            try {
                $curTarget = $sortedTargets[$curIndex++];
                $curTarget->performTasks();
            } catch (BuildException $exc) {
                if (!($this->keepGoingMode)) {
                    throw $exc;
                }
                $thrownException = $exc;
            }
            if (null != $thrownException) {
                if ($thrownException instanceof BuildException) {
                    $this->log(
                        "Target '" . $curTarget->getName()
                        . "' failed with message '"
                        . $thrownException->getMessage() . "'.",
                        Project::MSG_ERR
                    );
                    // only the first build exception is reported
                    if (null === $buildException) {
                        $buildException = $thrownException;
                    }
                } else {
                    $this->log(
                        "Target '" . $curTarget->getName()
                        . "' failed with message '"
                        . $thrownException->getMessage() . "'." . PHP_EOL
                        . $thrownException->getTraceAsString(),
                        Project::MSG_ERR
                    );
                    if (null === $buildException) {
                        $buildException = new BuildException($thrownException);
                    }
                }
            }
        } while ($curTarget->getName() !== $targetName);

        if (null !== $buildException) {
            throw $buildException;
        }
    }

    /**
     * Helper function.
     *
     * @param File $rootDir
     *
     * @throws IOException
     */
    public function resolveFile(string $fileName, File $rootDir = null): File
    {
        if (null === $rootDir) {
            return $this->fileUtils->resolveFile($this->basedir, $fileName);
        }

        return $this->fileUtils->resolveFile($rootDir, $fileName);
    }

    /**
     * Return the bool equivalent of a string, which is considered
     * <code>true</code> if either <code>"on"</code>, <code>"true"</code>,
     * or <code>"yes"</code> is found, ignoring case.
     *
     * @param string $s the string to convert to a bool value
     *
     * @return <code>true</code> if the given string is <code>"on"</code>,
     *                           <code>"true"</code> or <code>"yes"</code>, or
     *                           <code>false</code> otherwise
     *
     * @deprecated Use \Phing\Util\StringHelper::booleanValue instead
     */
    public static function toBoolean($s)
    {
        return StringHelper::booleanValue($s);
    }

    /**
     * Topologically sort a set of Targets.
     *
     * @param string $rootTarget is the (String) name of the root Target. The sort is
     *                           created in such a way that the sequence of Targets until the root
     *                           target is the minimum possible such sequence.
     *
     * @throws Exception
     * @throws BuildException
     *
     * @return Target[] targets in sorted order
     */
    public function topoSort($rootTarget)
    {
        $rootTarget = (string) $rootTarget;
        $ret = [];
        $state = [];
        $visiting = [];

        // We first run a DFS based sort using the root as the starting node.
        // This creates the minimum sequence of Targets to the root node.
        // We then do a sort on any remaining unVISITED targets.
        // This is unnecessary for doing our build, but it catches
        // circular dependencies or missing Targets on the entire
        // dependency tree, not just on the Targets that depend on the
        // build Target.

        $this->tsort($rootTarget, $state, $visiting, $ret);

        $retHuman = '';
        for ($i = 0, $_i = count($ret); $i < $_i; ++$i) {
            $retHuman .= (string) $ret[$i] . ' ';
        }
        $this->log("Build sequence for target '{$rootTarget}' is: {$retHuman}", Project::MSG_VERBOSE);

        $keys = array_keys($this->targets);
        while ($keys) {
            $curTargetName = (string) array_shift($keys);
            if (!isset($state[$curTargetName])) {
                $st = null;
            } else {
                $st = (string) $state[$curTargetName];
            }

            if (null === $st) {
                $this->tsort($curTargetName, $state, $visiting, $ret);
            } elseif ('VISITING' === $st) {
                throw new Exception("Unexpected node in visiting state: {$curTargetName}");
            }
        }

        $retHuman = '';
        for ($i = 0, $_i = count($ret); $i < $_i; ++$i) {
            $retHuman .= (string) $ret[$i] . ' ';
        }
        $this->log("Complete build sequence is: {$retHuman}", Project::MSG_VERBOSE);

        return $ret;
    }

    /**
     * Adds a reference to an object. This method is called when the parser
     * detects a id="foo" attribute. It passes the id as $name and a reference
     * to the object assigned to this id as $value.
     *
     * @param string $name
     * @param object $object
     */
    public function addReference($name, $object)
    {
        $ref = $this->references[$name] ?? null;
        if ($ref === $object) {
            return;
        }
        if (null !== $ref && !$ref instanceof UnknownElement) {
            $this->log("Overriding previous definition of reference to {$name}", Project::MSG_VERBOSE);
        }
        $refName = (is_scalar($object) || $object instanceof PropertyValue) ? (string) $object : get_class($object);
        $this->log("Adding reference: {$name} -> " . $refName, Project::MSG_DEBUG);
        $this->references[$name] = $object;
    }

    /**
     * Returns the references array.
     *
     * @return array
     */
    public function getReferences()
    {
        return $this->references;
    }

    /**
     * Returns a specific reference.
     *
     * @param string $key the reference id/key
     *
     * @return object Reference or null if not defined
     */
    public function getReference($key)
    {
        return $this->references[$key] ?? null; // just to be explicit
    }

    /**
     * Does the project know this reference?
     *
     * @param string $key the reference id/key
     */
    public function hasReference(string $key): bool
    {
        return isset($this->references[$key]);
    }

    /**
     * Abstracting and simplifyling Logger calls for project messages.
     *
     * @param string $msg
     * @param int    $level
     */
    public function log($msg, $level = Project::MSG_INFO)
    {
        $this->logObject($this, $msg, $level);
    }

    /**
     * @param string $msg
     * @param int    $level
     * @param mixed  $obj
     */
    public function logObject($obj, $msg, $level, Exception $t = null)
    {
        $this->fireMessageLogged($obj, $msg, $level, $t);

        // Checking whether the strict-mode is On, then consider all the warnings
        // as errors.
        if (($this->strictMode) && (Project::MSG_WARN == $level)) {
            throw new BuildException('Build contains warnings, considered as errors in strict mode', null);
        }
    }

    public function addBuildListener(BuildListener $listener)
    {
        $this->listeners[] = $listener;
    }

    public function removeBuildListener(BuildListener $listener)
    {
        $newarray = [];
        for ($i = 0, $size = count($this->listeners); $i < $size; ++$i) {
            if ($this->listeners[$i] !== $listener) {
                $newarray[] = $this->listeners[$i];
            }
        }
        $this->listeners = $newarray;
    }

    /**
     * @return array
     */
    public function getBuildListeners()
    {
        return $this->listeners;
    }

    public function fireBuildStarted()
    {
        $event = new BuildEvent($this);
        foreach ($this->listeners as $listener) {
            $listener->buildStarted($event);
        }

        $this->log((string) $event, Project::MSG_DEBUG);
    }

    /**
     * @param Exception $exception
     */
    public function fireBuildFinished($exception)
    {
        $event = new BuildEvent($this);
        $event->setException($exception);
        foreach ($this->listeners as $listener) {
            $listener->buildFinished($event);
        }

        $this->log((string) $event, Project::MSG_DEBUG);
    }

    /**
     * @param $target
     */
    public function fireTargetStarted($target)
    {
        $event = new BuildEvent($target);
        foreach ($this->listeners as $listener) {
            $listener->targetStarted($event);
        }

        $this->log((string) $event, Project::MSG_DEBUG);
    }

    /**
     * @param $target
     * @param $exception
     */
    public function fireTargetFinished($target, $exception)
    {
        $event = new BuildEvent($target);
        $event->setException($exception);
        foreach ($this->listeners as $listener) {
            $listener->targetFinished($event);
        }

        $this->log((string) $event, Project::MSG_DEBUG);
    }

    /**
     * @param $task
     */
    public function fireTaskStarted($task)
    {
        $event = new BuildEvent($task);
        foreach ($this->listeners as $listener) {
            $listener->taskStarted($event);
        }

        $this->log((string) $event, Project::MSG_DEBUG);
    }

    /**
     * @param $task
     * @param $exception
     */
    public function fireTaskFinished($task, $exception)
    {
        $event = new BuildEvent($task);
        $event->setException($exception);
        foreach ($this->listeners as $listener) {
            $listener->taskFinished($event);
        }

        $this->log((string) $event, Project::MSG_DEBUG);
    }

    /**
     * @param $event
     * @param $message
     * @param $priority
     */
    public function fireMessageLoggedEvent(BuildEvent $event, $message, $priority)
    {
        $event->setMessage($message, $priority);
        foreach ($this->listeners as $listener) {
            $listener->messageLogged($event);
        }
    }

    /**
     * @param string    $message
     * @param int       $priority
     * @param Exception $t
     * @param mixed     $object
     *
     * @throws Exception
     */
    public function fireMessageLogged($object, $message, $priority, Exception $t = null)
    {
        $event = new BuildEvent($object);
        if (null !== $t) {
            $event->setException($t);
        }
        $this->fireMessageLoggedEvent($event, $message, $priority);
    }

    /**
     * Sets a property unless it is already defined as a user property
     * (in which case the method returns silently).
     *
     * @param string $name  The name of the property.
     *                      Must not be
     *                      <code>null</code>.
     * @param string $value The property value. Must not be <code>null</code>.
     */
    private function setPropertyInternal($name, $value)
    {
        PropertyHelper::getPropertyHelper($this)->setProperty(null, $name, $value, false);
    }

    // one step in a recursive DFS traversal of the target dependency tree.
    // - The array "state" contains the state (VISITED or VISITING or null)
    //   of all the target names.
    // - The stack "visiting" contains a stack of target names that are
    //   currently on the DFS stack. (NB: the target names in "visiting" are
    //    exactly the target names in "state" that are in the VISITING state.)
    // 1. Set the current target to the VISITING state, and push it onto
    //    the "visiting" stack.
    // 2. Throw a BuildException if any child of the current node is
    //    in the VISITING state (implies there is a cycle.) It uses the
    //    "visiting" Stack to construct the cycle.
    // 3. If any children have not been VISITED, tsort() the child.
    // 4. Add the current target to the Vector "ret" after the children
    //    have been visited. Move the current target to the VISITED state.
    //    "ret" now contains the sorted sequence of Targets up to the current
    //    Target.

    /**
     * @param $root
     * @param $state
     * @param $visiting
     * @param $ret
     *
     * @throws BuildException
     * @throws Exception
     */
    private function tsort($root, &$state, &$visiting, &$ret)
    {
        $state[$root] = 'VISITING';
        $visiting[] = $root;

        if (!isset($this->targets[$root]) || !($this->targets[$root] instanceof Target)) {
            $target = null;
        } else {
            $target = $this->targets[$root];
        }

        // make sure we exist
        if (null === $target) {
            $sb = "Target '{$root}' does not exist in this project.";
            array_pop($visiting);
            if (!empty($visiting)) {
                $parent = (string) $visiting[count($visiting) - 1];
                $sb .= " It is a dependency of target '{$parent}'.";
            }
            if ($suggestion = $this->findSuggestion($root)) {
                $sb .= sprintf(" Did you mean '%s'?", $suggestion);
            }

            throw new BuildException($sb);
        }

        $deps = $target->getDependencies();

        while ($deps) {
            $cur = (string) array_shift($deps);
            if (!isset($state[$cur])) {
                $m = null;
            } else {
                $m = (string) $state[$cur];
            }
            if (null === $m) {
                // not been visited
                $this->tsort($cur, $state, $visiting, $ret);
            } elseif ('VISITING' == $m) {
                // currently visiting this node, so have a cycle
                throw $this->makeCircularException($cur, $visiting);
            }
        }

        $p = (string) array_pop($visiting);
        if ($root !== $p) {
            throw new Exception("Unexpected internal error: expected to pop {$root} but got {$p}");
        }

        $state[$root] = 'VISITED';
        $ret[] = $target;
    }

    /**
     * @param string $end
     * @param array  $stk
     *
     * @return BuildException
     */
    private function makeCircularException($end, $stk)
    {
        $sb = "Circular dependency: {$end}";
        do {
            $c = (string) array_pop($stk);
            $sb .= ' <- ' . $c;
        } while ($c != $end);

        return new BuildException($sb);
    }

    /**
     * Finds the Target with the most similar name to function's argument.
     *
     * Will return null if buildfile has no targets.
     *
     * @see https://www.php.net/manual/en/function.levenshtein.php
     *
     * @param string $unknownTarget Target name
     *
     * @return Target
     */
    private function findSuggestion(string $unknownTarget): ?Target
    {
        return array_reduce($this->targets, function (?Target $carry, Target $current) use ($unknownTarget): ?Target {
            // Omit target with empty name (there's always one)
            if (empty(strval($current))) {
                return $carry;
            }
            // $carry is null the first time
            if (is_null($carry)) {
                return $current;
            }

            return levenshtein($unknownTarget, $carry) < levenshtein($unknownTarget, $current) ? $carry : $current;
        });
    }
}
