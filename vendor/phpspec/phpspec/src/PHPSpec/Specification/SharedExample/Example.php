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
namespace PHPSpec\Specification\SharedExample;

use PHPSpec\Specification\BaseExample;
use PHPSpec\Specification\Result\Pending;
use PHPSpec\Specification\ExampleGroup;
use PHPSpec\Specification\SharedExample;

use PHPSpec\Runner\Reporter;

use PHPSpec\Util\ReflectionMethod;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Example extends BaseExample
{
    /**
     * The shared example
     *
     * @var string
     */
    protected $_sharedExample;
    
    /**
     * Creates the shared example
     *
     * @param ExampleGroup  $exampleGroup
     * @param string        $methodName
     * @param SharedExample $sharedExample
     */
    public function __construct(ExampleGroup $exampleGroup, $methodName,
        SharedExample $sharedExample)
    {
        $this->_exampleGroup = $exampleGroup;
        $this->_sharedExample = $sharedExample;
        $this->_methodName = $methodName;
    }
    
    /**
     * Runs the example
     *
     * @param Reporter $reporter
     */
    protected function runExample(Reporter $reporter)
    {
        $this->_exampleGroup->runSharedExample($this->_methodName);
    }
    
    /**
     * Marks example as pending if it is empty
     */
    protected function markExampleAsPendingIfItIsEmpty()
    {
        $method = new ReflectionMethod(
            $this->_sharedExample, $this->_methodName
        );
        if ($method->isEmpty()) {
            throw new Pending('empty example');
        }
    }
}