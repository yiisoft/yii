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
use Phing\Type\RegularExpression;

/**
 * Simple regular expression condition.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class Matches extends ProjectComponent implements Condition
{
    /**
     * @var string
     */
    private $string;

    /**
     * @var RegularExpression
     */
    private $regularExpression;

    /**
     * @var bool
     */
    private $multiLine = false;

    /**
     * @var bool
     */
    private $caseSensitive = true;

    /**
     * @var string
     */
    private $modifiers;

    /**
     * @param bool $caseSensitive
     */
    public function setCaseSensitive($caseSensitive)
    {
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Whether to match should be multiline.
     *
     * @param bool $multiLine
     */
    public function setMultiLine($multiLine)
    {
        $this->multiLine = $multiLine;
    }

    /**
     * @param string $pattern
     *
     * @throws BuildException
     */
    public function setPattern($pattern)
    {
        if (null !== $this->regularExpression) {
            throw new BuildException('Only one regular expression is allowed.');
        }
        $this->regularExpression = new RegularExpression();
        $this->regularExpression->setPattern($pattern);
    }

    /**
     * The string to match.
     *
     * @param string $string
     */
    public function setString($string)
    {
        $this->string = $string;
    }

    /**
     * @param string $modifiers
     */
    public function setModifiers($modifiers)
    {
        $this->modifiers = $modifiers;
    }

    public function evaluate()
    {
        if (null === $this->string) {
            throw new BuildException('Parameter string is required in matches.');
        }
        if (null === $this->regularExpression) {
            throw new BuildException('Missing pattern in matches.');
        }
        $this->regularExpression->setMultiline($this->multiLine);
        $this->regularExpression->setIgnoreCase(!$this->caseSensitive);
        $this->regularExpression->setModifiers($this->modifiers);
        $regexp = $this->regularExpression->getRegexp($this->getProject());

        return $regexp->matches($this->string);
    }
}
