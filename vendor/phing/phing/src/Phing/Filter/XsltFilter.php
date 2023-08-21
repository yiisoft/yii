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

use DOMDocument;
use Phing\Exception\BuildException;
use Phing\Io\BufferedReader;
use Phing\Io\File;
use Phing\Io\FileReader;
use Phing\Io\FilterReader;
use Phing\Io\IOException;
use Phing\Io\Reader;
use Phing\Project;
use XSLTProcessor;

/**
 * Applies XSL stylesheet to incoming text.
 *
 * Uses PHP XSLT support (libxslt).
 *
 * @author Hans Lellelid <hans@velum.net>
 * @author Yannick Lecaillez <yl@seasonfive.com>
 * @author Andreas Aderhold <andi@binarycloud.com>
 *
 * @see     FilterReader
 */
class XsltFilter extends BaseParamFilterReader implements ChainableReader
{
    /**
     * Path to XSL stylesheet.
     *
     * @var File
     */
    private $xslFile;

    /**
     * Whether XML file has been transformed.
     *
     * @var bool
     */
    private $processed = false;

    /**
     * XSLT Params.
     *
     * @var XsltParam[]
     */
    private $xsltParams = [];

    /**
     * Whether to use loadHTML() to parse the input XML file.
     */
    private $html = false;

    /**
     * Whether to resolve entities in the XML document (see
     * {@link http://www.php.net/manual/en/class.domdocument.php#domdocument.props.resolveexternals}
     * for more details).
     *
     * @var bool
     *
     * @since 2.4
     */
    private $resolveDocumentExternals = false;

    /**
     * Whether to resolve entities in the stylesheet.
     *
     * @var bool
     *
     * @since 2.4
     */
    private $resolveStylesheetExternals = false;

    /**
     * Create new XSLT Param object, to handle the <param/> nested element.
     *
     * @return XsltParam
     */
    public function createParam()
    {
        $num = array_push($this->xsltParams, new XsltParam());

        return $this->xsltParams[$num - 1];
    }

    /**
     * Sets the XSLT params for this class.
     * This is used to "clone" this class, in the chain() method.
     *
     * @param array $params
     */
    public function setParams($params)
    {
        $this->xsltParams = $params;
    }

    /**
     * Returns the XSLT params set for this class.
     * This is used to "clone" this class, in the chain() method.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->xsltParams;
    }

    /**
     * Set the XSLT stylesheet.
     *
     * @param mixed $file phingFile object or path
     */
    public function setStyle(File $file)
    {
        $this->xslFile = $file;
    }

    /**
     * Whether to use HTML parser for the XML.
     * This is supported in libxml2 -- Yay!
     *
     * @return bool
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * Whether to use HTML parser for XML.
     *
     * @param bool $b
     */
    public function setHtml($b)
    {
        $this->html = (bool) $b;
    }

    /**
     * Get the path to XSLT stylesheet.
     *
     * @return file XSLT stylesheet path
     */
    public function getStyle()
    {
        return $this->xslFile;
    }

    /**
     * Whether to resolve entities in document.
     *
     * @since 2.4
     */
    public function setResolveDocumentExternals(bool $resolveExternals)
    {
        $this->resolveDocumentExternals = $resolveExternals;
    }

    /**
     * @return bool
     *
     * @since 2.4
     */
    public function getResolveDocumentExternals()
    {
        return $this->resolveDocumentExternals;
    }

    /**
     * Whether to resolve entities in stylesheet.
     *
     * @since 2.4
     */
    public function setResolveStylesheetExternals(bool $resolveExternals)
    {
        $this->resolveStylesheetExternals = $resolveExternals;
    }

    /**
     * @return bool
     *
     * @since 2.4
     */
    public function getResolveStylesheetExternals()
    {
        return $this->resolveStylesheetExternals;
    }

