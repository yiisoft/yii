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

use Phing\Project;

/**
 * "Internal" class for holding an include/exclude pattern.
 */
class PatternSetNameEntry
{
    /**
     * The pattern.
     *
     * @var string
     */
    private $name;

    /**
     * The if-condition property for this pattern to be applied.
     *
     * @var string
     */
    private $ifCond;

    /**
     * The unless-condition property for this pattern to be applied.
     *
     * @var string
     */
    private $unlessCond;

    /**
     * Gets a string representation of this pattern.
     *
     * @return string
     */
    public function __toString()
    {
        $buf = $this->name;
        if ((null !== $this->ifCond) || (null !== $this->unlessCond)) {
            $buf .= ':';
            $connector = '';

            if (null !== $this->ifCond) {
                $buf .= "if->{$this->ifCond}";
                $connector = ';';
            }
            if (null !== $this->unlessCond) {
                $buf .= "{$connector} unless->{$this->unlessCond}";
            }
        }

        return $buf;
    }

    /**
     * An alias for the setName() method.
     *
     * @param string $pattern
     *
     * @see   setName()
     */
    public function setPattern($pattern)
    {
        $this->setName($pattern);
    }

    /**
     * Set the pattern text.
     *
     * @param string $name The pattern
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * Sets an if-condition property for this pattern to match.
     *
     * @param string $cond
     */
    public function setIf($cond)
    {
        $this->ifCond = (string) $cond;
    }

    /**
     * Sets an unless-condition property for this pattern to match.
     *
     * @param string $cond
     */
    public function setUnless($cond)
    {
        $this->unlessCond = (string) $cond;
    }

    /**
     * Get the pattern text.
     *
     * @return string the pattern
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Evaluates the pattern.
     *
     * @return string the pattern or null if it is ruled out by a condition
     */
    public function evalName(Project $project)
    {
        return $this->valid($project) ? $this->name : null;
    }

    /**
     * Checks whether pattern should be applied based on whether the if and unless
     * properties are set in project.
     *
     * @return bool
     */
    public function valid(Project $project)
    {
        if (null !== $this->ifCond && null === $project->getProperty($this->ifCond)) {
            return false;
        }

        if (null !== $this->unlessCond && null !== $project->getProperty($this->unlessCond)) {
            return false;
        }

        return true;
    }
}
