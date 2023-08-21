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
use Phing\Io\File;
use Phing\Io\FilterReader;
use Phing\Io\IOException;
use Phing\Io\Reader;
use Phing\Project;

/**
 * Applies Xinclude parsing to incoming text.
 *
 * Uses PHP DOM XML support
 *
 * @author  Bill Karwin <bill@karwin.com>
 *
 * @see     FilterReader
 */
class XincludeFilter extends BaseParamFilterReader implements ChainableReader
{
    /** @var File */
    private $basedir;

    /**
     * @var bool
     */
    private $processed = false;

    /**
     * Whether to resolve entities.
     *
     * @var bool
     *
     * @since 2.4
     */
    private $resolveExternals = false;

    /**
     * Whether to resolve entities.
     *
     * @param $resolveExternals
     *
     * @since 2.4
     */
    public function setResolveExternals(bool $resolveExternals)
    {
        $this->resolveExternals = $resolveExternals;
    }

    /**
     * @return bool
     *
     * @since 2.4
     */
    public function getResolveExternals()
    {
        return $this->resolveExternals;
    }

    public function setBasedir(File $dir)
    {
        $this->basedir = $dir;
    }

    /**
     * @return File
     */
    public function getBasedir()
    {
        return $this->basedir;
    }

    /**
     * Reads stream, applies XSLT and returns resulting stream.
     *
     * @param int $len
     *
     * @throws IOException
     * @throws BuildException
     *
     * @return string transformed buffer
     */
    public function read($len = null)
    {
        if (!class_exists('DOMDocument')) {
            throw new BuildException('Could not find the DOMDocument class. Make sure PHP has been compiled/configured to support DOM XML.');
        }

        if (true === $this->processed) {
            return -1; // EOF
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

            return '';
        }

        $this->log('Transforming XML ' . $this->in->getResource() . ' using Xinclude ', Project::MSG_VERBOSE);

        $out = '';

        try {
            $out = $this->process($_xml);
            $this->processed = true;
        } catch (IOException $e) {
            throw new BuildException($e);
        }

        return $out;
    }

    /**
     * Creates a new XincludeFilter using the passed in
     * Reader for instantiation.
     *
     * @param Reader A Reader object providing the underlying stream.
     *               Must not be <code>null</code>.
     *
     * @return XincludeFilter A new filter based on this configuration, but filtering
     *                        the specified reader
     */
    public function chain(Reader $reader): Reader
    {
        $newFilter = new self($reader);
        $newFilter->setProject($this->getProject());
        $newFilter->setBasedir($this->getBasedir());

        return $newFilter;
    }

    /**
     * Try to process the Xinclude transformation.
     *
     * @param string  XML to process
     * @param mixed $xml
     *
     * @return string
     */
    protected function process($xml)
    {
        if ($this->basedir) {
            $cwd = getcwd();
            chdir($this->basedir);
        }

        // Create and setup document.
        $xmlDom = new DOMDocument();
        $xmlDom->resolveExternals = $this->resolveExternals;

        $xmlDom->loadXML($xml);

        $xmlDom->xinclude();

        if ($this->basedir) {
            chdir($cwd);
        }

        return $xmlDom->saveXML();
    }
}
