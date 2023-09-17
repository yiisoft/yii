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

use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExceptionWrapper;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use Throwable;

/**
 * Prints plain text output of the test to a specified Writer.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.phpunit.formatter
 */
class PlainPHPUnitResultFormatter extends PHPUnitResultFormatter
{
    private $inner = "";

    /**
     * @return string
     */
    public function getExtension()
    {
        return ".txt";
    }

    /**
     * @return string
     */
    public function getPreferredOutfile()
    {
        return "testresults";
    }

    /**
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite): void
    {
        parent::startTestSuite($suite);

        $this->inner = "";
    }

    /**
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void
    {
        if ($suite->getName() === 'AllTests') {
            return;
        }

        $sb = "Testsuite: " . $suite->getName() . "\n";
        $sb .= "Tests run: " . $this->getRunCount();
        $sb .= ", Risky: " . $this->getRiskyCount();
        $sb .= ", Warnings: " . $this->getWarningCount();
        $sb .= ", Failures: " . $this->getFailureCount();
        $sb .= ", Errors: " . $this->getErrorCount();
        $sb .= ", Incomplete: " . $this->getIncompleteCount();
        $sb .= ", Skipped: " . $this->getSkippedCount();
        $sb .= ", Time elapsed: " . sprintf('%0.5f', $this->getElapsedTime()) . " s\n";

        if ($this->out !== null) {
            $this->out->write($sb);
            $this->out->write($this->inner);
        }

        parent::endTestSuite($suite);
    }

    /**
     * @param Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addError(Test $test, Throwable $e, float $time): void
    {
        parent::addError($test, $e, $time);

        $this->formatError("ERROR", $test, $e);
    }

    /**
     * @param Test $test
     * @param AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(
        Test $test,
        AssertionFailedError $e,
        float $time
    ): void {
        parent::addFailure($test, $e, $time);
        $this->formatError("FAILED", $test, $e);
    }

    /**
     * @param Test $test
     * @param AssertionFailedError $e
     * @param float $time
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        parent::addWarning($test, $e, $time);
        $this->formatError("WARNING", $test, $e);
    }

    /**
     * @param Test $test
     * @param AssertionFailedError $e
     * @param float $time
     */
    public function addRiskyTest(Test $test, Throwable $e, float $time): void
    {
        parent::addRiskyTest($test, $e, $time);
        $this->formatError("RISKY", $test, $e);
    }

    /**
     * @param Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addIncompleteTest(Test $test, Throwable $e, float $time): void
    {
        parent::addIncompleteTest($test, $e, $time);

        $this->formatError("INCOMPLETE", $test);
    }

    /**
     * @param Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addSkippedTest(Test $test, Throwable $e, float $time): void
    {
        parent::addSkippedTest($test, $e, $time);
        $this->formatError("SKIPPED", $test);
    }

    /**
     * @param $type
     * @param Test $test
     * @param Exception $e
     */
    private function formatError($type, Test $test, Exception $e = null)
    {
        if ($test != null) {
            $this->endTest($test, time());
        }

        $this->inner .= $test->getName() . " " . $type . "\n";

        if ($e !== null) {
            if ($e instanceof ExceptionWrapper) {
                $this->inner .= $e->getPreviousWrapped() ? $e->getPreviousWrapped()->getMessage() : $e->getMessage();
            } else {
                $this->inner .= $e->getMessage();
            }

            if ($e instanceof ExpectationFailedException && $e->getComparisonFailure()) {
                $this->inner .= $e->getComparisonFailure()->getDiff();
            }

            $this->inner .= "\n";
        }
    }

    public function endTestRun()
    {
        parent::endTestRun();

        if ($this->out != null) {
            $this->out->close();
        }
    }
}
