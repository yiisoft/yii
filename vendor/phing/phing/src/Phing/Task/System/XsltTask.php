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

namespace Phing\Task\System;

use Phing\Filter\XsltFilter;
use Phing\Filter\XsltParam;
use Phing\Io\File;
use Phing\Project;
use Phing\Type\FilterChain;

/**
 * Implements an XSLT processing filter while copying files.
 *
 * This is a shortcut for calling the <copy> task with the XSLTFilter used
 * in the <filterchains> section.
 *
 * @author  Andreas Aderhold, andi@binarycloud.com
 */
class XsltTask extends CopyTask
{
    /**
     * @var XsltFilter object that we use to handle transformation
     */
    private $xsltFilter;

    /**
     * @var XsltParam[] parameters to pass to XSLT processor
     */
    private $parameters = [];

    /**
     * Setup the filterchains w/ XSLTFilter that we will use while copying the files.
     */
    public function init()
    {
        $xf = new XsltFilter();
        $chain = new FilterChain($this->getProject());
        $chain->addXsltFilter($xf);
        $this->addFilterChain($chain);
        $this->xsltFilter = $xf;
    }

    /**
     * Set any XSLT Param and invoke CopyTask::main().
     *
     * @see CopyTask::main()
     */
    public function main()
    {
        $this->log('Doing XSLT transformation using stylesheet ' . $this->xsltFilter->getStyle(), Project::MSG_VERBOSE);
        $this->xsltFilter->setParams($this->parameters);
        parent::main();
    }

    public function setHtml(bool $isHtml)
    {
        $this->xsltFilter->setHtml($isHtml);
    }

    /**
     * Set the stylesheet to use.
     */
    public function setStyle(File $style)
    {
        $this->xsltFilter->setStyle($style);
    }

    /**
     * Whether to resolve entities in the XML document.
     *
     * @since 2.4
     */
    public function setResolveDocumentExternals(bool $resolveExternals)
    {
        $this->xsltFilter->setResolveDocumentExternals($resolveExternals);
    }

    /**
     * Whether to resolve entities in the stylesheet.
     *
     * @since 2.4
     */
    public function setResolveStylesheetExternals(bool $resolveExternals)
    {
        $this->xsltFilter->setResolveStylesheetExternals($resolveExternals);
    }

    /**
     * Support nested <param> tags using XSLTParam class.
     *
     * @return XsltParam
     */
    public function createParam()
    {
        $num = array_push($this->parameters, new XsltParam());

        return $this->parameters[$num - 1];
    }
}
