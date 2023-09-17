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

namespace Phing\Task\Ext\Coverage;

use Phing\Task;
use Phing\Type\Element\ClasspathAware;
use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Type\Excludes;
use Phing\Util\Properties;
use Phing\Util\StringHelper;
use Phing\Task\Ext\PhpUnit\PHPUnitUtil;

/**
 * Stops the build if any of the specified coverage threshold was not reached
 *
 * @author  Benjamin Schultz <bschultz@proqrent.de>
 * @package phing.tasks.ext.coverage
 * @since   2.4.1
 */
class CoverageThresholdTask extends Task
{
    use ClasspathAware;

    /**
     * Holds the exclusions
     *
     * @var Excludes
     */
    private $excludes = null;

    /**
     * Holds an optional database file
     *
     * @var File
     */
    private $database = null;

    /**
     * Holds the coverage threshold for the entire project
     *
     * @var integer
     */
    private $perProject = 25;

    /**
     * Holds the coverage threshold for any class
     *
     * @var integer
     */
    private $perClass = 25;

    /**
     * Holds the coverage threshold for any method
     *
     * @var integer
     */
    private $perMethod = 25;

    /**
     * Holds the minimum found coverage value for a class
     *
     * @var integer
     */
    private $minClassCoverageFound = null;

    /**
     * Holds the minimum found coverage value for a method
     *
     * @var integer
     */
    private $minMethodCoverageFound = null;

    /**
     * Number of statements in the entire project
     *
     * @var integer
     */
    private $projectStatementCount = 0;

    /**
     * Number of covered statements in the entire project
     *
     * @var integer
     */
    private $projectStatementsCovered = 0;

    /**
     * Whether to enable detailed logging
     *
     * @var boolean
     */
    private $verbose = false;

    /**
     * Sets the optional coverage database to use
     *
     * @param File The database file
     */
    public function setDatabase(File $database)
    {
        $this->database = $database;
    }

    /**
     * Sets the coverage threshold for entire project
     *
     * @param integer $threshold Coverage threshold for entire project
     */
    public function setPerProject($threshold)
    {
        $this->perProject = $threshold;
    }

    /**
     * Sets the coverage threshold for any class
     *
     * @param integer $threshold Coverage threshold for any class
     */
    public function setPerClass($threshold)
    {
        $this->perClass = $threshold;
    }

    /**
     * Sets the coverage threshold for any method
     *
     * @param integer $threshold Coverage threshold for any method
     */
    public function setPerMethod($threshold)
    {
        $this->perMethod = $threshold;
    }

    /**
     * Sets whether to enable detailed logging or not
     *
     * @param boolean $verbose
     */
    public function setVerbose($verbose)
    {
        $this->verbose = StringHelper::booleanValue($verbose);
    }

    /**
     * Filter covered statements
     *
     * @param  integer $var Coverage CODE/count
     * @return boolean
     */
    protected function filterCovered($var)
    {
        return ($var >= 0 || $var === -2);
    }

    /**
     * Create excludes object
     *
     * @return Excludes
     */
    public function createExcludes()
    {
        $this->excludes = new Excludes($this->project);

        return $this->excludes;
    }