    /**
     * Reads stream, applies XSLT and returns resulting stream.
     *
     * @param int $len
     *
     * @throws BuildException
     *
     * @return string transformed buffer
     */
    public function read($len = null)
    {
        if (!class_exists('XSLTProcessor')) {
            throw new BuildException('Could not find the XSLTProcessor class. Make sure PHP has been compiled/configured to support XSLT.');
        }

        if (true === $this->processed) {
            return -1; // EOF
        }

        if (!$this->getInitialized()) {
            $this->initialize();
            $this->setInitialized(true);
        }

        // Read XML
        $_xml = null;
        while (($data = $this->in->read($len)) !== -1) {
            $_xml .= $data;
        }

        if (null === $_xml) { // EOF?
            return -1;
        }

        if (empty($_xml)) {
            $this->log('XML file is empty!', Project::MSG_WARN);

            return ''; // return empty string, don't attempt to apply XSLT
        }

        // Read XSLT
        $_xsl = null;
        $br = new BufferedReader(new FileReader($this->xslFile));
        $_xsl = $br->read();

        $this->log(
            'Tranforming XML ' . $this->in->getResource() . ' using style ' . $this->xslFile->getPath(),
            Project::MSG_VERBOSE
        );

        $out = '';

        try {
            $out = $this->process($_xml, $_xsl);
            $this->processed = true;
        } catch (IOException $e) {
            throw new BuildException($e);
        }

        return $out;
    }

    /**
     * Creates a new XsltFilter using the passed in
     * Reader for instantiation.
     *
     * @param Reader A Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return XsltFilter A new filter based on this configuration, but filtering
     *                    the specified reader
     */
    public function chain(Reader $reader): Reader
    {
        $newFilter = new XsltFilter($reader);
        $newFilter->setProject($this->getProject());
        $newFilter->setStyle($this->getStyle());
        $newFilter->setInitialized(true);
        $newFilter->setParams($this->getParams());
        $newFilter->setHtml($this->getHtml());

        return $newFilter;
    }

    // {{{ method _ProcessXsltTransformation($xml, $xslt) throws BuildException

    /**
     * Try to process the XSLT transformation.
     *
     * @param string $xml XML to process
     * @param string $xsl XSLT sheet to use for the processing
     *
     * @throws BuildException On XSLT errors
     *
     * @return string
     */
    protected function process($xml, $xsl)
    {
        $processor = new XSLTProcessor();

        // Create and setup document.
        $xmlDom = new DOMDocument();
        $xmlDom->resolveExternals = $this->resolveDocumentExternals;

        // Create and setup stylesheet.
        $xslDom = new DOMDocument();
        $xslDom->resolveExternals = $this->resolveStylesheetExternals;

        if ($this->html) {
            $result = @$xmlDom->loadHTML($xml);
        } else {
            $result = @$xmlDom->loadXML($xml);
        }

        if (false === $result) {
            throw new BuildException('Invalid syntax detected.');
        }

        $xslDom->loadXML($xsl);

        if (defined('XSL_SECPREF_WRITE_FILE')) {
            $processor->setSecurityPrefs(XSL_SECPREF_WRITE_FILE | XSL_SECPREF_CREATE_DIRECTORY);
        }
        $processor->importStylesheet($xslDom);

        // ignoring param "type" attrib, because
        // we're only supporting direct XSL params right now
        foreach ($this->xsltParams as $param) {
            $this->log('Setting XSLT param: ' . $param->getName() . '=>' . $param->getExpression(), Project::MSG_DEBUG);
            $processor->setParameter(null, $param->getName(), $param->getExpression());
        }

        $errorlevel = error_reporting();
        error_reporting($errorlevel & ~E_WARNING);
        @$result = $processor->transformToXml($xmlDom);
        error_reporting($errorlevel);

        if (false === $result) {
            //$errno = xslt_errno($processor);
            //$err   = xslt_error($processor);
            throw new BuildException('XSLT Error');
        }

        return $result;
    }

    /**
     * Parses the parameters to get stylesheet path.
     */
    private function initialize()
    {
        $params = $this->getParameters();
        if (null !== $params) {
            for ($i = 0, $_i = count($params); $i < $_i; ++$i) {
                if (null === $params[$i]->getType()) {
                    if ('style' === $params[$i]->getName()) {
                        $this->setStyle($params[$i]->getValue());
                    }
                } elseif ('param' == $params[$i]->getType()) {
                    $xp = new XsltParam();
                    $xp->setName($params[$i]->getName());
                    $xp->setExpression($params[$i]->getValue());
                    $this->xsltParams[] = $xp;
                }
            }
        }
    }
}
