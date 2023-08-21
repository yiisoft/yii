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

use Phing\Exception\BuildException;
use Phing\ProjectComponent;

/**
 * Condition that tests whether a given string evals to true.
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Steve Loughran (Ant)
 */
class IsTrueCondition extends ProjectComponent implements Condition
{
    /**
     * what we eval.
     */
    private $value;

    /**
     * Set the value to be tested.
     *
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = (bool) $value;
    }

    /**
     * return the inverted value;.
     *
     * @throws BuildException if someone forgot to spec a value
     */
    public function evaluate()
    {
        if (null === $this->value) {
            throw new BuildException('Nothing to test for falsehood');
        }

        return $this->value;
    }
}
