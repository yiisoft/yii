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
namespace PHPSpec;

use PHPSpec\Runner\Reporter;
use PHPSpec\Runner\Formatter\Factory as FormatterFactory;
use PHPSpec\Matcher\MatcherFactory;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class World
{
    
    /**
     * Parsed options
     *
     * @var array
     */
    protected $_options;
    
    /**
     * The reporter
     *
     * @var \PHPSpec\Runner\Reporter
     */
    protected $_reporter;
    
    /**
     * The formatter factory
     *
     * @var \PHPSpec\Runner\Formatter\Factory
     */
    protected $_formatterFactory;
    
    /**
     * The Matcher Factory
     *
     * @var PHPSpec\Matcher\MatcherFactory
     */
    private $_matcherFactory;
    
    /**
     * Macros file
     *
     * @var string
     */
    protected $_macrosFile;
    
    /**
     * Gets a option
     * 
     * @param string $name
     * @return mixed
     */
    public function getOption($name)
    {
        return isset($this->_options[$name]) ? $this->_options[$name] : null;
    }
    
    /**
     * Sets the option
     * 
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function setOption($name, $value)
    {
        if (isset($this->_options[$name])) {
            return $this->_options[$name] = $value;            
        }
        return false;
    }
    
    /**
     * Gets an option
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * Sets the options
     * 
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
    }
    
    /**
     * Gets the reporter
     * 
     * @return \PHPSpec\Runner\Reporter
     */
    public function getReporter()
    {
        return $this->_reporter;
    }
    
    /**
     * Sets the reporter
     * 
     * @param \PHPSpec\Runner\Reporter $reporter
     */
    public function setReporter($reporter)
    {
        $this->_reporter = $reporter;
    }
    
    /**
     * Sets the backtrace to true so reporter will display a long backtrace
     * when exceptions occur
     */
    public function showLongBacktrace()
    {
        $this->setOption('backtrace', true);
    }
    
    /**
     * Sets the backtrace to false so reporter will not display a long
     * backtrace when exceptions occur
     */
    public function showShortBacktrace()
    {
        $this->setOption('backtrace', false);
    }
    
    /**
     * Sets the colour option to true so specific formatter will add colours
     * to the report
     */
    public function showColours()
    {
        $this->setOption('c', true);
        $this->setOption('colour', true);
        $this->setOption('color', true);
    }
    
    /**
     * Sets the colour option to false so specific formatter will hide colours
     * from the report
     */
    public function noColours()
    {
        $this->setOption('c', false);
        $this->setOption('colour', false);
        $this->setOption('color', false);
    }
    
    /**
     * Tells the runner to run a single example
     *
     * @param string $exampleToRun 
     */
    public function runExamplesWith($pattern)
    {
        $this->setOption('example', $pattern);
    }
    
    /**
     * Attaches a formatter to the reporter
     *
     * @param string $formatter 
     */
    public function attachFormatter($formatter)
    {
        $this->setOption('formatter', $formatter);
        $formatterObject = $this->getFormatterFactory()->create(
            $formatter, $this->getReporter()
        );
        $this->getReporter()->setFormatters(array($formatterObject));
    }
    
    /**
     * Gets the formatter factory
     * 
     * @return \PHPSpec\Runner\Formatter\Factory
     */
    public function getFormatterFactory()
    {
        if ($this->_formatterFactory === null) {
            $this->_formatterFactory = new FormatterFactory;
        }
        return $this->_formatterFactory;
    }
    
    /**
     * Sets the formatter factory
     * 
     * @param \PHPSpec\Runner\Formatter\Factory $factory
     */
    public function setFormatterFactory(FormatterFactory $factory)
    {
        $this->_formatterFactory = $factory;
    }
    
    /**
     * Tells the reporter to abort when an error, failure or exception
     * happens 
     */
    public function failFast()
    {
        $this->setOption('fail-fast', true);
    }
    
    /**
     * Tells the interceptor to look for matchers in the matcher paths passed
     *
     * @param string|array $matcherPaths
     */
    public function includeMatchers($matcherPaths)
    {
        if (is_string($matcherPaths)) {
            $matcherPaths = explode(':', $matcherPaths);
        }
        
        if (!is_array($matcherPaths)) {
            throw new \InvalidArgumentException(
                'includeMatchers expects a string or an array ' . 
                gettype($matcherPaths) . ' found instead'
            );
        }
        
        $paths = array_merge(
            $this->getOption('include-matchers'),
            $matcherPaths
        );
        $this->setOption('include-matchers', $paths);
        $this->_matcherFactory = new MatcherFactory(
            $this->getOption('include-matchers')
        );
    }
    
    /**
     * Returns the Matcher Factory
     *
     *  @return PHPSpec\Matcher\MatcherFactory
     */
     public function getMatcherFactory()
     {
         if ($this->_matcherFactory === null) {
             $this->_matcherFactory = new MatcherFactory(
                 $this->getOption('include-matchers')
             );
         }
         return $this->_matcherFactory;
     }
     
     /**
      * Includes macros
      *
      * @param string $macrosFile
      */
     public function includeMacros($macrosFile)
     {
         if (is_dir($this->_options['specFile'])) {
             $macrosFile = $this->_options['specFile'] . DIRECTORY_SEPARATOR .
                           $macrosFile;
         }
         if (!file_exists($macrosFile) || !is_readable($macrosFile)) {
             throw new Exception("Macro file $macrosFile not found");
         }
         $this->_macrosFile = $macrosFile;
     }
     
     /**
      * Retrieves the macros file
      *
      * @return string
      */
     public function getMacros()
     {
         return $this->_macrosFile;
     }
}