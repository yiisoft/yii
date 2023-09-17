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

namespace Phing\Util;

use Phing\Exception\BuildException;

/**
 * A factory class for regex functions.
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class Regexp
{
    /**
     * Matching groups found.
     *
     * @var array
     */
    private $groups = [];

    /**
     * Pattern to match.
     *
     * @var string
     */
    private $pattern;

    /**
     * Replacement pattern.
     *
     * @var string
     */
    private $replace;

    /**
     * The regex engine -- e.g. 'preg' or 'ereg';.
     *
     * @var PregEngine
     */
    private $engine;

    /**
     * Constructor sets the regex engine to use (preg by default).
     *
     * @param string $engineType
     *
     * @throws BuildException
     */
    public function __construct($engineType = 'preg')
    {
        if ('preg' == $engineType) {
            $this->engine = new PregEngine();
        } else {
            throw new BuildException('Invalid engine type for Regexp: ' . $engineType);
        }
    }

    /**
     * Sets pattern to use for matching.
     *
     * @param string $pat the pattern to match on
     */
    public function setPattern($pat)
    {
        $this->pattern = (string) $pat;
    }

    /**
     * Gets pattern to use for matching.
     *
     * @return string the pattern to match on
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Sets replacement string.
     *
     * @param string $rep the pattern to replace matches with
     */
    public function setReplace($rep)
    {
        $this->replace = (string) $rep;
    }

    /**
     * Gets replacement string.
     *
     * @return string the pattern to replace matches with
     */
    public function getReplace()
    {
        return $this->replace;
    }

    /**
     * Performs match of specified pattern against $subject.
     *
     * @param string $subject the subject, on which to perform matches
     *
     * @throws RegexpException
     *
     * @return bool whether or not pattern matches subject string passed
     */
    public function matches($subject)
    {
        if (null === $this->pattern) {
            throw new RegexpException('No pattern specified for regexp match().');
        }

        return $this->engine->match($this->pattern, $subject, $this->groups);
    }

    /**
     * Performs replacement of specified pattern and replacement strings.
     *
     * @param string $subject text on which to perform replacement
     *
     * @throws RegexpException
     *
     * @return string subject after replacement has been performed
     */
    public function replace($subject)
    {
        if (null === $this->pattern || null === $this->replace) {
            throw new RegexpException('Missing pattern or replacement string regexp replace().');
        }

        return $this->engine->replace($this->pattern, $this->replace, $subject);
    }

    /**
     * Get array of matched groups.
     *
     * @return array Matched groups
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Get specific matched group.
     *
     * @param int $idx
     *
     * @return string specified group or NULL if group is not set
     */
    public function getGroup($idx)
    {
        if (!isset($this->groups[$idx])) {
            return null;
        }

        return $this->groups[$idx];
    }

    /**
     * Sets pattern modifiers for regex engine.
     *
     * @param string $mods Modifiers to be applied to a given regex
     */
    public function setModifiers($mods)
    {
        $this->engine->setModifiers($mods);
    }

    /**
     * Gets pattern modifiers.
     * Subsequent call to engines getModifiers() filters out duplicates
     * i.e. if i is provided in $mods, and setIgnoreCase(true), "i"
     * modifier would be included only once.
     *
     * @return string
     */
    public function getModifiers()
    {
        return $this->engine->getModifiers();
    }

    /**
     * Sets whether the regexp matching is case insensitive.
     * (default is false -- i.e. case sensisitive).
     *
     * @param bool $bit
     */
    public function setIgnoreCase($bit)
    {
        $this->engine->setIgnoreCase($bit);
    }

    /**
     * Gets whether the regexp matching is case insensitive.
     *
     * @return bool
     */
    public function getIgnoreCase()
    {
        return $this->engine->getIgnoreCase();
    }

    /**
     * Sets whether regexp should be applied in multiline mode.
     *
     * @param bool $bit
     */
    public function setMultiline($bit)
    {
        $this->engine->setMultiline($bit);
    }

    /**
     * Gets whether regexp is to be applied in multiline mode.
     *
     * @return bool
     */
    public function getMultiline()
    {
        return $this->engine->getMultiline();
    }

    /**
     * Sets the maximum possible replacements for each pattern.
     *
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->engine->setLimit($limit);
    }
}
