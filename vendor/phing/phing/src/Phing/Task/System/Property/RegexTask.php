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

namespace Phing\Task\System\Property;

use Exception;
use Phing\Exception\BuildException;
use Phing\Project;
use Phing\Util\Regexp;

/**
 * Regular Expression Task for properties.
 *
 * <pre>
 *   <propertyregex property="pack.name"
 *                  subject="package.ABC.name"
 *                  pattern="package\.([^.]*)\.name"
 *                  match="$1"
 *                  casesensitive="false"
 *                  defaultvalue="test1"/>
 *
 *   <echo message="${pack.name}"/>
 *
 *   <propertyregex property="pack.name"
 *                  override="true"
 *                  subject="package.ABC.name"
 *                  pattern="(package)\.[^.]*\.(name)"
 *                  replace="$1.DEF.$2"
 *                  casesensitive="false"
 *                  defaultvalue="test2"/>
 *
 *   <echo message="${pack.name}"/>
 *
 * </pre>
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class RegexTask extends AbstractPropertySetterTask
{
    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $pattern;

    /**
     * @var string
     */
    private $match;

    /**
     * @var string
     */
    private $replace;

    /**
     * @var string
     */
    private $defaultValue;

    /**
     * @var bool
     */
    private $caseSensitive = true;

    /**
     * @var array
     */
    private $modifiers = '';

    /**
     * @var Regexp
     */
    private $reg;

    /**
     * @var int
     */
    private $limit = -1;

    public function init()
    {
        $this->reg = new Regexp();
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @param string $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->log('Set default value to ' . $defaultValue, Project::MSG_DEBUG);

        $this->defaultValue = $defaultValue;
    }

    /**
     * @param string $pattern
     *
     * @throws BuildException
     */
    public function setPattern($pattern)
    {
        if (null !== $this->pattern) {
            throw new BuildException(
                'Cannot specify more than one regular expression'
            );
        }

        $this->log('Set pattern to ' . $pattern, Project::MSG_DEBUG);

        $this->pattern = $pattern;
    }

    /**
     * @param string $replace
     *
     * @throws BuildException
     */
    public function setReplace($replace)
    {
        if (null !== $this->replace) {
            throw new BuildException(
                'Cannot specify more than one replace expression'
            );
        }
        if (null !== $this->match) {
            throw new BuildException(
                'You cannot specify both a select and replace expression'
            );
        }

        $this->log('Set replace to ' . $replace, Project::MSG_DEBUG);

        $this->replace = $replace;
    }

    /**
     * @param string $match
     *
     * @throws BuildException
     */
    public function setMatch($match)
    {
        if (null !== $this->match) {
            throw new BuildException(
                'Cannot specify more than one match expression'
            );
        }

        $this->log('Set match to ' . $match, Project::MSG_DEBUG);

        $this->match = $match;
    }

    /**
     * @param bool $caseSensitive
     */
    public function setCaseSensitive($caseSensitive)
    {
        $this->log("Set case-sensitive to {$caseSensitive}", Project::MSG_DEBUG);

        $this->caseSensitive = $caseSensitive;
    }

    /**
     * @throws BuildException
     */
    public function main()
    {
        $this->validate();

        $output = $this->match;

        if (null !== $this->replace) {
            $output = $this->doReplace();
        } else {
            $output = $this->doSelect();
        }

        if (null !== $output) {
            $this->setPropertyValue($output);
        }
    }

    /**
     * @throws BuildException
     *
     * @return mixed|string
     */
    protected function doReplace()
    {
        if (null === $this->replace) {
            throw new BuildException('No replace expression specified.');
        }
        $this->reg->setPattern($this->pattern);
        $this->reg->setReplace($this->replace);
        $this->reg->setModifiers($this->modifiers);
        $this->reg->setIgnoreCase(!$this->caseSensitive);
        $this->reg->setLimit($this->limit);

        try {
            $output = $this->reg->replace($this->subject);
        } catch (Exception $e) {
            $output = $this->defaultValue;
        }

        return $output;
    }

    /**
     * @throws BuildException
     *
     * @return string
     */
    protected function doSelect()
    {
        $this->reg->setPattern($this->pattern);
        $this->reg->setModifiers($this->modifiers);
        $this->reg->setIgnoreCase(!$this->caseSensitive);

        $output = $this->defaultValue;

        try {
            if ($this->reg->matches($this->subject)) {
                $output = $this->reg->getGroup((int) ltrim($this->match, '$'));
            }
        } catch (Exception $e) {
            throw new BuildException($e);
        }

        return $output;
    }

    /**
     * @throws BuildException
     */
    protected function validate()
    {
        if (null === $this->pattern) {
            throw new BuildException('No match expression specified.');
        }
        if (null === $this->replace && null === $this->match) {
            throw new BuildException(
                'You must specify either a preg_replace or preg_match pattern'
            );
        }
    }
}
