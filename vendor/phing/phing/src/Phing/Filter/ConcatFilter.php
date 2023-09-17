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

use Phing\Exception\BuildException;
use Phing\Io\BufferedReader;
use Phing\Io\File;
use Phing\Io\FileReader;
use Phing\Io\IOException;
use Phing\Io\Reader;
use Phing\Type\Parameter;

/**
 * Concats a file before and/or after the file.
 *
 * Example:
 * ```
 * <copy todir="build">
 *     <fileset dir="src" includes="*.php"/>
 *     <filterchain>
 *         <concatfilter prepend="license.txt"/>
 *     </filterchain>
 * </copy>
 * ```
 *
 * Copies all php sources from `src` to `build` and adds the
 * content of `license.txt` add the beginning of each
 * file.
 *
 * @author  Siad.ardroumli <siad.ardroumli@gmail.com>
 */
class ConcatFilter extends BaseParamFilterReader implements ChainableReader
{
    /**
     * File to add before the content.
     *
     * @var File
     */
    private $prepend;

    /**
     * File to add after the content.
     *
     * @var File|string
     */
    private $append;

    /**
     * Reader for prepend-file.
     *
     * @var BufferedReader
     */
    private $prependReader;

    /**
     * Reader for append-file.
     *
     * @var BufferedReader
     */
    private $appendReader;

    /**
     * @param Reader $in
     */
    public function __construct(Reader $in = null)
    {
        parent::__construct($in);
    }

    /**
     * Returns the next character in the filtered stream. If the desired
     * number of lines have already been read, the resulting stream is
     * effectively at an end. Otherwise, the next character from the
     * underlying stream is read and returned.
     *
     * @param int $len
     *
     * @throws IOException               if the underlying stream throws an IOException
     *                                   during reading
     * @throws BuildException
     * @throws \InvalidArgumentException
     *
     * @return int|string the next character in the resulting stream, or -1
     *                    if the end of the resulting stream has been reached
     */
    public function read($len = null)
    {
        // do the "singleton" initialization
        if (!$this->getInitialized()) {
            $this->initialize();
            $this->setInitialized(true);
        }

        $ch = -1;

        // The readers return -1 if they end. So simply read the "prepend"
        // after that the "content" and at the end the "append" file.
        if (null !== $this->prependReader) {
            $ch = $this->prependReader->read();
            if (-1 === $ch) {
                // I am the only one so I have to close the reader
                $this->prependReader->close();
                $this->prependReader = null;
            }
        }
        if (-1 === $ch) {
            $ch = parent::read();
        }
        if (-1 === $ch && null !== $this->appendReader) {
            $ch = $this->appendReader->read();
            if (-1 === $ch) {
                // I am the only one so I have to close the reader
                $this->appendReader->close();
                $this->appendReader = null;
            }
        }

        return $ch;
    }

    /**
     * Creates a new ConcatReader using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader A Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @throws IOException
     * @throws \InvalidArgumentException
     *
     * @return ConcatFilter a new filter based on this configuration, but filtering
     *                      the specified reader
     */
    public function chain(Reader $reader): Reader
    {
        $newFilter = new ConcatFilter($reader);
        $newFilter->setProject($this->getProject());
        $newFilter->setPrepend($this->getPrepend());
        $newFilter->setAppend($this->getAppend());

        return $newFilter;
    }

    /**
     * Returns `prepend` attribute.
     *
     * @return File prepend attribute
     */
    public function getPrepend()
    {
        return $this->prepend;
    }

    /**
     * Sets `prepend` attribute.
     *
     * @param File|string $prepend prepend new value
     *
     * @throws IOException
     * @throws \InvalidArgumentException
     */
    public function setPrepend($prepend)
    {
        if ($prepend instanceof File) {
            $this->prepend = $prepend;

            return;
        }

        $this->prepend = new File($prepend);
    }

    /**
     * Returns `append` attribute.
     *
     * @return File|string append attribute
     */
    public function getAppend()
    {
        return $this->append;
    }

    /**
     * Sets `append` attribute.
     *
     * @param File|string $append append new value
     */
    public function setAppend($append)
    {
        $this->append = $append;
    }

    /**
     * Scans the parameters list for the "lines" parameter and uses
     * it to set the number of lines to be returned in the filtered stream.
     * also scan for skip parameter.
     *
     * @throws IOException
     * @throws \InvalidArgumentException
     */
    private function initialize()
    {
        // get parameters
        $params = $this->getParameters();
        if (null !== $params) {
            /**
             * @var Parameter $param
             */
            foreach ($params as $param) {
                if ('prepend' === $param->getName()) {
                    $this->setPrepend(new File($param->getValue()));

                    continue;
                }
                if ('append' === $param->getName()) {
                    $this->setAppend(new File($param->getValue()));

                    continue;
                }
            }
        }
        if (null !== $this->prepend) {
            if (!$this->prepend->isAbsolute()) {
                $this->prepend = new File($this->getProject()->getBasedir(), $this->prepend->getPath());
            }
            $this->prependReader = new BufferedReader(new FileReader($this->prepend));
        }
        if (null !== $this->append) {
            if (!$this->append->isAbsolute()) {
                $this->append = new File($this->getProject()->getBasedir(), $this->append->getPath());
            }
            $this->appendReader = new BufferedReader(new FileReader($this->append));
        }
    }
}
