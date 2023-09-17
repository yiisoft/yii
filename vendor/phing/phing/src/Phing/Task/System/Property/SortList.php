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

namespace Phing\Task\System\Property;

use Phing\Exception\BuildException;
use Phing\Task\System\PropertyTask;
use Phing\Type\Reference;

/**
 * SortList Task.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class SortList extends AbstractPropertySetterTask
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var Reference
     */
    private $ref;

    /**
     * @var string
     */
    private $delimiter = ',';

    /**
     * @var array
     */
    private static $allowedFlags = [
        'SORT_REGULAR',
        'SORT_NUMERIC',
        'SORT_STRING',
        'SORT_LOCALE_STRING',
        'SORT_NATURAL',
        'SORT_FLAG_CASE',
    ];

    /**
     * @var string
     */
    private $flags = '';

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setRefid(Reference $ref)
    {
        $this->ref = $ref;
    }

    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @param string $flags
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
    }

    public function main()
    {
        $this->validate();

        $val = $this->value;
        if (null === $val && null !== $this->ref) {
            /**
             * @var PropertyTask $propTask
             */
            $propTask = $this->ref->getReferencedObject($this->project);
            $val = $propTask->getValue();
        }

        if (null === $val) {
            throw new BuildException("Either the 'value' or 'refid' attribute must be set.");
        }

        $propList = explode($this->delimiter, $val);

        if ($this->flags) {
            sort($propList, $this->validateFlags());
        } else {
            sort($propList);
        }

        $this->setPropertyValue(implode($this->delimiter, $propList));
    }

    private function validateFlags()
    {
        $flags = 0;

        foreach (explode('|', $this->flags) as $flag) {
            $flag = trim($flag);
            $flag = strtoupper($flag);

            if (!in_array($flag, self::$allowedFlags, true)) {
                throw new BuildException($flag . ' is not a valid sort flag.');
            }

            $flags |= constant($flag);
        }

        return $flags;
    }
}
