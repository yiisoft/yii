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
use Phing\Type\RegularExpression;
use Phing\Util\Regexp;
use Phing\Util\RegexpException;
use Phing\Util\StringHelper;

/**
 * Selector that filters files based on whether they contain a
 * particular string using regexp.
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Bruce Atherton <bruce@callenish.com> (Ant)
 */
class ContainsRegexpSelector extends BaseExtendSelector
{
    public const EXPRESSION_KEY = 'expression';
    public const CASE_KEY = 'casesensitive';
    public const ML_KEY = 'multiline';
    /**
     * The expression set from XML.
     *
     * @var string
     */
    private $userProvidedExpression;

    /**
     * @var Regexp
     */
    private $myExpression;

    /**
     * @var bool
     */
    private $casesensitive = true;

    /**
     * @var bool
     */
    private $multiline = false;

    /**
     * @var RegularExpression
     */
    private $myRegExp;

    /**
     * @return string
     */
    public function __toString()
    {
        $buf = '{containsregexpselector expression: ';
        $buf .= $this->userProvidedExpression;
        $buf .= ' casesensitive: ';
        if ($this->casesensitive) {
            $buf .= 'true';
        } else {
            $buf .= 'false';
        }
        $buf .= '}';

        return $buf;
    }

    /**
     * The expression to match on within a file.
     *
     * @param string $exp the string that a file must contain to be selected
     */
    public function setExpression($exp)
    {
        $this->userProvidedExpression = $exp;
    }

    /**
     * Whether to ignore case in the regex match.
     *
     * @param bool $casesensitive whether to pay attention to case sensitivity
     */
    public function setCasesensitive($casesensitive)
    {
        $this->casesensitive = $casesensitive;
    }

    public function setMultiline(bool $multiline): void
    {
        $this->multiline = $multiline;
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
                    case self::EXPRESSION_KEY:
                        $this->setExpression($parameters[$i]->getValue());

                        break;

                    case self::CASE_KEY:
                        $this->setCasesensitive(StringHelper::booleanValue($parameters[$i]->getValue()));

                        break;

                    case self::ML_KEY:
                        $this->setMultiLine(StringHelper::booleanValue($parameters[$i]->getValue()));

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
        if (null === $this->userProvidedExpression) {
            $this->setError('The expression attribute is required');
        }
    }

    /**
     * The heart of the matter. This is where the selector gets to decide
     * on the inclusion of a file in a particular fileset.
     *
     * @param File   $basedir  base directory the scan is being done from
     * @param string $filename the name of the file to check
     * @param File   $file     PhingFile object the selector can use
     *
     * @throws IOException
     * @throws RegexpException
     *
     * @return bool whether the file should be selected or not
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

        if (null === $this->myRegExp) {
            $this->myRegExp = new RegularExpression();
            $this->myRegExp->setPattern($this->userProvidedExpression);
            $this->myExpression = $this->myRegExp->getRegexp($this->getProject());
        }

        $in = null;

        try {
            $in = new BufferedReader(new FileReader($file));
            $teststr = $in->readLine();
            while (null !== $teststr) {
                $this->myExpression->setMultiline($this->multiline);
                $this->myExpression->setIgnoreCase(!$this->casesensitive);
                if ($this->myExpression->matches($teststr)) {
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
