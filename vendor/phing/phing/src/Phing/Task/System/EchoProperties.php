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

use ArrayIterator;
use DOMDocument;
use Exception;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\FileOutputStream;
use Phing\Io\IOException;
use Phing\Io\OutputStream;
use Phing\Phing;
use Phing\Project;
use Phing\Task;
use Phing\Util\Properties;
use RegexIterator;

/**
 *  Displays all the current properties in the build. The output can be sent to
 *  a file if desired.
 *
 *  Attribute "destfile" defines a file to send the properties to. This can be
 *  processed as a standard property file later.
 *
 *  Attribute "prefix" defines a prefix which is used to filter the properties
 *  only those properties starting with this prefix will be echoed.
 *
 *  By default, the "failonerror" attribute is enabled. If an error occurs while
 *  writing the properties to a file, and this attribute is enabled, then a
 *  BuildException will be thrown. If disabled, then IO errors will be reported
 *  as a log statement, but no error will be thrown.
 *
 *  Examples: <pre>
 *  &lt;echoproperties  /&gt;
 * </pre> Report the current properties to the log.
 *
 *  <pre>
 *  &lt;echoproperties destfile="my.properties" /&gt;
 * </pre> Report the current properties to the file "my.properties", and will
 *  fail the build if the file could not be created or written to.
 *
 *  <pre>
 *  &lt;echoproperties destfile="my.properties" failonerror="false"
 *      prefix="phing" /&gt;
 * </pre> Report all properties beginning with 'phing' to the file
 *  "my.properties", and will log a message if the file could not be created or
 *  written to, but will still allow the build to continue.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class EchoProperties extends Task
{
    /**
     * the properties element.
     */
    private static $PROPERTIES = 'properties';

    /**
     * the property element.
     */
    private static $PROPERTY = 'property';

    /**
     * name attribute for property, testcase and testsuite elements.
     */
    private static $ATTR_NAME = 'name';

    /**
     * value attribute for property elements.
     */
    private static $ATTR_VALUE = 'value';
    /**
     * the input file.
     *
     * @var File
     */
    private $inFile;

    /**
     * File object pointing to the output file. If this is null, then
     * we output to the project log, not to a file.
     *
     * @var File
     */
    private $destfile;

    /**
     * If this is true, then errors generated during file output will become
     * build errors, and if false, then such errors will be logged, but not
     * thrown.
     *
     * @var bool
     */
    private $failonerror = true;

    /**
     * @var string
     */
    private $format = 'text';

    /**
     * @var string
     */
    private $prefix = '';

    /**
     * @var string
     */
    private $regex = '';

    /**
     * Sets the input file.
     *
     * @param File|string $file the input file
     */
    public function setSrcfile($file)
    {
        if (is_string($file)) {
            $this->inFile = new File($file);
        } else {
            $this->inFile = $file;
        }
    }

    /**
     *  Set a file to store the property output.  If this is never specified,
     *  then the output will be sent to the Phing log.
     *
     * @param File|string $destfile file to store the property output
     */
    public function setDestfile($destfile)
    {
        if (is_string($destfile)) {
            $this->destfile = new File($destfile);
        } else {
            $this->destfile = $destfile;
        }
    }

    /**
     * If true, the task will fail if an error occurs writing the properties
     * file, otherwise errors are just logged.
     */
    public function setFailOnError(bool $failonerror)
    {
        $this->failonerror = $failonerror;
    }

    /**
     *  If the prefix is set, then only properties which start with this
     *  prefix string will be recorded. If regex is not set and  if this
     *  is never set, or it is set to an empty string or <tt>null</tt>,
     *  then all properties will be recorded. <P>.
     *
     *  For example, if the attribute is set as:
     *    <PRE>&lt;echoproperties  prefix="phing." /&gt;</PRE>
     *  then the property "phing.home" will be recorded, but "phing-example"
     *  will not.
     *
     * @param string $prefix The new prefix value
     */
    public function setPrefix($prefix)
    {
        if (null != $prefix && 0 != strlen($prefix)) {
            $this->prefix = $prefix;
        }
    }

    /**
     *  If the regex is set, then only properties whose names match it
     *  will be recorded.  If prefix is not set and if this is never set,
     *  or it is set to an empty string or <tt>null</tt>, then all
     *  properties will be recorded.<P>.
     *
     *  For example, if the attribute is set as:
     *    <PRE>&lt;echoproperties  prefix=".*phing.*" /&gt;</PRE>
     *  then the properties "phing.home" and "user.phing" will be recorded,
     *  but "phing-example" will not.
     *
     * @param string $regex The new regex value
     */
    public function setRegex($regex)
    {
        if (null != $regex && 0 != strlen($regex)) {
            $this->regex = $regex;
        }
    }

    /**
     * Set the output format - xml or text.
     *
     * @param string $ea an enumerated <code>FormatAttribute</code> value
     */
    public function setFormat($ea)
    {
        $this->format = $ea;
    }

    /**
     *  Run the task.
     *
     * @throws BuildException trouble, probably file IO
     */
    public function main()
    {
        if (null != $this->prefix && null != $this->regex) {
            throw new BuildException('Please specify either prefix or regex, but not both', $this->getLocation());
        }

        //copy the properties file
        $allProps = [];

        // load properties from file if specified, otherwise use Phing's properties
        if (null == $this->inFile) {
            // add phing properties
            $allProps = $this->getProject()->getProperties();
        } elseif (null != $this->inFile) {
            if ($this->inFile->exists() && $this->inFile->isDirectory()) {
                $message = 'srcfile is a directory!';
                $this->failOnErrorAction(null, $message, Project::MSG_ERR);

                return;
            }

            if ($this->inFile->exists() && !$this->inFile->canRead()) {
                $message = 'Can not read from the specified srcfile!';
                $this->failOnErrorAction(null, $message, Project::MSG_ERR);

                return;
            }

            try {
                $props = new Properties();
                $props->load(new File($this->inFile));
                $allProps = $props->getProperties();
            } catch (IOException $ioe) {
                $message = 'Could not read file ' . $this->inFile->getAbsolutePath();
                $this->failOnErrorAction($ioe, $message, Project::MSG_WARN);

                return;
            }
        }

        $os = null;

        try {
            if (null == $this->destfile) {
                $os = Phing::getOutputStream();
                $this->saveProperties($allProps, $os);
                $this->log($os);
            } else {
                if ($this->destfile->exists() && $this->destfile->isDirectory()) {
                    $message = 'destfile is a directory!';
                    $this->failOnErrorAction(null, $message, Project::MSG_ERR);

                    return;
                }

                if ($this->destfile->exists() && !$this->destfile->canWrite()) {
                    $message = 'Can not write to the specified destfile!';
                    $this->failOnErrorAction(null, $message, Project::MSG_ERR);

                    return;
                }
                $os = new FileOutputStream($this->destfile);
                $this->saveProperties($allProps, $os);
            }
        } catch (IOException $ioe) {
            $this->failOnErrorAction($ioe);
        }
    }

    /**
     *  Send the key/value pairs in the hashtable to the given output stream.
     *  Only those properties matching the <tt>prefix</tt> constraint will be
     *  sent to the output stream.
     *  The output stream will be closed when this method returns.
     *
     * @param array        $allProps propfile to save
     * @param OutputStream $os       output stream
     *
     * @throws IOException    on output errors
     * @throws BuildException on other errors
     */
    protected function saveProperties($allProps, $os)
    {
        ksort($allProps);
        $props = new Properties();

        if ('' !== $this->regex) {
            $a = new ArrayIterator($allProps);
            $i = new RegexIterator($a, $this->regex, RegexIterator::MATCH, RegexIterator::USE_KEY);
            $allProps = iterator_to_array($i);
        }
        if ('' !== $this->prefix) {
            $a = new ArrayIterator($allProps);
            $i = new RegexIterator(
                $a,
                '~^' . preg_quote($this->prefix, '~') . '.*~',
                RegexIterator::MATCH,
                RegexIterator::USE_KEY
            );
            $allProps = iterator_to_array($i);
        }

        foreach ($allProps as $name => $value) {
            $props->setProperty($name, $value);
        }

        if ('text' === $this->format) {
            $this->textSaveProperties($props, $os, 'Phing properties');
        } elseif ('xml' === $this->format) {
            $this->xmlSaveProperties($props, $os);
        }
    }

    /**
     * Output the properties as xml output.
     *
     * @param Properties   $props the properties to save
     * @param OutputStream $os    the output stream to write to (Note this gets closed)
     *
     * @throws BuildException
     */
    protected function xmlSaveProperties(Properties $props, OutputStream $os)
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;
        $rootElement = $doc->createElement(self::$PROPERTIES);

        $properties = $props->getProperties();
        ksort($properties);
        foreach ($properties as $key => $value) {
            $propElement = $doc->createElement(self::$PROPERTY);
            $propElement->setAttribute(self::$ATTR_NAME, $key);
            $propElement->setAttribute(self::$ATTR_VALUE, $value);
            $rootElement->appendChild($propElement);
        }

        try {
            $doc->appendChild($rootElement);
            $os->write($doc->saveXML());
        } catch (IOException $ioe) {
            throw new BuildException('Unable to write XML file', $ioe);
        }
    }

    /**
     * @param Properties   $props  the properties to record
     * @param OutputStream $os     record the properties to this output stream
     * @param string       $header prepend this header to the property output
     *
     * @throws BuildException on an I/O error during a write
     */
    protected function textSaveProperties(Properties $props, OutputStream $os, $header)
    {
        try {
            $props->storeOutputStream($os, $header);
        } catch (IOException $ioe) {
            throw new BuildException($ioe, $this->getLocation());
        }
    }

    /**
     * @param Exception $exception
     * @param string    $message
     * @param int       $level
     *
     * @throws BuildException
     */
    private function failOnErrorAction(Exception $exception = null, $message = '', $level = Project::MSG_INFO)
    {
        if ($this->failonerror) {
            throw new BuildException(
                $exception ?? $message,
                $this->getLocation()
            );
        }

        $this->log(
            null !== $exception && '' === $message
                ? $exception->getMessage()
                : $message,
            $level
        );
    }
}
