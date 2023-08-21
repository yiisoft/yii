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

namespace Phing\Task\Ext\PhpUnit\Formatter;

use Phing\Task\Ext\PhpUnit\PHPUnitTask;
use PHPUnit\Framework\TestResult;
use PHPUnit\Runner\Version;

/**
 * Prints Clover XML output of the test
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.formatter
 */
class CloverPHPUnitResultFormatter extends PHPUnitResultFormatter
{
    /**
     * @var TestResult
     */
    private $result = null;

    /**
     * PHPUnit version
     *
     * @var string
     */
    private $version = null;

    /**
     * @param PHPUnitTask $parentTask
     */
    public function __construct(PHPUnitTask $parentTask)
    {
        parent::__construct($parentTask);

        $this->version = Version::id();
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return ".xml";
    }

    /**
     * @return string
     */
    public function getPreferredOutfile()
    {
        return "clover-coverage";
    }

    /**
     * @param TestResult $result
     */
    public function processResult(TestResult $result)
    {
        $this->result = $result;
    }

    public function endTestRun()
    {
        $coverage = $this->result->getCodeCoverage();

        if (!empty($coverage)) {
            $cloverClass = '\SebastianBergmann\CodeCoverage\Report\Clover';
            $clover = new $cloverClass();

            $contents = $clover->process($coverage);

            if ($this->out) {
                $this->out->write($contents);
                $this->out->close();
            }
        }

        parent::endTestRun();
    }
}