    /**
     * Calculates the coverage threshold
     *
     * @param  string $filename The filename to analyse
     * @param  array $coverageInformation Array with coverage information
     * @throws BuildException
     */
    protected function calculateCoverageThreshold($filename, $coverageInformation)
    {
        $classes = PHPUnitUtil::getDefinedClasses($filename, $this->classpath);

        if (is_array($classes)) {
            foreach ($classes as $className) {
                // Skip class if excluded from coverage threshold validation
                if ($this->excludes !== null) {
                    if (in_array($className, $this->excludes->getExcludedClasses())) {
                        continue;
                    }
                }

                $reflection = new \ReflectionClass($className);
                $classStartLine = $reflection->getStartLine();

                // Strange PHP5 reflection bug, classes without parent class
                // or implemented interfaces seem to start one line off
                if (
                    $reflection->getParentClass() === null
                    && count($reflection->getInterfaces()) === 0
                ) {
                    unset($coverageInformation[$classStartLine + 1]);
                } else {
                    unset($coverageInformation[$classStartLine]);
                }

                reset($coverageInformation);

                $methods = $reflection->getMethods();

                foreach ($methods as $method) {
                    // PHP5 reflection considers methods of a parent class
                    // to be part of a subclass, we don't
                    if ($method->getDeclaringClass()->getName() != $reflection->getName()) {
                        continue;
                    }

                    // Skip method if excluded from coverage threshold validation
                    if ($this->excludes !== null) {
                        $excludedMethods = $this->excludes->getExcludedMethods();

                        if (isset($excludedMethods[$className])) {
                            if (
                                in_array($method->getName(), $excludedMethods[$className])
                                || in_array($method->getName() . '()', $excludedMethods[$className])
                            ) {
                                continue;
                            }
                        }
                    }

                    $methodStartLine = $method->getStartLine();
                    $methodEndLine = $method->getEndLine();

                    // small fix for XDEBUG_CC_UNUSED
                    if (isset($coverageInformation[$methodStartLine])) {
                        unset($coverageInformation[$methodStartLine]);
                    }

                    if (isset($coverageInformation[$methodEndLine])) {
                        unset($coverageInformation[$methodEndLine]);
                    }

                    if ($method->isAbstract()) {
                        continue;
                    }

                    $lineNr = key($coverageInformation);

                    while ($lineNr !== null && $lineNr < $methodStartLine) {
                        next($coverageInformation);
                        $lineNr = key($coverageInformation);
                    }

                    $methodStatementsCovered = 0;
                    $methodStatementCount = 0;

                    while ($lineNr !== null && $lineNr <= $methodEndLine) {
                        $methodStatementCount++;

                        $lineCoverageInfo = $coverageInformation[$lineNr];
                        // set covered when CODE is other than -1 (not executed)
                        if ($lineCoverageInfo > 0 || $lineCoverageInfo === -2) {
                            $methodStatementsCovered++;
                        }

                        next($coverageInformation);
                        $lineNr = key($coverageInformation);
                    }

                    if ($methodStatementCount > 0) {
                        $methodCoverage = ($methodStatementsCovered
                                / $methodStatementCount) * 100;
                    } else {
                        $methodCoverage = 0;
                    }

                    if ($methodCoverage < $this->perMethod && !$method->isAbstract()) {
                        throw new BuildException(
                            'The coverage (' . round($methodCoverage, 2) . '%) '
                            . 'for method "' . $method->getName() . '" is lower'
                            . ' than the specified threshold ('
                            . $this->perMethod . '%), see file: "'
                            . $filename . '"'
                        );
                    }

                    if (
                        $methodCoverage < $this->perMethod
                        && $method->isAbstract()
                        && $this->verbose === true
                    ) {
                        $this->log(
                            'Skipped coverage threshold for abstract method "'
                            . $method->getName() . '"'
                        );
                    }

                    // store the minimum coverage value for logging (see #466)
                    if ($this->minMethodCoverageFound !== null) {
                        if ($this->minMethodCoverageFound > $methodCoverage) {
                            $this->minMethodCoverageFound = $methodCoverage;
                        }
                    } else {
                        $this->minMethodCoverageFound = $methodCoverage;
                    }
                }

                $classStatementCount = count($coverageInformation);
                $classStatementsCovered = count(
                    array_filter(
                        $coverageInformation,
                        [$this, 'filterCovered']
                    )
                );

                if ($classStatementCount > 0) {
                    $classCoverage = ($classStatementsCovered
                            / $classStatementCount) * 100;
                } else {
                    $classCoverage = 0;
                }

                if ($classCoverage < $this->perClass && !$reflection->isAbstract()) {
                    throw new BuildException(
                        'The coverage (' . round($classCoverage, 2) . '%) for class "'
                        . $reflection->getName() . '" is lower than the '
                        . 'specified threshold (' . $this->perClass . '%), '
                        . 'see file: "' . $filename . '"'
                    );
                }

                if (
                    $classCoverage < $this->perClass
                    && $reflection->isAbstract()
                    && $this->verbose === true
                ) {
                    $this->log(
                        'Skipped coverage threshold for abstract class "'
                        . $reflection->getName() . '"'
                    );
                }

                // store the minimum coverage value for logging (see #466)
                if ($this->minClassCoverageFound !== null) {
                    if ($this->minClassCoverageFound > $classCoverage) {
                        $this->minClassCoverageFound = $classCoverage;
                    }
                } else {
                    $this->minClassCoverageFound = $classCoverage;
                }

                $this->projectStatementCount += $classStatementCount;
                $this->projectStatementsCovered += $classStatementsCovered;
            }
        }
    }

    public function main()
    {
        if ($this->database === null) {
            $coverageDatabase = $this->project
                ->getProperty('coverage.database');

            if (!$coverageDatabase) {
                throw new BuildException(
                    'Either include coverage-setup in your build file or set '
                    . 'the "database" attribute'
                );
            }

            $database = new File($coverageDatabase);
        } else {
            $database = $this->database;
        }

        $this->log(
            'Calculating coverage threshold: min. '
            . $this->perProject . '% per project, '
            . $this->perClass . '% per class and '
            . $this->perMethod . '% per method is required'
        );

        $props = new Properties();
        $props->load($database);

        foreach ($props->keys() as $filename) {
            $file = unserialize($props->getProperty($filename));

            // Skip file if excluded from coverage threshold validation
            if ($this->excludes !== null) {
                if (in_array($file['fullname'], $this->excludes->getExcludedFiles())) {
                    continue;
                }
            }

            $this->calculateCoverageThreshold(
                $file['fullname'],
                $file['coverage']
            );
        }

        if ($this->projectStatementCount > 0) {
            $coverage = ($this->projectStatementsCovered
                    / $this->projectStatementCount) * 100;
        } else {
            $coverage = 0;
        }

        if ($coverage < $this->perProject) {
            throw new BuildException(
                'The coverage (' . round($coverage, 2) . '%) for the entire project '
                . 'is lower than the specified threshold ('
                . $this->perProject . '%)'
            );
        }

        $this->log(
            'Passed coverage threshold. Minimum found coverage values are: '
            . round($coverage, 2) . '% per project, '
            . round($this->minClassCoverageFound, 2) . '% per class and '
            . round($this->minMethodCoverageFound, 2) . '% per method'
        );
    }
}
