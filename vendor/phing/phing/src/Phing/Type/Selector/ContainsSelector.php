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

namespace Phing\Type\Selector;

use Phing\Exception\BuildException;
use Phing\Io\BufferedReader;
use Phing\Io\File;
use Phing\Io\FileReader;
use Phing\Io\IOException;
use Phing\Task\System\Condition\OsCondition;

/**
 * Selector that filters files based on whether they contain a
 * particular string.
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Bruce Atherton <bruce@callenish.com> (Ant)
 */
class ContainsSelector extends BaseExtendSelector
{
    public const CONTAINS_KEY = 'text';
    public const CASE_KEY = 'casesensitive';
    public const WHITESPACE_KEY = 'ignorewhitespace';
    private $contains;
    private $casesensitive = true;
    private $ignorewhitespace = false;

    /**
     * @return string
     */
    public function __toString()
    {
        $buf = '{containsselector text: ';
        $buf .= $this->contains;
        $buf .= ' casesensitive: ';
        if ($this->casesensitive) {
            $buf .= 'true';
        } else {
            $buf .= 'false';
        }
        $buf .= ' ignorewhitespace: ';
        if ($this->ignorewhitespace) {
            $buf .= 'true';
        } else {
            $buf .= 'false';
        }
        $buf .= '}';

        return $buf;
    }

    /**
     * The string to search for within a file.
     *
     * @param string $contains the string that a file must contain to be selected
     */
    public function setText($contains)
    {
        $this->contains = $contains;
    }

    /**
     * Whether to ignore case in the string being searched.
     *
     * @param bool $casesensitive whether to pay attention to case sensitivity
     */
    public function setCasesensitive($casesensitive)
    {
        $this->casesensitive = $casesensitive;
    }

    /**
     * @param bool $ignoreWhitespace
     */
    public function setIgnoreWhitespace($ignoreWhitespace)
    {
        $this->ignorewhitespace = $ignoreWhitespace;
    }

    /**
     * When using this as a custom selector, this method will be called.
     * It translates each parameter into the appropriate setXXX() call.
     *
     * @param array $parameters the complete set of parameters for this selector
     */
    public function setParameters(array $parameters): void
    {
        parent::setParameters($parameters);
        if (null !== $parameters) {
            for ($i = 0, $size = count($parameters); $i < $size; ++$i) {
                $paramname = $parameters[$i]->getName();

                switch (strtolower($paramname)) {
                    case self::CONTAINS_KEY:
                        $this->setText($parameters[$i]->getValue());

                        break;

                    case self::CASE_KEY:
                        $this->setCasesensitive($parameters[$i]->getValue());

                        break;

                    case self::WHITESPACE_KEY:
                        $this->setIgnoreWhitespace($parameters[$i]->getValue());

                        break;

                    default:
                        $this->setError('Invalid parameter ' . $paramname);
                }
            } // for each param
        } // if params
    }

    /**
     * Checks to make sure all settings are kosher. In this case, it
     * means that the pattern attribute has been set.
     */
    public function verifySettings()
    {
        if (null === $this->contains) {
            $this->setError('The text attribute is required');
        }
    }

    /**
     * The heart of the matter. This is where the selector gets to decide
     * on the inclusion of a file in a particular fileset.
     *
     * @param string $filename
     *
     * @throws BuildException
     *
     * @return bool whether the file should be selected or not
     *
     * @internal param is $filename the name of the file to check
     * @internal param a $file PhingFile object the selector can use
     * @internal param the $basedir base directory the scan is being done from
     */
    public function isSelected(File $basedir, $filename, File $file)
    {
        $this->validate();

        try {
            if ($file->isDirectory() || $file->isLink()) {
                return true;
            }
        } catch (IOException $ioe) {
            if (OsCondition::isOS('windows')) {
                return true;
            }

            throw new BuildException($ioe);
        }

        $userstr = $this->contains;
        if (!$this->casesensitive) {
            $userstr = strtolower($this->contains);
        }
        if ($this->ignorewhitespace) {
            $userstr = SelectorUtils::removeWhitespace($userstr);
        }

        $in = null;

        try {
            $in = new BufferedReader(new FileReader($file));
            $teststr = $in->readLine();
            while (null !== $teststr) {
                if (!$this->casesensitive) {
                    $teststr = strtolower($teststr);
                }
                if ($this->ignorewhitespace) {
                    $teststr = SelectorUtils::removeWhitespace($teststr);
                }
                if (false !== strpos($teststr, $userstr)) {
                    return true;
                }
                $teststr = $in->readLine();
            }

            $in->close();

            return false;
        } catch (IOException $ioe) {
            if ($in) {
                $in->close();
            }

            throw new BuildException('Could not read file ' . $filename);
        }
    }
}
