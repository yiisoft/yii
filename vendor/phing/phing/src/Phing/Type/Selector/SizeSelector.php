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

use Exception;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Io\IOException;
use Phing\Type\Parameter;
use Phing\Util\SizeHelper;

/**
 * Selector that filters files based on their size.
 *
 * @author Hans Lellelid <hans@xmpl.org> (Phing)
 * @author Bruce Atherton <bruce@callenish.com> (Ant)
 */
class SizeSelector extends BaseExtendSelector
{
    private const VALUE_KEY = 'value';
    private const WHEN_KEY = 'when';
    private const WHEN = [-1 => 'less',
        0 => 'equal',
        1 => 'more', ];
    /**
     * @var float
     */
    private $bytes = -1;

    /**
     * @var string
     */
    private $value = '';

    /**
     * @var string 'less', 'equal' or 'more'
     */
    private $when = self::WHEN[0];

    public function __toString(): string
    {
        $format = '{%s value: %s compare: %s}';

        return sprintf($format, __CLASS__, $this->value, $this->when);
    }

    /**
     * Filesize.
     *
     * @param string $value values like '1024', '5000B', '300M', '2G'
     */
    public function setValue(string $value)
    {
        $this->value = $value;
        $this->bytes = SizeHelper::fromHumanToBytes($value);
    }

    /**
     * This specifies when the file should be selected, whether it be
     * when the file matches a particular size, when it is smaller,
     * or whether it is larger.
     */
    public function setWhen(string $when): void
    {
        if (!in_array($when, self::WHEN, true)) {
            throw new BuildException("Invalid 'when' value '{$when}'");
        }
        $this->when = $when;
    }

    /**
     * When using this as a custom selector, this method will be called.
     * It translates each parameter into the appropriate setXXX() call.
     *
     * {@inheritdoc}
     *
     * @param Parameter[] $parameters the complete set of parameters for this selector
     *
     * @throws BuildException
     */
    public function setParameters(array $parameters): void
    {
        try {
            parent::setParameters($parameters);
            foreach ($parameters as $param) {
                switch (strtolower($param->getName())) {
                    case self::VALUE_KEY:
                        $this->setValue($param->getValue());

                        break;

                    case self::WHEN_KEY:
                        $this->setWhen($param->getValue());

                        break;

                    default:
                        throw new BuildException(sprintf('Invalid parameter %s', $param->getName()));
                }
            }
        } catch (Exception $exception) {
            $this->setError($exception->getMessage(), $exception);
        }
    }

    /**
     * <p>Checks to make sure all settings are kosher. In this case, it
     * means that the size attribute has been set (to a positive value),
     * that the multiplier has a valid setting, and that the size limit
     * is valid. Since the latter is a calculated value, this can only
     * fail due to a programming error.
     * </p>
     * <p>If a problem is detected, the setError() method is called.
     * </p>.
     *
     * {@inheritdoc}
     */
    public function verifySettings()
    {
        if ('' === $this->value) {
            $this->setError("The 'value' attribute is required");
        }
        if ($this->bytes < 0) {
            $this->setError("The 'value' attribute must be positive");
        }
    }

    /**
     * The heart of the matter. This is where the selector gets to decide
     * on the inclusion of a file in a particular fileset.
     *
     * {@inheritdoc}
     *
     * @param File   $basedir  A PhingFile object for the base directory
     * @param string $filename The name of the file to check
     * @param File   $file     A PhingFile object for this filename
     *
     * @throws IOException
     *
     * @return bool whether the file should be selected or not
     */
    public function isSelected(File $basedir, $filename, File $file): bool
    {
        $this->validate();

        // Directory size never selected for
        if ($file->isDirectory()) {
            return true;
        }
        $expected = array_search($this->when, self::WHEN);

        return ($file->length() <=> $this->bytes) === $expected;
    }
}
