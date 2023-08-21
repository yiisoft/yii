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

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Textmate extends Html
{
     const TEXTMATE_URL = 'txmt://open?url=file://%s&line=%s';

    /**
     * Renders examples specdox
     *
     * @param ReporterEvent $reporterEvent
     */
    protected function _renderExamples($reporterEvent)
    {
        $this->_examples .= $this->specdox(
            $reporterEvent->status, $reporterEvent->example,
            $reporterEvent->message, $this->_addTextMateUrlToBacktrace(
                $reporterEvent->backtrace
            ), $reporterEvent->exception
        );
    }
    
    private function _addTextMateUrlToBacktrace($backtrace)
    {
        $backtraceLines = '';
        
        foreach ($this->_convertBacktraceToArray($backtrace) as $line) {
            $backtraceLines .= $this->_addTextMateUrlToBacktraceLine($line);
        }

        return $backtraceLines;
    }
    
    private function _convertBacktraceToArray($backtrace)
    {
        $asArray = explode(PHP_EOL, $backtrace);
        
        $asArrayWithNoEmptyElements = array_filter(
            $asArray, function($each) {
                return !empty($each);
            }
        );
        
        $asArrayWithNoEmptyElementsAndTrimmed = array_map(
            function($each) {
                return ltrim($each, '    # ');
            }, $asArrayWithNoEmptyElements
        );
        
        return $asArrayWithNoEmptyElementsAndTrimmed;
    }
    
    private function _addTextMateUrlToBacktraceLine($backtraceLine)
    {
        list ($path, $line) = explode(':', $backtraceLine);
        $url = sprintf(self::TEXTMATE_URL, realpath($path), $line);
        return sprintf("<a href=\"%s\">%s</a>", $url, $backtraceLine);
    }

}