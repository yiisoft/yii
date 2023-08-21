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
namespace PHPSpec\Specification\Result;

use \PHPSpec\Specification\Result;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Error extends Result
{
    /**
     * @param string  $message
     * @param integer $code
     * @param string  $file
     * @param integer $line
     * @param array   $backtrace
     */
    public function __construct($message = null, $code = 0, $file = null,
                                $line = null, $backtrace = null)
    {
        parent::__construct($message, $code);
        if (!is_null($file)) {
            $this->file = $file;
        }
        if (!is_null($line)) {
            $this->line = $line;
        }
        if (!is_null($backtrace)) {
            $this->trace = $backtrace;
        }
    }
    
    /**
     * Gets the error type based on the error code
     * 
     * @return string
     */
    public function getErrorType()
    {
        switch ($this->code) {
            case E_ERROR:
                return 'PHP Error';
                break;
        
            case E_WARNING:
                return 'PHP Warning';
                break;
        
            case E_NOTICE:
                return 'PHP Notice';
                break;
        
            case E_DEPRECATED:
                return 'PHP Deprecated';
                break;
                
            case E_USER_ERROR:
                return 'User Error';
                break;
        
            case E_USER_WARNING:
                return 'User Warning';
                break;
        
            case E_USER_NOTICE:
                return 'User Notice';
                break;
        
            case E_USER_DEPRECATED:
                return 'User Deprecated';
                break;
        
            default:
                return 'Unknown';
                break;
        }
    }
}