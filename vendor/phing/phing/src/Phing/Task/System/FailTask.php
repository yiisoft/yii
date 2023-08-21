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
use Phing\Exception\ExitStatusException;
use Phing\Project;
use Phing\Task;
use Phing\Task\System\Condition\NestedCondition;

/**
 * Exits the active build, giving an additional message
 * if available.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Nico Seessle <nico@seessle.de> (Ant)
 */
class FailTask extends Task
{
    /**
     * @var string
     */
    protected $message;

    /**
     * @var string
     */
    protected $ifCondition;

    /**
     * @var string
     */
    protected $unlessCondition;

    /**
     * @var NestedCondition
     */
    protected $nestedCondition;

    /**
     * @var int
     */
    protected $status;

    /**
     * A message giving further information on why the build exited.
     *
     * @param string $value message to output
     */
    public function setMsg($value)
    {
        $this->setMessage($value);
    }

    /**
     * A message giving further information on why the build exited.
     *
     * @param string $value message to output
     */
    public function setMessage($value)
    {
        $this->message = $value;
    }

    /**
     * Only fail if a property of the given name exists in the current project.
     *
     * @param string $c property name
     */
    public function setIf($c)
    {
        $this->ifCondition = $c;
    }

    /**
     * Only fail if a property of the given name does not
     * exist in the current project.
     *
     * @param string $c property name
     */
    public function setUnless($c)
    {
        $this->unlessCondition = $c;
    }

    /**
     * Set the status code to associate with the thrown Exception.
     *
     * @param int $int the <code>int</code> status
     */
    public function setStatus($int)
    {
        $this->status = (int) $int;
    }

    /**
     * {@inheritdoc}
     *
     * @throws BuildException
     */
    public function main()
    {
        $fail = $this->nestedConditionPresent() ? $this->testNestedCondition() :
            $this->testIfCondition() && $this->testUnlessCondition();

        if ($fail) {
            $text = null;
            if (null !== $this->message && strlen(trim($this->message)) > 0) {
                $text = trim($this->message);
            } else {
                if (null !== $this->ifCondition && '' !== $this->ifCondition && $this->testIfCondition()) {
                    $text = 'if=' . $this->ifCondition;
                }
                if (null !== $this->unlessCondition && '' !== $this->unlessCondition && $this->testUnlessCondition()) {
                    if (null === $text) {
                        $text = '';
                    } else {
                        $text .= ' and ';
                    }
                    $text .= 'unless=' . $this->unlessCondition;
                }
                if ($this->nestedConditionPresent()) {
                    $text = 'condition satisfied';
                } else {
                    if (null === $text) {
                        $text = 'No message';
                    }
                }
            }

            $this->log('failing due to ' . $text, Project::MSG_DEBUG);
            if (null === $this->status) {
                throw new BuildException($text);
            }

            throw new ExitStatusException($text, $this->status);
        }
    }

    /**
     * Add a condition element.
     *
     * @throws BuildException
     *
     * @return NestedCondition
     */
    public function createCondition()
    {
        if (null !== $this->nestedCondition) {
            throw new BuildException('Only one nested condition is allowed.');
        }
        $this->nestedCondition = new NestedCondition();

        return $this->nestedCondition;
    }

    /**
     * Set a multiline message.
     *
     * @param string $msg
     */
    public function addText($msg)
    {
        if (null === $this->message) {
            $this->message = '';
        }
        $this->message .= $this->project->replaceProperties($msg);
    }

    /**
     * @return bool
     */
    protected function testIfCondition()
    {
        if (null === $this->ifCondition || '' === $this->ifCondition) {
            return true;
        }

        return null !== $this->project->getProperty($this->ifCondition);
    }

    /**
     * @return bool
     */
    protected function testUnlessCondition()
    {
        if (null === $this->unlessCondition || '' === $this->unlessCondition) {
            return true;
        }

        return null === $this->project->getProperty($this->unlessCondition);
    }

    /**
     * test the nested condition.
     *
     * @throws BuildException
     *
     * @return bool true if there is none, or it evaluates to true
     */
    private function testNestedCondition()
    {
        $result = $this->nestedConditionPresent();

        if ($result && (null !== $this->ifCondition || null !== $this->unlessCondition)) {
            throw new BuildException('Nested conditions not permitted in conjunction with if/unless attributes');
        }

        return $result && $this->nestedCondition->evaluate();
    }

    /**
     * test whether there is a nested condition.
     *
     * @return bool
     */
    private function nestedConditionPresent()
    {
        return null !== $this->nestedCondition;
    }
}
