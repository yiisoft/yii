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

namespace Phing\Task\System\Condition;

use IteratorAggregate;
use Phing\Exception\BuildException;
use Phing\Parser\CustomChildCreator;
use Phing\Project;
use Phing\ProjectComponent;
use Phing\Task\System\AvailableTask;
use Phing\Task\System\Pdo\PDOSQLExecTask;

/**
 * Abstract baseclass for the <condition> task as well as several
 * conditions - ensures that the types of conditions inside the task
 * and the "container" conditions are in sync.
 *
 * @author    Hans Lellelid <hans@xmpl.org>
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 */
abstract class ConditionBase extends ProjectComponent implements IteratorAggregate, CustomChildCreator
{
    public $conditions = []; // needs to be public for "inner" class access

    /**
     * @var string
     */
    private $taskName = 'condition';

    public function __construct($taskName = 'component')
    {
        parent::__construct();
        $this->taskName = $taskName;
    }

    /**
     * Sets the name to use in logging messages.
     *
     * @param string $name The name to use in logging messages.
     *                     Should not be <code>null</code>.
     */
    public function setTaskName($name)
    {
        $this->taskName = $name;
    }

    /**
     * Returns the name to use in logging messages.
     *
     * @return string the name to use in logging messages
     */
    public function getTaskName()
    {
        return $this->taskName;
    }

    /**
     * @return int
     */
    public function countConditions()
    {
        return count($this->conditions);
    }

    /**
     * Required for \IteratorAggregate.
     */
    public function getIterator(): ConditionEnumeration
    {
        return new ConditionEnumeration($this);
    }

    /**
     * @return Condition[]
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    public function addAvailable(AvailableTask $a)
    {
        $this->conditions[] = $a;
    }

    /**
     * @return NotCondition
     */
    public function createNot()
    {
        $num = array_push($this->conditions, new NotCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return AndCondition
     */
    public function createAnd()
    {
        $num = array_push($this->conditions, new AndCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return OrCondition
     */
    public function createOr()
    {
        $num = array_push($this->conditions, new OrCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return XorCondition
     */
    public function createXor()
    {
        $num = array_push($this->conditions, new XorCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return EqualsCondition
     */
    public function createEquals()
    {
        $num = array_push($this->conditions, new EqualsCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return OsCondition
     */
    public function createOs()
    {
        $num = array_push($this->conditions, new OsCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsFalseCondition
     */
    public function createIsFalse()
    {
        $num = array_push($this->conditions, new IsFalseCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsTrueCondition
     */
    public function createIsTrue()
    {
        $num = array_push($this->conditions, new IsTrueCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsPropertyFalseCondition
     */
    public function createIsPropertyFalse()
    {
        $num = array_push($this->conditions, new IsPropertyFalseCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsPropertyTrueCondition
     */
    public function createIsPropertyTrue()
    {
        $num = array_push($this->conditions, new IsPropertyTrueCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return ContainsCondition
     */
    public function createContains()
    {
        $num = array_push($this->conditions, new ContainsCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return IsSetCondition
     */
    public function createIsSet()
    {
        $num = array_push($this->conditions, new IsSetCondition());

        return $this->conditions[$num - 1];
    }

    /**
     * @return ReferenceExistsCondition
     */
    public function createReferenceExists()
    {
        $num = array_push($this->conditions, new ReferenceExistsCondition());

        return $this->conditions[$num - 1];
    }

    public function createVersionCompare()
    {
        $num = array_push($this->conditions, new VersionCompareCondition());

        return $this->conditions[$num - 1];
    }

    public function createHttp()
    {
        $num = array_push($this->conditions, new HttpCondition());

        return $this->conditions[$num - 1];
    }

    public function createPhingVersion()
    {
        $num = array_push($this->conditions, new PhingVersion());

        return $this->conditions[$num - 1];
    }

    public function createHasFreeSpace()
    {
        $num = array_push($this->conditions, new HasFreeSpaceCondition());

        return $this->conditions[$num - 1];
    }

    public function createFilesMatch()
    {
        $num = array_push($this->conditions, new FilesMatch());

        return $this->conditions[$num - 1];
    }

    public function createSocket()
    {
        $num = array_push($this->conditions, new SocketCondition());

        return $this->conditions[$num - 1];
    }

    public function createIsFailure()
    {
        $num = array_push($this->conditions, new IsFailure());

        return $this->conditions[$num - 1];
    }

    public function createIsFileSelected()
    {
        $num = array_push($this->conditions, new IsFileSelected());

        return $this->conditions[$num - 1];
    }

    public function createMatches()
    {
        $num = array_push($this->conditions, new Matches());

        return $this->conditions[$num - 1];
    }

    public function createPdoSqlExec()
    {
        $num = array_push($this->conditions, new PDOSQLExecTask());

        return $this->conditions[$num - 1];
    }

    /**
     * @param string $elementName
     *
     * @throws BuildException
     *
     * @return Condition
     */
    public function customChildCreator($elementName, Project $project)
    {
        $condition = $project->createCondition($elementName);
        $num = array_push($this->conditions, $condition);

        return $this->conditions[$num - 1];
    }
}
