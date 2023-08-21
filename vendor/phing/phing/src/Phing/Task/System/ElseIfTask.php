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
use Phing\Task\System\Condition\ConditionBase;

/**
 * "Inner" class for IfTask.
 * This class has same basic structure as the IfTask, although of course it doesn't support <else> tags.
 */
class ElseIfTask extends ConditionBase
{
    private $thenTasks;

    /**
     * @throws BuildException
     */
    public function addThen(SequentialTask $t)
    {
        if (null != $this->thenTasks) {
            throw new BuildException('You must not nest more than one <then> into <elseif>');
        }
        $this->thenTasks = $t;
    }

    /**
     * @throws BuildException
     *
     * @return bool
     */
    public function evaluate()
    {
        if ($this->countConditions() > 1) {
            throw new BuildException('You must not nest more than one condition into <elseif>');
        }
        if ($this->countConditions() < 1) {
            throw new BuildException('You must nest a condition into <elseif>');
        }

        $conditions = $this->getConditions();
        $c = $conditions[0];

        return $c->evaluate();
    }

    public function main()
    {
        if (null != $this->thenTasks) {
            $this->thenTasks->main();
        }
    }
}
