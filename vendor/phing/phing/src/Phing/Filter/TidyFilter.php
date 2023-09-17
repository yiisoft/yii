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
use Phing\Io\Reader;
use Phing\Type\Parameter;
use tidy;

/**
 * This filter uses the bundled-with-PHP Tidy extension to filter input.
 *
 * <p>
 * Example:<br/>
 * <pre>
 * <tidyfilter encoding="utf8">
 *   <config name="indent" value="true"/>
 *   <config name="output-xhtml" value="true"/>
 * </tidyfilter>
 * </pre>
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 */
class TidyFilter extends BaseParamFilterReader implements ChainableReader
{
    /**
     * @var string encoding of resulting document
     */
    private $encoding = 'utf8';

    /**
     * @var array Parameter[]
     */
    private $configParameters = [];

    /**
     * Set the encoding for resulting (X)HTML document.
     *
     * @param string $v
     */
    public function setEncoding($v)
    {
        $this->encoding = $v;
    }

    /**
     * Sets the config params.
     *
     * @param array Parameter[]
     * @param mixed $params
     *
     * @see   chain()
     */
    public function setConfigParameters($params)
    {
        $this->configParameters = $params;
    }

    /**
     * Adds a <config> element (which is a Parameter).
     *
     * @return Parameter
     */
    public function createConfig()
    {
        $num = array_push($this->configParameters, new Parameter());

        return $this->configParameters[$num - 1];
    }

    /**
     * Reads input and returns Tidy-filtered output.
     *
     * @param int $len
     *
     * @throws BuildException
     *
     * @return string Characters read, or -1 if the end of the stream has been reached
     */
    public function read($len = null)
    {
        if (!class_exists('Tidy')) {
            throw new BuildException("You must enable the 'tidy' extension in your PHP configuration in order to use the Tidy filter.");
        }

        if (!$this->getInitialized()) {
            $this->initialize();
            $this->setInitialized(true);
        }

        $buffer = $this->in->read($len);
        if (-1 === $buffer) {
            return -1;
        }

        $config = $this->getDistilledConfig();

        $tidy = new tidy();
        $tidy->parseString($buffer, $config, $this->encoding);
        $tidy->cleanRepair();

        return tidy_get_output($tidy);
    }

    /**
     * Creates a new TidyFilter using the passed in Reader for instantiation.
     *
     * @param Reader $reader Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @return TidyFilter a new filter based on this configuration, but filtering the specified reader
     */
    public function chain(Reader $reader): Reader
    {
        $newFilter = new self($reader);
        $newFilter->setConfigParameters($this->configParameters);
        $newFilter->setEncoding($this->encoding);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /**
     * Converts the Parameter objects being used to store configuration into a simle assoc array.
     *
     * @return array
     */
    private function getDistilledConfig()
    {
        $config = [];
        foreach ($this->configParameters as $p) {
            $config[$p->getName()] = $p->getValue();
        }

        return $config;
    }

    /**
     * Initializes any parameters (e.g. config options).
     * This method is only called when this filter is used through a <filterreader> tag in build file.
     */
    private function initialize()
    {
        $params = $this->getParameters();
        if (!empty($params)) {
            foreach ($params as $param) {
                if ('config' == $param->getType()) {
                    $this->configParameters[] = $param;
                } else {
                    if ('encoding' == $param->getName()) {
                        $this->setEncoding($param->getValue());
                    }
                }
            }
        }
    }
}
