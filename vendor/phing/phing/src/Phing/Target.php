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

use Phing\Exception\BuildException;
use Phing\Parser\Location;

use function array_search;

/**
 * The Target component. Carries all required target data. Implements the
 * abstract class {@link TaskContainer}.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 *
 * @see       TaskContainer
 */
class Target implements TaskContainer
{
    /**
     * Name of target.
     *
     * @var string
     */
    private $name;

    /**
     * Dependencies.
     *
     * @var array
     */
    private $dependencies = [];

    /**
     * Holds objects of children of this target.
     *
     * @var array
     */
    private $children = [];

    /**
     * The if condition from xml.
     *
     * @var string
     */
    private $ifCondition = '';

    /**
     * The unless condition from xml.
     *
     * @var string
     */
    private $unlessCondition = '';

    /**
     * Description of this target.
     *
     * @var string
     */
    private $description;

    /**
     * Whether to hide target in targets list (-list -p switches).
     *
     * @var bool
     */
    private $hidden = false;

    /**
     * Whether to log message as INFO or VERBOSE if target skipped.
     *
     * @var bool
     */
    private $logSkipped = false;

    /**
     * Rreference to project.
     *
     * @var Project
     */
    private $project;
    private $location;

    public function __construct(Target $other = null)
    {
        if (null !== $other) {
            $this->name = $other->name;
            $this->ifCondition = $other->ifCondition;
            $this->unlessCondition = $other->unlessCondition;
            $this->dependencies = $other->dependencies;
            $this->location = $other->location;
            $this->project = $other->project;
            $this->description = $other->description;
            // The children are added to after this cloning
            $this->children = $other->children;
        }
    }

    /**
     * Returns a string representation of this target. In our case it
     * simply returns the target name field.
     *
     * @return string The string representation of this target
     */
    public function __toString()
    {
        return (string) $this->name;
    }

