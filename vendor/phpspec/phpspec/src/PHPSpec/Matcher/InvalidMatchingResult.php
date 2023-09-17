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

use PHPSpec\Specification\Result\Exception;
use PHPSpec\Util\Backtrace;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class InvalidMatchingResult extends Exception
{
    public function __construct($message)
    {
        $caller = array_slice(debug_backtrace(), 2, 1);
        parent::__construct(new \Exception($message));
        $this->_line = isset($caller[0]['line']) ? $caller[0]['line'] : null;
        $this->_file = isset($caller[0]['file']) ? $caller[0]['file'] : null;
    }
    
    public function getSnippet($index = 0)
    {
        return Backtrace::readLine($this->_file, $this->_line);
    }
}