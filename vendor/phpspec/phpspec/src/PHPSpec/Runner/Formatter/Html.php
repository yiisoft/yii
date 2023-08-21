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

use PHPSpec\Util\Backtrace;
use PHPSpec\Specification\Result\DeliberateFailure;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Html extends Progress
{
    /**
     * All results
     * 
     * @var string
     */
    protected $_result = '';
    
    /**
     * All examples in a group
     * 
     * @var string
     */
    protected $_examples = '';
    
    /**
     * Prints the report in a HTML format
     */
    public function output()
    {
        if ($this->justShowAMessage()) {
            return;
        }

        $template = new \Text_Template(
            $this->templateDir() . '/Report.html.dist'
        );
        $template->setVar(
            array(
                'totals' => $this->getSummary(),
                'results' => $this->getResults()
            )
        );
        $this->put($template->render());
    }
    
    /**
     * Returns totals to be added the page
     * 
     * @return string
     */
    protected function getSummary()
    {
        $template = new \Text_Template(
            $this->templateDir() . '/Totals.html.dist'
        );
        $template->setVar(
            array(
                'totals' => $this->getTotals(),
                'time' => $this->_reporter->getRuntime()
            )
        );
        return $template->render();
    }
    
    /**
     * Gets the result
     * 
     * @return string
     */
    protected function getResults()
    {
        return $this->_result;
    }
    
    /**
     * Gets the template directory
     * 
     * @return string
     */
    protected function templateDir ()
    {
        return realpath(dirname(__FILE__)) . '/Html/Template';
    }
    
    /**
     * Inhibits ansii colors in Html formatter
     *
     * @return boolean
     */
    public function showColors()
    {
        return false;
    }
    
    /**
     * Outputs to the browser using echo
     * 
     * @param string $output
     */
    public function put($output)
    {
        echo $output;
    }
     
    /**
     * Starts rendering example group
     *
     * @param ReporterEvent $reporterEvent
     */
    protected function _startRenderingExampleGroup($reporterEvent)
    {
        static $groupIndex = 1;
        $template = new \Text_Template(
            $this->templateDir() . '/GroupStart.html.dist'
        );
        $template->setVar(
            array(
                'index' => $groupIndex++,
                'name'  => $reporterEvent->example
            )
        );
        $this->_result .= $template->render();
    }
     
    /**
     * Finishes rendering example group
     */
    protected function _finishRenderingExampleGroup()
    {
        $template = new \Text_Template(
            $this->templateDir() . '/GroupEnd.html.dist'
        );
        $template->setVar(array('examples' => $this->_examples));
        $this->_result .= $template->render();
        $this->_examples = '';
    }
       
    /**
     * Renders examples specdox
     *
     * @param ReporterEvent $reporterEvent
     */
    protected function _renderExamples($reporterEvent)
    {
        $this->_examples .= $this->specdox(
            $reporterEvent->status, $reporterEvent->example,
            $reporterEvent->message, $reporterEvent->backtrace,
            $reporterEvent->exception
        );
    }
        
    /**
     * Renders a specdox
     * 
     * @param string $status
     * @param string $example
     * @param string $message
     * @param string $backtrace
     * @param \Exception $e
     * @return string
     */
    protected function specdox($status, $example, $message = '',
                               $backtrace = '', $e = null)
    {
        switch($status) {
            case '.':
                $template = new \Text_Template(
                    $this->templateDir() . '/Passed.html.dist'
                );
                $template->setVar(array('description' => $example));
                return $template->render();
                break;
            case '*':
                static $pending = 1;
                $template = new \Text_Template(
                    $this->templateDir() . '/Pending.html.dist'
                );
                $template->setVar(
                    array(
                        'description' => $example . " (PENDING: $message)",
                        'index' => $pending++
                    )
                );
                return $template->render();
                break;
            case 'E':
                static $error = 1;
                $template = new \Text_Template(
                    $this->templateDir() . '/Failed.html.dist'
                );
                $template->setVar(
                    array (
                        'description' => $example . " (ERROR - " .
                                         ($error) .")",
                        'message' => $message,
                        'backtrace' => $backtrace,
                        'code' => $this->getCode($e),
                        'index' => $error++
                    )
                );
                return $template->render();
                break;
            case 'F':
                static $failure = 1;
                $template = new \Text_Template(
                    $this->templateDir() . '/Failed.html.dist'
                );
                $template->setVar(
                    array(
                        'description' => $example .
                                         " (FAILED - " . ($failure) .")",
                        'message' => $message,
                        'backtrace' => $backtrace,
                        'code' => $this->getCode($e),
                        'index' => $failure++
                    )
                );
                return $template->render();
                break;
        }
    }
    
    /**
     * Gets the code based on the exception backtrace
     * 
     * @param \Exception $e
     * @return string
     */
    protected function getCode($e)
    {
        if (!$e instanceof \Exception) {
            return '';
        }
        
        if (!$e instanceof \PHPSpec\Specification\Result\DeliberateFailure) {
            $traceline = Backtrace::getFileAndLine($e->getTrace(), 1);
        } else {
            $traceline = Backtrace::getFileAndLine($e->getTrace());
        }
        $lines = '';
        if (!empty($traceline)) {
            $lines .= $this->getLine($traceline, -2);
            $lines .= $this->getLine($traceline, -1);
            $lines .= $this->getLine($traceline, 0, 'offending');
            $lines .= $this->getLine($traceline, 1);
        }
        
        $template = new \Text_Template(
            $this->templateDir() . '/Code.html.dist'
        );
        $template->setVar(array('code' => $lines));
        return $template->render();
    }
    
    /**
     * Cleans and returns a line. Removes php tag added to make highlight-string
     * work
     * 
     * @param unknown_type $traceline
     * @param unknown_type $relativePosition
     * @param unknown_type $style
     * @return Ambigous <string, mixed>
     */
    protected function getLine($traceline, $relativePosition, $style = 'normal')
    {
        $line = new \Text_Template($this->templateDir() . '/Line.html.dist');
        $code = str_replace(
            array('<span style="color: #0000BB">&lt;?php&nbsp;</span>',
                  '<code>', '</code>'),
            '',
            highlight_string(
                '<?php ' . Backtrace::readLine(
                    $traceline['file'],
                    $traceline['line'] + $relativePosition
                ), true
            )
        );
        $code = preg_replace('/\n/', '', $code);
        $code = preg_replace(
            '/<span style="color: #0000BB">&lt;\?php&nbsp;(.*)(<\/span>+?)/',
            '$1', $code
        );
        $line->setVar(
            array(
                'line' => $traceline['line'] + $relativePosition,
                'class' => $style,
                'code' => ' ' . $code
            )
        );
        return $line->render();
    }
}