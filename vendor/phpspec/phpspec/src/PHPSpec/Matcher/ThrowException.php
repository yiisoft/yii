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
namespace PHPSpec\Matcher;

/**
 * @see \PHPSpec\Matcher
 */
use PHPSpec\Matcher;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class ThrowException implements Matcher
{
    /**
     * The exception class name you are comparing with
     * 
     * @var mixed
     */
    protected $_expectedException;
    
    /**
     * The exception message are comparing with
     * 
     * @var mixed
     */
    protected $_expectedMessage;

    /**
     * The actual exception class name
     * 
     * @var object
     */
    protected $_actualException;

    /**
     * The actual message
     * 
     * @var object
     */
    protected $_actualMessage;
    
    /**
     * Constructs the matcher with the value expected
     * 
     * @param mixed $exception
     */
    public function __construct($exception, $message = null)
    {
        $this->_expectedException = $exception;
        $this->_expectedMessage = $message;
    }
    
    /**
     * Describes Matcher specific implementation 
     *
     * @param mixed $actualException
     * @return boolean
     */
    public function matches($actualException, $actualMessage = null)
    {
        $this->_actualException = $actualException;
        $this->_actualMessage = $actualMessage;
        if (isset($this->_expectedMessage)) {
            return ltrim($this->_actualException, '\\')  ===
                   ltrim($this->_expectedException, '\\')  &&
                   $this->_actualMessage === $this->_expectedMessage;
        }
        return ltrim($this->_actualException, '\\') ===
               ltrim($this->_expectedException, '\\');
    }

    /**
     * Returns the failure message to be displayed
     * 
     * @return string
     */
    public function getFailureMessage()
    {
        if (isset($this->_expectedException)) {
            if (isset($this->_expectedMessage)) {
                if ($this->_actualException === null) {
                    return 'expected to throw exception ' .
                       $this->_expectedException .
                       ' with message "' . $this->_expectedMessage .
                       '", got no exception (using throwException())';
                } elseif ($this->_expectedMessage !== $this->_actualMessage) {
                    return 'expected to throw exception ' .
                       $this->_expectedException .
                       ' with message "' . $this->_expectedMessage .
                       '", got message "' . $this->_actualMessage .
                       '" (using throwException())';
                }
            }
            
            if ($this->_expectedException !== $this->_actualException) {
                return 'expected to throw exception ' .
                       $this->export($this->_expectedException) .
                       ', got ' . $this->export($this->_actualException) .
                       ' (using throwException())';
            }
        }        
    }

    /**
     * Returns the negative failure message in case
     * of using should not instead of should
     * 
     * @return string
     */
    public function getNegativeFailureMessage()
    {
        if (isset($this->_expectedException)) {
            if (isset($this->_expectedMessage)) {
                if ($this->_actualException === null) {
                    return 'expected not to throw exception ' .
                       $this->_expectedException .
                       ' with message "' . $this->_expectedMessage .
                       '", and got no exception (using throwException())';
                } elseif ($this->_expectedMessage !== $this->_actualMessage) {
                    return 'expected not to throw ' .
                           $this->export($this->_actualException) .
                           ' and got ' .
                           $this->export($this->_expectedException) .
                           ' (using throwException())';
                }
            }
        }
        
        if ($this->_expectedException !== $this->_actualException) {
            return 'expected ' . $this->export($this->_actualException) .
                   ' not to be thrown but got ' .
                   $this->export($this->_expectedException) .
                   ' (using throwException())';
        }
    }

    /**
     * Describes the matching
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'throw exception ' . $this->_expectedException .
               (isset($this->_expectedMessage) ? ' with message ' .
               $this->_expectedMessage : '');
    }
    
    /**
     * Wrapps value cleanning it a bit
     * 
     * @param string $value
     * @return string
     */
    private function export($value)
    {
        return $value === null ?
               'no exception' :
               str_replace('\\\\', '\\', var_export($value, true));
    }
}