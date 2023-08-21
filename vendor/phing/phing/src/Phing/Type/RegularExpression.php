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

namespace Phing\Type;

use Phing\Exception\BuildException;
use Phing\Phing;
use Phing\Project;
use Phing\Util\Regexp;
use Phing\Util\StringHelper;

/**
 * A regular expression datatype.  Keeps an instance of the
 * compiled expression for speed purposes.  This compiled
 * expression is lazily evaluated (it is compiled the first
 * time it is needed).  The syntax is the dependent on which
 * regular expression type you are using.
 *
 * @author  <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 *
 * @see     phing.util.regex.RegexMatcher
 */
class RegularExpression extends DataType
{
    /**
     * @var Regexp
     */
    private $regexp;

    public function __construct()
    {
        parent::__construct();
        $this->regexp = new Regexp();
    }

    /**
     * @param string $pattern
     */
    public function setPattern($pattern)
    {
        $this->regexp->setPattern($pattern);
    }

    /**
     * @param string $replace
     */
    public function setReplace($replace)
    {
        $this->regexp->setReplace($replace);
    }

    /**
     * @param Project $p
     *
     * @throws BuildException
     *
     * @return string
     */
    public function getPattern($p)
    {
        if ($this->isReference()) {
            $ref = $this->getRef($p);

            return $ref->getPattern($p);
        }

        return $this->regexp->getPattern();
    }

    /**
     * @param Project $p
     *
     * @throws BuildException
     *
     * @return string
     */
    public function getReplace($p)
    {
        if ($this->isReference()) {
            $ref = $this->getRef($p);

            return $ref->getReplace($p);
        }

        return $this->regexp->getReplace();
    }

    /**
     * @param string $modifiers
     */
    public function setModifiers($modifiers)
    {
        $this->regexp->setModifiers($modifiers);
    }

    /**
     * @return string
     */
    public function getModifiers()
    {
        return $this->regexp->getModifiers();
    }

    /**
     * @param bool $bit
     */
    public function setIgnoreCase($bit)
    {
        $this->regexp->setIgnoreCase($bit);
    }

    /**
     * @return bool
     */
    public function getIgnoreCase()
    {
        return $this->regexp->getIgnoreCase();
    }

    /**
     * @param bool $multiline
     */
    public function setMultiline($multiline)
    {
        $this->regexp->setMultiline($multiline);
    }

    /**
     * @return bool
     */
    public function getMultiline()
    {
        return $this->regexp->getMultiline();
    }

    /**
     * @throws BuildException
     *
     * @return null|Regexp
     */
    public function getRegexp(Project $p)
    {
        if ($this->isReference()) {
            $ref = $this->getRef($p);

            return $ref->getRegexp($p);
        }

        return $this->regexp;
    }

    /**
     * @throws BuildException
     */
    public function getRef(Project $p)
    {
        $dataTypeName = StringHelper::substring(__CLASS__, strrpos(__CLASS__, '\\') + 1);

        return $this->getCheckedRef(__CLASS__, $dataTypeName);
    }
}
