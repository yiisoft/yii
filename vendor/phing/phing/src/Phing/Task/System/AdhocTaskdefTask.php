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
use Phing\Project;
use Phing\Task;
use ReflectionClass;
use ReflectionException;

/**
 * A class for creating adhoc tasks in build file.
 *
 * <target name="test-adhoc">
 *        <adhoc-task name="foo"><![CDATA[
 *
 *            class FooTest extends Task {
 *                private $bar;
 *
 *                function setBar($bar) {
 *                    $this->bar = $bar;
 *                }
 *
 *                function main() {
 *                    $this->log("In FooTest: " . $this->bar);
 *                }
 *            }
 *
 *        ]]></adhoc-task>
 *
 *      <foo bar="B.L.I.N.G"/>
 * </target>
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class AdhocTaskdefTask extends AdhocTask
{
    /**
     * The tag that refers to this task.
     */
    private $name;

    /**
     * Set the tag that will represent this adhoc task/type.
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Main entry point.
     */
    public function main()
    {
        if (null === $this->name) {
            throw new BuildException('The name attribute is required for adhoc task definition.', $this->getLocation());
        }

        $taskdefs = $this->getProject()->getTaskDefinitions();

        if (!isset($taskdefs[$this->name])) {
            $this->execute();

            $classes = $this->filterValidTasks($this->getNewClasses());

            if (count($classes) < 1) {
                throw new BuildException(
                    'You must define at least one class that is a sub-class of ' . Task::class,
                    $this->getLocation()
                );
            }

            $classname = array_shift($classes);

            $this->log('Task ' . $this->name . ' will be handled by class ' . $classname, Project::MSG_VERBOSE);
            $this->project->addTaskDefinition($this->name, $classname);
        }
    }

    /**
     * @param string[] $classes
     *
     * @throws ReflectionException
     *
     * @return string[]
     */
    private function filterValidTasks(array $classes): array
    {
        return array_filter($classes, function ($classname) {
            $t = new ReflectionClass($classname);

            if (!$t->isSubclassOf(Task::class) || !$t->isInstantiable()) {
                return false;
            }

            return true;
        });
    }
}
