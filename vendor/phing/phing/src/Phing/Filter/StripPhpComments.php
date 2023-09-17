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

use Phing\Io\FilterReader;
use Phing\Io\IOException;
use Phing\Io\Reader;

/**
 * This is a Php comment and string stripper reader that filters
 * those lexical tokens out for purposes of simple Php parsing.
 * (if you have more complex Php parsing needs, use a real lexer).
 * Since this class heavily relies on the single char read function,
 * you are recommended to make it work on top of a buffered reader.
 *
 * @author  <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @author  hans lellelid, hans@velum.net
 *
 * @see     FilterReader
 */
class StripPhpComments extends BaseFilterReader implements ChainableReader
{
    /**
     * Returns the  stream without Php comments.
     *
     * @param null|int $len
     *
     * @throws IOException
     *
     * @return string the resulting stream, or -1
     *                if the end of the resulting stream has been reached
     */
    public function read($len = null)
    {
        $buffer = $this->in->read($len);
        if (-1 === $buffer) {
            return -1;
        }
        $newStr = '';
        $tokens = token_get_all($buffer);

        foreach ($tokens as $token) {
            if (is_array($token)) {
                [$id, $text] = $token;

                switch ($id) {
                    case T_COMMENT:
                    case T_DOC_COMMENT:
                        // no action on comments
                        continue 2;

                    default:
                        $newStr .= $text;

                        continue 2;
                }
            }
            $newStr .= $token;
        }

        return $newStr;
    }

    /**
     * Creates a new StripPhpComments using the passed in
     * Reader for instantiation.
     *
     * @param A|Reader $reader
     *
     * @return $this a new filter based on this configuration, but filtering
     *               the specified reader
     *
     * @internal param A $reader Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     */
    public function chain(Reader $reader): Reader
    {
        $newFilter = new StripPhpComments($reader);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }
}
