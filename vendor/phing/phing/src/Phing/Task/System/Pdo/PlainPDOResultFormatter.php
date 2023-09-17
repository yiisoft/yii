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

namespace Phing\Task\System\Pdo;

use Phing\Io\File;
use Phing\Util\StringHelper;

/**
 * Plain text formatter for PDO results.
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 *
 * @since   2.3.0
 */
class PlainPDOResultFormatter extends PDOResultFormatter
{
    /**
     * Have column headers been printed?
     *
     * @var bool
     */
    private $colsprinted = false;

    /**
     * Whether to show headers.
     *
     * @var bool
     */
    private $showheaders = true;
    private $showtrailers = false;

    /**
     * Column delimiter.
     * Defaults to ','.
     *
     * @var string
     */
    private $coldelimiter = ',';

    /**
     * Row delimiter.
     * Defaults to PHP_EOL.
     *
     * @var string
     */
    private $rowdelimiter = PHP_EOL;
    private $statementcounter = 0;

    /**
     * Set the showheaders attribute.
     *
     * @param bool $v
     */
    public function setShowheaders($v)
    {
        $this->showheaders = StringHelper::booleanValue($v);
    }

    /**
     * Set the showtrailers attribute.
     *
     * @param bool $v
     */
    public function setShowtrailers($v)
    {
        $this->showtrailers = StringHelper::booleanValue($v);
    }

    public function setStatementCounter($count)
    {
        $this->statementcounter = $count;
    }

    /**
     * Sets the column delimiter.
     *
     * @param string $v
     */
    public function setColdelim($v)
    {
        $this->coldelimiter = $v;
    }

    /**
     * Sets the row delimiter.
     *
     * @param string $v
     */
    public function setRowdelim($v)
    {
        $this->rowdelimiter = $v;
    }

    /**
     * Processes a specific row from PDO result set.
     *
     * @param array $row row of PDO result set
     */
    public function processRow($row)
    {
        $line = '';

        if ($this->showtrailers) {
            $this->out->write('# ' . $this->statementcounter . ' statement(s) successful executed.' . PHP_EOL);
        }

        if (!$this->colsprinted && $this->showheaders) {
            $first = true;
            foreach ($row as $fieldName => $ignore) {
                if ($first) {
                    $first = false;
                } else {
                    $line .= ',';
                }
                $line .= $fieldName;
            }

            $this->out->write($line);
            $this->out->write(PHP_EOL);

            $line = '';
            $this->colsprinted = true;
        } // if show headers

        $first = true;
        foreach ($row as $columnValue) {
            if (null !== $columnValue) {
                $columnValue = trim($columnValue);
            }

            if ($first) {
                $first = false;
            } else {
                $line .= $this->coldelimiter;
            }
            $line .= $columnValue;
        }

        $this->out->write($line);
        $this->out->write($this->rowdelimiter);
    }

    /**
     * @return File
     */
    public function getPreferredOutfile()
    {
        return new File('results.txt');
    }
}
