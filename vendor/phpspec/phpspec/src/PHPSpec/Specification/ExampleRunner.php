<?php
/**
 * PHPSpec
 *
 * LICENSE
 *
 * This file is subject to the GNU Lesser General Public License Version 3
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/lgpl-3.0.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@phpspec.net so we can send you a copy immediately.
 *
 * @category  PHPSpec
 * @package   PHPSpec
 * @copyright Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                    Marcello Duarte
 * @license   http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
namespace PHPSpec\Specification;

use PHPSpec\Specification\SharedExample\Example as Shared;

use PHPSpec\Runner\Reporter;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class ExampleRunner
{
    const ALL_EXAMPLES = '.*';
    
    /**
     * The example factory
     *
     * @var PHPSpec\Specification\ExampleFactory
     */
    protected $_exampleFactory;
    
    /**
     * Pattern of the name of the example to run
     *
     * @var string
     */
    protected $_examplesToRun = ExampleRunner::ALL_EXAMPLES;
    
    /**
     * Example groups that have started (only when -e is used)
     *
     * @var array
     */
    protected $_groupsStarted = array();

    /**
     * Example groups that have finished (only when -e is used)
     *
     * @var array
     */
    protected $_groupsFinished = array();
    
    /**
     * Example group
     *
     * @var ExampleGroup
     */
    protected $_exampleGroup;
    
    /**
     * Creates the runners
     *
     * @param ExampleGroup $exampleGroup
     */
    public function __construct(ExampleGroup $exampleGroup)
    {
        $this->_exampleGroup = $exampleGroup;
    }
    
    /**
     * Sets the example group
     *
     * @param ExampleGroup $exampleGroup 
     */
    public function setExampleGroup(ExampleGroup $exampleGroup)
    {
        $this->_exampleGroup = $exampleGroup;
    }
    
    /**
     * Runs all examples inside an example group
     * 
     * @param \PHPSpec\Runner\Reporter           $reporter
     */
    public function run(Reporter $reporter)
    {
        if ($this->_examplesToRun !== ExampleRunner::ALL_EXAMPLES) {
            $this->checkGroupStarted($reporter);
            $this->runExamples($reporter);
            $this->checkGroupFinished($reporter);
            return;
        }

        $reporter->exampleGroupStarted($this->_exampleGroup);
        $this->runExamples($reporter);
        $reporter->exampleGroupFinished($this->_exampleGroup);
    }
    
    /**
     * Checks if example group has started, if it hasn't then it will notify
     * the reporter that it has
     */
    private function checkGroupStarted(Reporter $reporter)
    {
        $groupName = get_class($this->_exampleGroup);
        foreach ($this->getMethodNames($reporter) as $method) {
            if ($this->methodIsAnExample($method) &&
                $this->filterExample($method) &&
                $this->groupHasntStarted($groupName)) {
                $reporter->exampleGroupStarted($this->_exampleGroup);
                $this->_groupsStarted[] = $groupName;
            }
        }
    }
    
    /**
     * Checks if example group has finished, if it hasn't then it will notify
     * the reporter that it has
     */
    private function checkGroupFinished(Reporter $reporter)
    {
        $groupName = get_class($this->_exampleGroup);
        foreach ($this->getMethodNames($reporter) as $method) {
            if ($this->methodIsAnExample($method) &&
                $this->filterExample($method) &&
                $this->groupHasntFinished($groupName)) {
                $reporter->exampleGroupFinished($this->_exampleGroup);
                $this->_groupsFinished[] = $groupName;
            }
        }
    }
    
    /**
     * Whether the example group has had any example ran
     *
     * @return boolean
     */
    private function groupHasntStarted()
    {
        return !in_array($this->_exampleGroup, $this->_groupsStarted);
    }
    
    /**
     * Whether the example group has finished running the examples
     *
     * @return boolean
     */
    private function groupHasntFinished()
    {
        return !in_array($this->_exampleGroup, $this->_groupsFinished);
    }
    
    /**
     * Creates and runs all examples (methods started with 'it')
     * 
     * @param \PHPSpec\Runner\Reporter           $reporter
     */
    protected function runExamples(Reporter $reporter)
    {
        foreach ($this->getMethodNames($reporter) as $methodName) {
            if ($this->methodIsAnExample($methodName) &&
                $this->filterExample($methodName)) {
                $example = $this->createExample($methodName);
                
                if ($this->_exampleGroup->behavesLikeAnotherObject()) {
                    if (!$example instanceof Shared) {
                        $reporter->sharedExampleFinished(new $sharedExample);
                    }
                }
                
                if ($this->_exampleGroup->behavesLikeAnotherObject()) {
                    $sharedExample = $this->_exampleGroup->getBehavesLike();
                    $reporter->sharedExampleStarted(new $sharedExample);
                }
                
                $example->run($reporter);
            }
        }
    }
    
    /**
     * Gets the example group method names
     *
     * @param $reporter Reporter
     * @return array
     */
    private function getMethodNames(Reporter $reporter)
    {
        $object = new \ReflectionObject($this->_exampleGroup);
        $methodNames = array();

        if ($this->_exampleGroup->behavesLikeAnotherObject()) {
            $behaveLike = $this->_exampleGroup->getBehavesLike();
            $sharedExamples = new \ReflectionObject(
                new $behaveLike
            );
            foreach ($sharedExamples->getMethods() as $method) {
                $methodNames[] = $method->getName();
                $this->_exampleGroup->addSharedExample(
                    $sharedExamples->newInstance(), $method->getName()
                );
            }
        }
        
        foreach ($object->getMethods() as $method) {
            $methodNames[] = $method->getName();
        }
        return $methodNames;
    }
    
    /**
     * Whether the method name starts with it, indicating it is an example
     *
     * @param string $name 
     * @return boolean
     */
    private function methodIsAnExample($name)
    {
        return strtolower(substr($name, 0, 2)) === 'it';
    }
    
    /**
     * If I am filtering examples with the -e|--example flag this will return
     * true if the current example matches the filter, causing the example to
     * run
     *
     * @param string $name 
     * @return boolean
     */
    private function filterExample($name)
    {
        return preg_match("/$this->_examplesToRun/i", $name);
    }
    
    /**
     * Creates an example
     * 
     * @param string                             $example
     * @return \PHPSpec\Specification\Example
     */
    protected function createExample($example)
    {
        return $this->getExampleFactory()->create(
            $this->_exampleGroup, $example
        );
    }
    
    /**
     * Gets the example factory
     * 
     * @return PHPSpec\Specification\ExampleFactory
     */
    public function getExampleFactory()
    {
        if ($this->_exampleFactory === null) {
            $this->_exampleFactory = new ExampleFactory;
        }
        return $this->_exampleFactory;
    }
    
    /**
     * Sets the example factory
     * 
     * @param PHPSpec\Specification\ExampleFactory $factory
     */
    public function setExampleFactory(ExampleFactory $factory)
    {
        $this->_exampleFactory = $factory;
    }
    
    /**
     * Sets the runner to run only a single example
     *
     * @param string $example
     */
    public function runOnly($example)
    {
        $this->_examplesToRun = $example;
    }
}