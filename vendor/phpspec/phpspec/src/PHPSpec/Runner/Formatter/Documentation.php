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
namespace PHPSpec\Runner\Formatter;

use PHPSpec\Util\Filter;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Documentation extends Progress
{
    /**
     * Ident to apply to specdox lines. Allow for more identation in case
     * of shared examples and in the future nested contexts
     *
     * @var string
     */
    protected $_ident = '';
    
    /**
     * Implements observer events listener
     */
    public function update(\SplSubject $subject, $reporterEvent = null)
    {
        switch($reporterEvent->event) {
            case 'startShared':
                $shared = Filter::camelCaseToSpace($reporterEvent->example);
                $this->_ident = '  ';
                $this->putln("  behaves like " . $shared);
                break;
            case 'start':
                $this->putln($reporterEvent->example);
                break;
            case 'status':
                $this->specdox(
                    $reporterEvent->status, $reporterEvent->example,
                    $reporterEvent->message
                );
                break;
            case 'finishShared':
                $this->_ident = '';
                break;
            case 'exit':
                $this->output();
                exit;
                break;
        }
    }
    
    /**
     * Adds specdocx to the output
     * 
     * @param string $status
     * @param string $example
     * @param string $message
     */
    protected function specdox($status, $example, $message = '')
    {
        switch($status) {
            case '.':
                $this->put("  " . $this->_ident . $this->green($example));
                break;
            case '*':
                $this->put(
                    "  " . $this->_ident .
                    $this->yellow($example . " (PENDING: $message)")
                );
                break;
            case 'E':
                static $error = 1;
                $this->put(
                    "  " . $this->_ident .
                    $this->red($example . " (ERROR - " . ($error++) .")")
                );
                break;
            case 'F':
                static $failure = 1;
                $this->put(
                    "  " . $this->_ident . $this->red(
                        $example . " (FAILED - " . ($failure++) .")"
                    )
                );
                break;
        }
        $this->putln("");
    }
}