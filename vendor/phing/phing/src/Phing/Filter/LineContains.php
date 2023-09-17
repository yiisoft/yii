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

use Exception;
use Phing\Io\IOException;
use Phing\Io\Reader;
use Phing\Project;
use Phing\Type\FilterReader;
use Phing\Util\StringHelper;

/**
 * Filter which includes only those lines that contain all the user-specified
 * strings.
 *
 * Example:
 *
 * <pre><linecontains>
 *   <contains value="foo">
 *   <contains value="bar">
 * </linecontains></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.LineContains">
 *    <param type="contains" value="foo"/>
 *    <param type="contains" value="bar"/>
 * </filterreader></pre>
 *
 * This will include only those lines that contain <code>foo</code> and
 * <code>bar</code>.
 *
 * @author  Yannick Lecaillez <yl@seasonfive.com>
 * @author  Hans Lellelid <hans@velum.net>
 *
 * @see     FilterReader
 */
class LineContains extends BaseParamFilterReader implements ChainableReader
{
    /**
     * The parameter name for the string to match on.
     */
    private const CONTAINS_KEY = 'contains';

    /**
     * The parameter name for the string to match on.
     */
    private const NEGATE_KEY = 'negate';

    /**
     * Array of Contains objects.
     *
     * @var array
     */
    private $contains = [];

    /**
     * @var bool
     */
    private $negate = false;

    /**
     * Remaining line to be read from this filter, or <code>null</code> if
     * the next call to <code>read()</code> should read the original stream
     * to find the next matching line.
     */
    private $line;

    private $matchAny = false;

    /**
     * Remaining line to be read from this filter, or <code>null</code> if
     * the next call to <code>read()</code> should read the original stream
     * to find the next matching line.
     *
     * @param int|int $len
     *
     * @throws IOException if the underlying stream throws an IOException during reading
     *
     * @return int|string the next character in the resulting stream, or -1
     *                    if the end of the resulting stream has been reached
     */
    public function read($len = null)
    {
        if (!$this->getInitialized()) {
            $this->initialize();
            $this->setInitialized(true);
        }

        $ch = -1;

        if (null !== $this->line) {
            $ch = $this->line[0] ?? -1;
            if (1 === strlen($this->line)) {
                $this->line = null;
            } else {
                $part = substr($this->line, 1);

                $this->line = false !== $part ? $part : null;
            }
        } else {
            for ($this->line = $this->readLine(); null !== $this->line; $this->line = $this->readLine()) {
                $matches = true;
                foreach ($this->contains as $iValue) {
                    $containsStr = $iValue->getValue();
                    if ('' === $containsStr) {
                        $this->log(
                            'The value of <contents> is evaluated to an empty string.',
                            Project::MSG_DEBUG
                        );
                        $matches = false;
                    } else {
                        $matches = false !== strpos($this->line, $containsStr);
                    }
                    if (!$matches) {
                        if ($this->matchAny) {
                            continue;
                        }

                        break;
                    }

                    if ($this->matchAny) {
                        break;
                    }
                }
                if ($matches ^ $this->isNegated()) {
                    break;
                }
            }
            if (null !== $this->line) {
                return $this->read();
            }
        }

        return $ch;
    }

    /**
     * Set the negation mode.  Default false (no negation).
     *
     * @param bool $b the bool negation mode to set
     */
    public function setNegate(bool $b)
    {
        $this->negate = $b;
    }

    /**
     * Find out whether we have been negated.
     *
     * @return bool negation flag
     */
    public function isNegated(): bool
    {
        return $this->negate;
    }

    /**
     * Adds a <code>contains</code> nested element.
     *
     * @return Contains The <code>contains</code> element added.
     *                  Must not be <code>null</code>.
     */
    public function createContains(): Contains
    {
        $num = array_push($this->contains, new Contains());

        return $this->contains[$num - 1];
    }

    /**
     * Returns the vector of words which must be contained within a line read
     * from the original stream in order for it to match this filter.
     *
     * @return array The array of words which must be contained within a line read
     *               from the original stream in order for it to match this filter. The
     *               returned object is "live" - in other words, changes made to the
     *               returned object are mirrored in the filter.
     */
    public function getContains(): array
    {
        return $this->contains;
    }

    /**
     * Creates a new LineContains using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader A Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @throws Exception
     *
     * @return LineContains A new filter based on this configuration, but filtering
     *                      the specified reader
     */
    public function chain(Reader $reader): Reader
    {
        $newFilter = new self($reader);
        $newFilter->setContains($this->getContains());
        $newFilter->setNegate($this->isNegated());
        $newFilter->setMatchAny($this->isMatchAny());
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    public function setMatchAny(bool $matchAny): void
    {
        $this->matchAny = $matchAny;
    }

    public function isMatchAny(): bool
    {
        return $this->matchAny;
    }

    /**
     * Sets the array of words which must be contained within a line read
     * from the original stream in order for it to match this filter.
     *
     * @param array $contains An array of words which must be contained
     *                        within a line in order for it to match in this filter.
     *                        Must not be <code>null</code>.
     *
     * @throws Exception
     */
    private function setContains(array $contains)
    {
        $this->contains = $contains;
    }

    /**
     * Parses the parameters to add user-defined contains strings.
     */
    private function initialize()
    {
        $params = $this->getParameters();
        if (null !== $params) {
            foreach ($params as $param) {
                if (self::CONTAINS_KEY === $param->getType()) {
                    $cont = new Contains();
                    $cont->setValue($param->getValue());
                    $this->contains[] = $cont;
                } elseif (self::NEGATE_KEY === $param->getType()) {
                    $this->setNegate(StringHelper::booleanValue($param->getValue()));
                }
            }
        }
    }
}
