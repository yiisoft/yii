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

use Phing\Io\IOException;
use Phing\Io\Reader;

/**
 * Filter to flatten the stream to a single line.
 *
 * Example:
 *
 * <pre><striplinebreaks/></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.StripLineBreaks"/></pre>
 *
 * @author  <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @author  hans lellelid, hans@velum.net
 *
 * @see     BaseParamFilterReader
 */
class StripLineBreaks extends BaseParamFilterReader implements ChainableReader
{
    /**
     * Default line-breaking characters.
     *
     * @var string
     */
    public const DEFAULT_LINE_BREAKS = "\r\n";

    /**
     * Parameter name for the line-breaking characters parameter.
     *
     * @var string
     */
    public const LINES_BREAKS_KEY = 'linebreaks';

    /**
     * The characters that are recognized as line breaks.
     *
     * @var string
     */
    private $lineBreaks = "\r\n"; // self::DEFAULT_LINE_BREAKS;

    /**
     * Returns the filtered stream, only including
     * characters not in the set of line-breaking characters.
     *
     * @param int $len
     *
     * @throws IOException if the underlying stream throws an IOException
     *                     during reading
     *
     * @return mixed the resulting stream, or -1
     *               if the end of the resulting stream has been reached
     */
    public function read($len = null)
    {
        if (!$this->getInitialized()) {
            $this->initialize();
            $this->setInitialized(true);
        }

        $buffer = $this->in->read($len);
        if (-1 === $buffer) {
            return -1;
        }

        return preg_replace('/[' . $this->lineBreaks . ']/', '', $buffer);
    }

    /**
     * Sets the line-breaking characters.
     *
     * @param string $lineBreaks a String containing all the characters to be
     *                           considered as line-breaking
     */
    public function setLineBreaks($lineBreaks)
    {
        $this->lineBreaks = (string) $lineBreaks;
    }

    /**
     * Gets the line-breaking characters.
     *
     * @return string a String containing all the characters that are considered as line-breaking
     */
    public function getLineBreaks()
    {
        return $this->lineBreaks;
    }

    /**
     * Creates a new StripLineBreaks using the passed in
     * Reader for instantiation.
     *
     * @return StripLineBreaks A new filter based on this configuration, but filtering
     *                         the specified reader
     *
     * @internal param A $object Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     */
    public function chain(Reader $reader): Reader
    {
        $newFilter = new StripLineBreaks($reader);
        $newFilter->setLineBreaks($this->getLineBreaks());
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /**
     * Parses the parameters to set the line-breaking characters.
     */
    private function initialize()
    {
        $userDefinedLineBreaks = null;
        $params = $this->getParameters();
        if (null !== $params) {
            for ($i = 0, $paramsCount = count($params); $i < $paramsCount; ++$i) {
                if (self::LINES_BREAKS_KEY === $params[$i]->getName()) {
                    $userDefinedLineBreaks = $params[$i]->getValue();

                    break;
                }
            }
        }

        if (null !== $userDefinedLineBreaks) {
            $this->lineBreaks = $userDefinedLineBreaks;
        }
    }
}
