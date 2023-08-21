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

namespace Phing\Filter;

use Phing\Util\RegisterSlot;

/**
 * Class that holds an XSLT parameter.
 */
class XsltParam
{
    private $name;

    /**
     * @var RegisterSlot|string
     */
    private $expr;

    /**
     * Sets param name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get param name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets expression value (alias to the setExpression()) method.
     *
     * @param string $v
     *
     * @see   setExpression()
     */
    public function setValue($v)
    {
        $this->setExpression($v);
    }

    /**
     * Gets expression value (alias to the getExpression()) method.
     *
     * @return string
     *
     * @see    getExpression()
     */
    public function getValue()
    {
        return $this->getExpression();
    }

    /**
     * Sets expression value.
     *
     * @param string $expr
     */
    public function setExpression($expr)
    {
        $this->expr = $expr;
    }

    /**
     * Sets expression to dynamic register slot.
     */
    public function setListeningExpression(RegisterSlot $expr)
    {
        $this->expr = $expr;
    }

    /**
     * Returns expression value -- performs lookup if expr is registerslot.
     *
     * @return string
     */
    public function getExpression()
    {
        if ($this->expr instanceof RegisterSlot) {
            return $this->expr->getValue();
        }

        return $this->expr;
    }
}
