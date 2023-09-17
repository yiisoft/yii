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

use PHPSpec\Runner\Formatter;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @author     Amjad Mohamed <amjad@alliedinsure.net>
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 * @since      File available since release 1.3.0
 */
abstract class FormatterAbstract implements Formatter
{
    
    /**
     * Listens to events from the reporter, and calls appropriate methods to
     * update the output
     * 
     * @param SplSubject $method
     * @param PHPSpec\Runnner\ReporterEvent $reporterEvent
     */
    public function update(\SplSubject $method, $reporterEvent = null)
    {
        switch ($reporterEvent->event) {
            case 'start':
                $this->_startRenderingExampleGroup($reporterEvent);
                break;
            case 'finish':
                $this->_finishRenderingExampleGroup();
                break;
            case 'status':
                $this->_renderExamples($reporterEvent);
                break;
            case 'exit':
                $this->output();
                $this->_onExit();
                break;
        }
    }
    
    abstract protected function _startRenderingExampleGroup($reporterEvent);
    abstract protected function _finishRenderingExampleGroup();
    abstract protected function _renderExamples($reporterEvent);
    abstract protected function _onExit();
    
} 