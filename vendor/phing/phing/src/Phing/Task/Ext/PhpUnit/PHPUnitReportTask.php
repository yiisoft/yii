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

namespace Phing\Task\Ext\PhpUnit;

use DOMDocument;
use Phing\Exception\BuildException;
use Phing\Io\ExtendedFileStream;
use Phing\Io\FileWriter;
use Phing\Io\IOException;
use Phing\Io\File;
use Phing\Phing;
use Phing\Task;
use Phing\Task\System\Condition\OsCondition;
use XSLTProcessor;

/**
 * Transform a PHPUnit xml report using XSLT.
 * This transformation generates an html report in either framed or non-framed
 * style. The non-framed style is convenient to have a concise report via mail,
 * the framed report is much more convenient if you want to browse into
 * different packages or testcases since it is a Javadoc like report.
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.phpunit
 * @since   2.1.0
 */
class PHPUnitReportTask extends Task
{
    private $format = "noframes";
    private $styleDir = "";

    /**
     * @var File
     */
    private $toDir;

    /**
     * Whether to use the sorttable JavaScript library, defaults to false
     * See {@link http://www.kryogenix.org/code/browser/sorttable/)}
     *
     * @var boolean
     */
    private $useSortTable = false;

    /**
     * the directory where the results XML can be found
     */
    private $inFile = "testsuites.xml";

    /**
     * Set the filename of the XML results file to use.
     *
     * @param  File $inFile
     * @return void
     */
    public function setInFile(File $inFile)
    {
        $this->inFile = $inFile;
    }

    /**
     * Set the format of the generated report. Must be noframes or frames.
     *
     * @param  $format
     * @return void
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Set the directory where the stylesheets are located.
     *
     * @param  $styleDir
     * @return void
     */
    public function setStyleDir($styleDir)
    {
        $this->styleDir = $styleDir;
    }

    /**
     * Set the directory where the files resulting from the
     * transformation should be written to.
     *
     * @param  File $toDir
     * @return void
     */
    public function setToDir(File $toDir)
    {
        $this->toDir = $toDir;
    }

    /**
     * Sets whether to use the sorttable JavaScript library, defaults to false
     * See {@link http://www.kryogenix.org/code/browser/sorttable/)}
     *
     * @param  boolean $useSortTable
     * @return void
     */
    public function setUseSortTable($useSortTable)
    {
        $this->useSortTable = (bool) $useSortTable;
    }

    /**
     * Returns the path to the XSL stylesheet
     *
     * @return File
     * @throws IOException
     */
    protected function getStyleSheet()
    {
        $xslname = "phpunit-" . $this->format . ".xsl";

        if ($this->styleDir) {
            $file = new File($this->styleDir, $xslname);
        } else {
            $path = Phing::getResourcePath("phing/etc/$xslname");

            if ($path === null) {
                $path = Phing::getResourcePath("etc/$xslname");

                if ($path === null) {
                    throw new BuildException("Could not find $xslname in resource path");
                }
            }

            $file = new File($path);
        }

        if (!$file->exists()) {
            throw new BuildException("Could not find file " . $file->getPath());
        }

        return $file;
    }

    /**
     * Transforms the DOM document
     *
     * @param DOMDocument $document
     * @throws BuildException
     * @throws IOException
     */
    protected function transform(\DOMDocument $document)
    {
        if (!$this->toDir->exists()) {
            throw new BuildException("Directory '" . $this->toDir . "' does not exist");
        }

        $xslfile = $this->getStyleSheet();

        $xsl = new \DOMDocument();
        $xsl->load($xslfile->getAbsolutePath());

        $proc = new XSLTProcessor();
        if (defined('XSL_SECPREF_WRITE_FILE')) {
            $proc->setSecurityPrefs(XSL_SECPREF_WRITE_FILE | XSL_SECPREF_CREATE_DIRECTORY);
        }
        $proc->registerPHPFunctions('nl2br');
        $proc->importStylesheet($xsl);
        $proc->setParameter('', 'output.sorttable', (string) $this->useSortTable);

        if ($this->format === "noframes") {
            $writer = new FileWriter(new File($this->toDir, "phpunit-noframes.html"));
            $writer->write($proc->transformToXml($document));
            $writer->close();
        } else {
            ExtendedFileStream::registerStream();

            $toDir = (string) $this->toDir;

            // urlencode() the path if we're on Windows
            if (OsCondition::isOS(OsCondition::FAMILY_WINDOWS)) {
                $toDir = urlencode($toDir);
            }

            // no output for the framed report
            // it's all done by extension...
            $proc->setParameter('', 'output.dir', $toDir);
            $proc->transformToXml($document);

            ExtendedFileStream::unregisterStream();
        }
    }

    /**
     * Fixes DOM document tree:
     *   - adds package="default" to 'testsuite' elements without
     *     package attribute
     *   - removes outer 'testsuite' container(s)
     *
     * @param \DOMDocument $document
     */
    protected function fixDocument(\DOMDocument $document)
    {
        $rootElement = $document->firstChild;

        $xp = new \DOMXPath($document);

        $nodes = $xp->query("/testsuites/testsuite/testsuite/testsuite");

        if ($nodes->length === 0) {
            $nodes = $xp->query("/testsuites/testsuite");

            foreach ($nodes as $node) {
                $children = $xp->query("./testsuite", $node);

                if ($children->length) {
                    $this->handleChildren($rootElement, $children);
                    $rootElement->removeChild($node);
                }
            }
        } else {
            $nodes = $xp->query("/testsuites/testsuite/testsuite");

            foreach ($nodes as $node) {
                $children = $xp->query("./testsuite", $node);

                if ($children->length) {
                    $this->handleChildren($rootElement, $children);
                    $rootElement->firstChild->removeChild($node);
                }
            }
        }
    }

    private function handleChildren($rootElement, $children)
    {
        /**
         * @var $child \DOMElement
         */
        foreach ($children as $child) {
            $rootElement->appendChild($child);

            if ($child->hasAttribute('package')) {
                continue;
            }

            if ($child->hasAttribute('namespace')) {
                $child->setAttribute('package', $child->getAttribute('namespace'));
                continue;
            }

            $package = 'default';
            try {
                $refClass = new \ReflectionClass($child->getAttribute('name'));

                if (preg_match('/@package\s+(.*)\r?\n/m', $refClass->getDocComment(), $matches)) {
                    $package = end($matches);
                } elseif (method_exists($refClass, 'getNamespaceName')) {
                    $namespace = $refClass->getNamespaceName();

                    if ($namespace !== '') {
                        $package = $namespace;
                    }
                }
            } catch (\ReflectionException $e) {
                // do nothing
            }

            $child->setAttribute('package', trim($package));
        }
    }

    /**
     * Initialize the task
     *
     * @throws \Phing\Exception\BuildException
     */
    public function init()
    {
        if (!class_exists('XSLTProcessor')) {
            throw new BuildException("PHPUnitReportTask requires the XSL extension");
        }
    }

    /**
     * The main entry point
     *
     * @throws BuildException
     */
    public function main()
    {
        $testSuitesDoc = new \DOMDocument();
        $testSuitesDoc->load((string) $this->inFile);

        $this->fixDocument($testSuitesDoc);

        try {
            $this->transform($testSuitesDoc);
        } catch (IOException $e) {
            throw new BuildException('Transformation failed.', $e);
        }
    }
}
