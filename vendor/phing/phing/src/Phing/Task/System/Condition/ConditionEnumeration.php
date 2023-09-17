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

use Iterator;
use Phing\ProjectComponent;

/**
 * "Inner" class for handling enumerations.
 * Uses build-in PHP5 iterator support.
 */
class ConditionEnumeration implements Iterator
{
    /**
     * Current element number.
     */
    private $num = 0;

    /**
     * "Outer" ConditionBase class.
     */
    private $outer;

    public function __construct(ConditionBase $outer)
    {
        $this->outer = $outer;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->outer->countConditions() > $this->num;
    }

    public function current()
    {
        $o = $this->outer->conditions[$this->num];
        if ($o instanceof ProjectComponent) {
            $o->setProject($this->outer->getProject());
        }

        return $o;
    }

    public function next()
    {
        ++$this->num;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->num;
    }

    public function rewind()
    {
        $this->num = 0;
    }
}
