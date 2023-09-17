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
 * Converts tabs to spaces.
 *
 * Example:
 *
 * <pre><tabtospaces tablength="8"></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.TabsToSpaces">
 *   <param name="tablength" value="8">
 * </filterreader></pre>
 *
 * @author  Yannick Lecaillez <yl@seasonfive.com>
 * @author  Hans Lellelid <hans@xmpl.org>
 *
 * @see     BaseParamFilterReader
 */
class TabToSpaces extends BaseParamFilterReader implements ChainableReader
{
    /**
     * The default tab length.
     *
     * @var int
     */
    public const DEFAULT_TAB_LENGTH = 8;

    /**
     * Parameter name for the length of a tab.
     *
     * @var string
     */
    public const TAB_LENGTH_KEY = 'tablength';

    /**
     * Tab length in this filter.
     *
     * @var int
     */
    private $tabLength = 8; //self::DEFAULT_TAB_LENGTH;

    /**
     * Returns stream after converting tabs to the specified number of spaces.
     *
     * @param int $len
     *
     * @throws IOException if the underlying stream throws an IOException
     *                     during reading
     *
     * @return int the resulting stream, or -1
     *             if the end of the resulting stream has been reached
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

        return str_replace("\t", str_repeat(' ', $this->tabLength), $buffer);
    }

    /**
     * Sets the tab length.
     *
     * @param int $tabLength the number of spaces to be used when converting a tab
     */
    public function setTablength($tabLength)
    {
        $this->tabLength = (int) $tabLength;
    }

    /**
     * Returns the tab length.
     *
     * @return int The number of spaces used when converting a tab
     */
    public function getTablength()
    {
        return $this->tabLength;
    }

    /**
     * Creates a new TabsToSpaces using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader A Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @return TabToSpaces A new filter based on this configuration, but filtering
     *                     the specified reader
     */
    public function chain(Reader $reader): Reader
    {
        $newFilter = new TabToSpaces($reader);
        $newFilter->setTablength($this->getTablength());
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /**
     * Parses the parameters to set the tab length.
     */
    private function initialize()
    {
        $params = $this->getParameters();
        if (null !== $params) {
            for ($i = 0, $paramsCount = count($params); $i < $paramsCount; ++$i) {
                if (self::TAB_LENGTH_KEY === $params[$i]->getName()) {
                    $this->tabLength = $params[$i]->getValue();

                    break;
                }
            }
        }
    }
}
