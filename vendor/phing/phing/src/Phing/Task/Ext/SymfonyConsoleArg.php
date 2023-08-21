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

namespace Phing\Task\Ext;

use Phing\Type\DataType;

/**
 * Implementation of console argument.
 *
 * @author  nuno costa <nuno@francodacosta.com>
 * @license GPL
 */
class SymfonyConsoleArg extends DataType
{
    private $name;
    private $value;
    private $quotes = false;

    /**
     * Transforms the argument object into a string, takes into consideration
     * the quotes and the argument value.
     *
     * @return string
     */
    public function __toString()
    {
        $name = '';
        $value = '';
        $quote = $this->getQuotes() ? '"' : '';

        if (null !== $this->getValue()) {
            $value = $quote . $this->getValue() . $quote;
        }

        if (null !== $this->getName()) {
            $name = '--' . $this->getName();
        }

        if (strlen($name) > 0 && strlen($value) > 0) {
            $value = '=' . $value;
        }

        return $name . $value;
    }

    /**
     * Gets the argument name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the argument name.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets the argument value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the argument value.
     *
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Should the argument value be enclosed in double quotes.
     *
     * @return bool
     */
    public function getQuotes()
    {
        return $this->quotes;
    }

    /**
     * Should the argument value be enclosed in double quotes.
     *
     * @param bool $quotes
     */
    public function setQuotes($quotes)
    {
        $this->quotes = $quotes;
    }
}