    /**
     * References the project to the current component.
     *
     * @param Project $project The reference to the current project
     */
    public function setProject(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Returns reference to current project.
     *
     * @return Project Reference to current porject object
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Sets the location of this target's definition.
     *
     * @param location location
     */
    public function setLocation(Location $location): void
    {
        $this->location = $location;
    }

    /**
     * Get the location of this target's definition.
     */
    public function getLocation(): Location
    {
        return $this->location;
    }

    /**
     * Sets the target dependencies from xml.
     *
     * @param string $depends Comma separated list of targetnames that depend on
     *                        this target
     *
     * @throws BuildException
     */
    public function setDepends($depends)
    {
        // explode should be faster than strtok
        $deps = explode(',', $depends);
        for ($i = 0, $size = count($deps); $i < $size; ++$i) {
            $trimmed = trim($deps[$i]);
            if ('' === $trimmed) {
                throw new BuildException(
                    'Syntax Error: Depend attribute for target ' . $this->getName() . ' is malformed.'
                );
            }
            $this->addDependency($trimmed);
        }
    }

    /**
     * Adds a singular dependent target name to the list.
     *
     * @param string $dependency The dependency target to add
     */
    public function addDependency($dependency)
    {
        $this->dependencies[] = (string) $dependency;
    }

    /**
     * Returns reference to indexed array of the dependencies this target has.
     *
     * @return array Reference to target dependencoes
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param string $targetName Name of the target to search for
     *
     * @return false|int|string
     */
    public function dependsOn($targetName)
    {
        return array_search($targetName, $this->dependencies);
    }

    /**
     * Sets the name of the target.
     *
     * @param string $name Name of this target
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Returns name of this target.
     *
     * @return string The name of the target
     */
    public function getName()
    {
        return (string) $this->name;
    }

    /**
     * Set target status. If true, target does not come in phing -list.
     *
     * @param bool $flag
     *
     * @return Target
     */
    public function setHidden($flag)
    {
        $this->hidden = (bool) $flag;

        return $this;
    }

    /**
     * Get target status. If true, target does not come in phing -list.
     *
     * @return bool
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Alias for getHidden().
     *
     * @return bool
     */
    public function isHidden()
    {
        return $this->getHidden();
    }

    /**
     * Adds a task element to the list of this targets child elements.
     *
     * @param Task $task The task object to add
     */
    public function addTask(Task $task)
    {
        $this->children[] = $task;
    }

    /**
     * Adds a runtime configurable element to the list of this targets child
     * elements.
     *
     * @param RuntimeConfigurable $rtc The RuntimeConfigurable object
     */
    public function addDataType($rtc)
    {
        $this->children[] = $rtc;
    }

    /**
     * Returns an array of all tasks this target has as childrens.
     *
     * The task objects are copied here. Don't use this method to modify
     * task objects.
     *
     * @return array Task[]
     */
    public function getTasks()
    {
        $tasks = [];
        for ($i = 0, $size = count($this->children); $i < $size; ++$i) {
            $tsk = $this->children[$i];
            if ($tsk instanceof Task) {
                // note: we're copying objects here!
                $tasks[] = clone $tsk;
            }
        }

        return $tasks;
    }

    /**
     * Set the if-condition from the XML tag, if any. The property name given
     * as parameter must be present so the if condition evaluates to true.
     *
     * @param string|null $property The property name that has to be present
     */
    public function setIf($property)
    {
        $this->ifCondition = $property ?? '';
    }

    public function getIf()
    {
        return $this->ifCondition;
    }

    /**
     * Set the unless-condition from the XML tag, if any. The property name
     * given as parameter must be present so the unless condition evaluates
     * to true.
     *
     * @param string|null $property The property name that has to be present
     */
    public function setUnless($property)
    {
        $this->unlessCondition = $property ?? '';
    }

    public function getUnless()
    {
        return $this->unlessCondition;
    }

    /**
     * Sets a textual description of this target.
     *
     * @param string $description The description text
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Returns the description of this target.
     *
     * @return string The description text of this target
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function setLogSkipped(bool $log)
    {
        $this->logSkipped = $log;
    }

    /**
     * @return null|bool
     */
    public function getLogSkipped()
    {
        if (null === $this->logSkipped) {
            $this->setLogSkipped(false);
        }

        return $this->logSkipped;
    }

    /**
     * The entry point for this class. Does some checking, then processes and
     * performs the tasks for this target.
     */
    public function main()
    {
        if ($this->testIfCondition() && $this->testUnlessCondition()) {
            foreach ($this->children as $o) {
                if ($o instanceof Task) {
                    // child is a task
                    $o->perform();
                } elseif ($o instanceof RuntimeConfigurable) {
                    // child is a RuntimeConfigurable
                    $o->maybeConfigure($this->project);
                }
            }
        } elseif (!$this->testIfCondition()) {
            $this->project->log(
                "Skipped target '" . $this->name . "' because property '" . $this->ifCondition . "' not set.",
                $this->getLogSkipped() ? Project::MSG_INFO : Project::MSG_VERBOSE
            );
        } else {
            $this->project->log(
                "Skipped target '" . $this->name . "' because property '" . $this->unlessCondition . "' set.",
                $this->getLogSkipped() ? Project::MSG_INFO : Project::MSG_VERBOSE
            );
        }
    }

    /**
     * Performs the tasks by calling the main method of this target that
     * actually executes the tasks.
     *
     * This method is for ZE2 and used for proper exception handling of
     * task exceptions.
     */
    public function performTasks()
    {
        try { // try to execute this target
            $this->project->fireTargetStarted($this);
            $this->main();
            $this->project->fireTargetFinished($this, $null = null);
        } catch (BuildException $exc) {
            // log here and rethrow
            $this->project->fireTargetFinished($this, $exc);

            throw $exc;
        }
    }

    /**
     * Replaces all occurrences of the given task in the list
     * of children with the replacement data type wrapper.
     *
     * @param RuntimeConfigurable|Task $o
     */
    public function replaceChild(Task $task, $o)
    {
        $keys = array_keys($this->children, $task);
        foreach ($keys as $index) {
            $this->children[$index] = $o;
        }
    }

    /**
     * @param string $depends
     * @param string $targetName
     * @param string $attributeName
     *
     * @throws BuildException
     *
     * @return string[]
     */
    public static function parseDepends($depends, $targetName, $attributeName)
    {
        $list = [];
        if ('' !== $depends) {
            $list = explode(',', $depends);
            array_walk($list, 'trim');
            if (0 === count($list)) {
                throw new BuildException('Syntax Error: '
                    . $attributeName
                    . ' attribute of target "'
                    . $targetName
                    . '" contains an empty string.');
            }
        }

        return $list;
    }

    /**
     * Tests if the property set in ifConfiditon exists.
     *
     * @return bool <code>true</code> if the property specified
     *              in <code>$this->ifCondition</code> exists;
     *              <code>false</code> otherwise
     */
    private function testIfCondition()
    {
        if ('' === $this->ifCondition) {
            return true;
        }

        $properties = explode(',', $this->ifCondition);

        $result = true;
        foreach ($properties as $property) {
            $test = $this->getProject()->replaceProperties($property);
            $result = $result && (null !== $this->project->getProperty($test));
        }

        return $result;
    }

    /**
     * Tests if the property set in unlessCondition exists.
     *
     * @return bool <code>true</code> if the property specified
     *              in <code>$this->unlessCondition</code> exists;
     *              <code>false</code> otherwise
     */
    private function testUnlessCondition()
    {
        if ('' === $this->unlessCondition) {
            return true;
        }

        $properties = explode(',', $this->unlessCondition);

        $result = true;
        foreach ($properties as $property) {
            $test = $this->getProject()->replaceProperties($property);
            $result = $result && (null === $this->project->getProperty($test));
        }

        return $result;
    }
}
