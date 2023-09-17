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

/**
 * Prints Clover HTML code coverage of the tests
 *
 * @author  Blair Cooper <dev@raincitysolutions.com>
 * @package phing.tasks.ext.formatter
 */
class CloverHtmlPHPUnitResultFormatter extends PHPUnitResultFormatter
{
    /**
     * @var TestResult
     */
    private $result = null;

    /**
     * @var string
     */
    private $toDir = '.';

    /**
     * @param PHPUnitTask $parentTask
     */
    public function __construct(PHPUnitTask $parentTask, string $toDir)
    {
        parent::__construct($parentTask);

        $this->toDir = $toDir;
    }

    /**
     * @param TestResult $result
     */
    public function processResult(TestResult $result): void
    {
        $this->result = $result;
    }

    public function endTestRun(): void
    {
        $coverage = $this->result->getCodeCoverage();

        if (!empty($coverage)) {
            $cloverClass = '\SebastianBergmann\CodeCoverage\Report\Html\Facade';
            $clover = new $cloverClass();
            $clover->process($coverage, $this->toDir);
        }

        parent::endTestRun();
    }
}
