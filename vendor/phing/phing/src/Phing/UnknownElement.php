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

/**
 * Wrapper class that holds all information necessary to create a task
 * that did not exist when Phing started.
 *
 * <em> This has something to do with phing encountering an task XML element
 * it is not aware of at start time. This is a situation where special steps
 * need to be taken so that the element is then known.</em>
 *
 * @author  Andreas Aderhold <andi@binarycloud.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class UnknownElement extends Task
{
    private $elementName;
    private $realThing;
    private $children = [];

    /**
     * Constructs a UnknownElement object.
     *
     * @param string $elementName The XML element name that is unknown
     */
    public function __construct($elementName)
    {
        parent::__construct();
        $this->elementName = (string) $elementName;
    }

    /**
     * Return the XML element name that this <code>UnnownElement</code>
     * handles.
     *
     * @return string The XML element name that is unknown
     */
    public function getTag()
    {
        return (string) $this->elementName;
    }

    /**
     * Tries to configure the unknown element.
     *
     * @throws BuildException if the element can not be configured
     */
    public function maybeConfigure()
    {
        if (null !== $this->realThing) {
            return;
        }
        $this->configure($this->makeObject($this, $this->wrapper));
    }

    public function configure($realObj)
    {
        if (null === $realObj) {
            return;
        }
        $this->realThing = $realObj;
        $this->wrapper->setProxy($this->realThing);

        $task = null;
        if ($this->realThing instanceof Task) {
            $task = $this->realThing;
            $task->setRuntimeConfigurableWrapper($this->wrapper);
            if (null !== $this->getWrapper()->getId()) {
                $this->getOwningTarget()->replaceChild($this, $this->realThing);
            }
        }

        if (null !== $task) {
            $task->maybeConfigure();
        } else {
            $this->getWrapper()->maybeConfigure($this->getProject());
        }
        $this->handleChildren($this->realThing, $this->wrapper);
    }

    /**
     * Called when the real task has been configured for the first time.
     *
     * @throws BuildException if the task can not be created
     */
    public function main()
    {
        if (null === $this->realThing) {
            // plain impossible to get here, maybeConfigure should
            // have thrown an exception.
            throw new BuildException("Should not be executing UnknownElement::main() -- task/type: {$this->elementName}");
        }

        if ($this->realThing instanceof Task) {
            $this->realThing->main();
        }
    }

    /**
     * Add a child element to the unknown element.
     *
     * @internal param The $object object representing the child element
     */
    public function addChild(UnknownElement $child)
    {
        $this->children[] = $child;
    }

    /**
     *  Handle child elemets of the unknown element, if any.
     *
     * @param object $parent        The parent object the unknown element belongs to
     * @param object $parentWrapper The parent wrapper object
     */
    public function handleChildren($parent, $parentWrapper)
    {
        if ($parent instanceof TypeAdapter) {
            $parent = $parent->getProxy();
        }

        $parentClass = null === $parent ? get_class() : get_class($parent);
        $ih = IntrospectionHelper::getHelper($parentClass);

        for ($i = 0, $childrenCount = count($this->children); $i < $childrenCount; ++$i) {
            $childWrapper = $parentWrapper->getChild($i);
            $child = $this->children[$i];

            $realChild = null;
            if ($parent instanceof TaskContainer) {
                $parent->addTask($child);

                continue;
            }

            $project = $this->project ?? $parent->getProject();
            $realChild = $ih->createElement($project, $parent, $child->getTag());

            $childWrapper->setProxy($realChild);
            if ($realChild instanceof Task) {
                $realChild->setRuntimeConfigurableWrapper($childWrapper);
            }

            $childWrapper->maybeConfigure($this->project);
            $child->handleChildren($realChild, $childWrapper);
        }
    }

    /**
     *  Get the name of the task to use in logging messages.
     *
     * @return string The task's name
     */
    public function getTaskName()
    {
        return null === $this->realThing || !$this->realThing instanceof Task
            ? parent::getTaskName()
            : $this->realThing->getTaskName();
    }

    /**
     * Returns the task instance after it has been created and if it is a task.
     *
     * @return Task a task instance or <code>null</code> if the real object is not
     *              a task
     */
    public function getTask()
    {
        if ($this->realThing instanceof Task) {
            return $this->realThing;
        }

        return null;
    }

    /**
     * Return the configured object.
     *
     * @return object the real thing whatever it is
     */
    public function getRealThing()
    {
        return $this->realThing;
    }

    /**
     * Set the configured object.
     *
     * @param object $realThing the configured object
     */
    public function setRealThing($realThing)
    {
        $this->realThing = $realThing;
    }

    /**
     * Creates a named task or data type. If the real object is a task,
     * it is configured up to the init() stage.
     *
     * @param UnknownElement      $ue The unknown element to create the real object for.
     *                                Must not be <code>null</code>.
     * @param RuntimeConfigurable $w  ignored in this implementation
     *
     * @throws BuildException
     *
     * @return object the Task or DataType represented by the given unknown element
     */
    protected function makeObject(UnknownElement $ue, RuntimeConfigurable $w)
    {
        $o = $this->makeTask($ue, $w, true);
        if (null === $o) {
            $o = $this->project->createDataType($ue->getTag());
        }
        if (null === $o) {
            throw new BuildException(
                "Could not create task/type: '" . $ue->getTag() . "'. Make sure that this class has been declared using taskdef / typedef."
            );
        }
        if ($o instanceof Task) {
            $o->setOwningTarget($this->getOwningTarget());
        }
        if ($o instanceof ProjectComponent) {
            $o->setLocation($this->getLocation());
        }

        return $o;
    }

    /**
     *  Create a named task and configure it up to the init() stage.
     *
     * @param UnknownElement      $ue         The unknwon element to create a task from
     * @param RuntimeConfigurable $w          The wrapper object
     * @param bool                $onTopLevel whether to treat this task as if it is top-level
     *
     * @throws BuildException
     *
     * @return Task The freshly created task
     */
    protected function makeTask(UnknownElement $ue, RuntimeConfigurable $w, $onTopLevel = false)
    {
        $task = $this->project->createTask($ue->getTag());

        if (null === $task) {
            if (!$onTopLevel) {
                throw new BuildException("Could not create task of type: '" . $this->elementName . "'. Make sure that this class has been declared using taskdef.");
            }

            return null;
        }
        $task->setLocation($this->getLocation());
        if (null !== $this->target) {
            $task->setOwningTarget($this->target);
        }
        $task->init();

        return $task;
    }
}
