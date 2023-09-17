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
use Phing\Type\RegularExpression;

/**
 * PropertySelector Task.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class PropertySelector extends AbstractPropertySetterTask
{
    /**
     * @var RegularExpression
     */
    private $match;
    private $select = '$0';
    private $delim = ',';
    private $caseSensitive = true;
    private $distinct = false;

    public function setMatch($match)
    {
        $this->match = new RegularExpression();
        $this->match->setPattern($match);
    }

    public function setSelect($select)
    {
        $this->select = $select;
    }

    public function setCaseSensitive($caseSensitive)
    {
        $this->caseSensitive = $caseSensitive;
    }

    public function setDelimiter($delim)
    {
        $this->delim = $delim;
    }

    public function setDistinct($distinct)
    {
        $this->distinct = $distinct;
    }

    public function main()
    {
        $this->validate();

        $regex = $this->match->getRegexp($this->project);
        $regex->setIgnoreCase(!$this->caseSensitive);

        $buf = '';
        $used = [];
        foreach (array_keys($this->project->getProperties()) as $key) {
            if ($regex->matches($key)) {
                $groups = $regex->getGroups();
                $output = $groups[ltrim($this->select, '$')];
                if (!($this->distinct && in_array($output, $used))) {
                    $used[] = $output;
                    if ('' !== $buf) {
                        $buf .= $this->delim;
                    }
                    $buf .= $output;
                }
            }
        }

        if ('' !== $buf) {
            $this->setPropertyValue($buf);
        }
    }

    protected function validate()
    {
        parent::validate();
        if (null === $this->match) {
            throw new BuildException('No match expression specified.');
        }
    }
}
