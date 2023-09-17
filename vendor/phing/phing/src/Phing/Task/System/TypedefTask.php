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
use Phing\Task;
use Phing\Type\Element\ClasspathAware;

/**
 * Register a datatype for use within a buildfile.
 *
 * This is for registering your own datatypes for use within a buildfile.
 *
 * If you find that you are using a particular class frequently, you may want to edit the
 * phing/types/defaults.properties file so that it is included by default.  You may also
 * want to submit it (if LGPL or compatible license) to be included in Phing distribution.
 *
 * <pre>
 *   <typedef name="mytype" classname="path.to.MyHandlingClass"/>
 *   .
 *   <sometask ...>
 *     <mytype param1="val1" param2="val2"/>
 *   </sometask>
 * </pre>
 *
 * TODO:
 *    -- possibly refactor since this is almost the same as TaskDefTask
 *      (right now these are just too simple to really justify creating an abstract class)
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class TypedefTask extends Task
{
    use ClasspathAware;

    /**
     * Tag name for datatype that will be used in XML.
     */
    private $name;

    /**
     * Classname of task to register.
     *
     * @var string
     */
    private $classname;

    /**
     * Main entry point.
     */
    public function main()
    {
        if (null === $this->name || null === $this->classname) {
            throw new BuildException('You must specify name and class attributes for <typedef>.');
        }
        $this->project->addDataTypeDefinition($this->name, $this->classname, $this->classpath);
    }

    /**
     * Sets the name that will be used in XML buildfile.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Sets the class name to use.
     *
     * @param string $class
     */
    public function setClassname($class)
    {
        $this->classname = $class;
    }
}
