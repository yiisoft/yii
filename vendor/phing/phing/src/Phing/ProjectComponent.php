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

use Phing\Parser\Location;

/**
 *  Abstract class providing properties and methods common to all
 *  the project components.
 *
 * @author Andreas Aderhold <andi@binarycloud.com>
 * @author Hans Lellelid <hans@xmpl.org>
 */
abstract class ProjectComponent
{
    /**
     * Holds a reference to the project that a project component
     * (a task, a target, etc.) belongs to.
     *
     * @var Project A reference to the current project instance
     */
    protected $project;

    /**
     * @var Location
     */
    private $location;

    /**
     * @var string
     */
    private $description;

    public function __construct()
    {
        $this->location = new Location();
    }

    /**
     * References the project to the current component.
     *
     * @param Project $project The reference to the current project
     */
    public function setProject($project)
    {
        $this->project = $project;
    }

    /**
     * Returns a reference to current project.
     *
     * @return Project Reference to current porject object
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Returns the file/location where this task was defined.
     *
     * @return Location the file/location where this task was defined.
     *                  Should not return <code>null</code>.
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Sets the file/location where this task was defined.
     *
     * @param Location $location The file/location where this task was defined.
     *                           Should not be <code>null</code>
     */
    public function setLocation(Location $location)
    {
        $this->location = $location;
    }

    /**
     * Sets a description of the current action. This may be used for logging
     * purposes.
     *
     * @param string $desc Description of the current action.
     *                     May be <code>null</code>, indicating that no description is
     *                     available.
     */
    public function setDescription($desc)
    {
        $this->description = $desc;
    }

    /**
     * Returns the description of the current action.
     *
     * @return string the description of the current action, or <code>null</code> if
     *                no description is available
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Logs a message with the given priority.
     *
     * @param string $msg   the message to be logged
     * @param int    $level The message's priority at this message should have
     */
    public function log($msg, $level = Project::MSG_INFO)
    {
        if (null !== $this->project) {
            $this->project->log($msg, $level);
        }
    }
}
