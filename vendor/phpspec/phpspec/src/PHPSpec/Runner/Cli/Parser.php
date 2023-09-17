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
namespace PHPSpec\Runner\Cli;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Parser implements \PHPSpec\Runner\Parser
{
    /**
     * Valid options
     * 
     * @var array
     */
    protected $_options = array(
        'noneGiven'        => false,
        'c'                => false,
        'color'            => false,
        'colour'           => false,
        'h'                => false,
        'help'             => false,
        'b'                => false,
        'backtrace'        => false,
        'version'          => false,
        'f'                => 'p',
        'formatter'        => 'p',
        'specFile'         => '',
        'fail-fast'        => false,
        'e'                => false,
        'example'          => false,
        'bootstrap'        => false,
        'include-matchers' => array()
    );
    
    /**
     * Properties that takes values
     *
     * @var array
     */
    protected $_takesAValue = array(
        'f',
        'formatter',
        'e',
        'example',
        'bootstrap',
        'include-matchers'
    );
    
    /**
     * Aliases
     * 
     * @var array
     */
     protected $_aliases = array(
        'c' => array('color', 'colour'),
        'h' => 'help',
        'b' => 'backtrace',
        'f' => 'formatter',
        'e' => 'example'
     );
    
    /**
     * Valid formatters
     * 
     * @var array
     */
     protected $_validFormatters = array(
        'p',
        'd',
        'h',
        'j',
        't',
        'progress',
        'documentation',
        'html',
        'junit',
        'textmate'
     );
     
    /**
     * 
     *
     * @var mixed
     */
    protected $_arguments;
    
    /**
     * Parses command line arguments
     *
     * @param array $arguments
     * @return array|null
     */
    public function parse(array $arguments)
    {
        $this->_arguments = $arguments;
        $this->removeProgramNameFromArguments();
        if (!empty($this->_arguments)) {
            $this->extractSpecFile();
            return $this->convertArgumentIntoOptions();
        }
        throw new \PHPSpec\Runner\Cli\Error(
            'Invalid number of arguments. Type -h for help'
        );
    }
    
    /**
     * Removes phpspec script name from command line argument list
     */
    protected function removeProgramNameFromArguments()
    {
        if (is_file($this->_arguments[0])) {
            array_shift($this->_arguments);
        }
    }
    
    /**
     * Extracts spec file. If the first argument is not a - or -- option
     * it should be a spec filename
     */
    private function extractSpecFile()
    {
        if ($this->_arguments[0]{0} !== '-') {
            $this->_options['specFile'] = $this->_arguments[0];
            array_shift($this->_arguments);
        }
    }
    
    /**
     * Converts arguments into options
     * 
     * @return array
     */
    private function convertArgumentIntoOptions()
    {
        $arguments = new \ArrayIterator($this->getArguments());

        while ($arguments->valid()) {
            $argument = $arguments->current();
            if ($this->isLongOption($argument)) {
                $this->convertLongArgumentstoOptions($arguments, $argument);
                continue;
            } elseif ($this->isShortOption($argument)) {
                $this->convertShortArgumentsToOptions($arguments, $argument);
            }
            $arguments->next();
        }
        return $this->_options;
    }
    
    /**
     * Converts long arguments, e.g. --argument value, to options
     * 
     * @param string $argument
     */
    private function convertLongArgumentstoOptions($arguments, $argument)
    {
        if ($this->takesAValue(substr($argument, 2))) {
            $this->checkValueIsInNextArgument($arguments);
        } else {
            $this->setOption(substr($argument, 2), true);
        }
        $arguments->next();
    }
    
    /**
     * Converts short arguments, e.g. -chv, to options
     * 
     * @param string $argument
     */
    private function convertShortArgumentsToOptions($arguments, $argument)
    {
        $options = str_split(substr($argument, 1));
        $shortOptions = new \ArrayIterator($options);
        while ($shortOptions->valid()) {
            $flag = $shortOptions->current();
            if ($this->takesAValue($flag)) {
                if ($this->checkValueIsInNextOption($shortOptions)) {
                    continue;
                }
                $this->checkValueIsInNextArgument($arguments);
            } else {
                $this->setOption($flag, true);
                $shortOptions->next();
            }
        }
    }
    
    /**
     * Checks if value is the next option
     * 
     * @param array $options
     * @throws \PHPSpec\Runner\Cli\Error
     */
    public function checkValueIsInNextOption($options)
    {
        $option = $options->current();
        $options->next();
        $value = $options->current();
        try {
            if ($value === null) {
                return false;
            } 
            $this->setOption($option, $value);
            $options->next();
        } catch (\Exception $e) {
            if ($value) {
                throw $e;
            }
            return false;
        }
        return true;
    }
    
    /**
     * Checks if value is the next argument
     * 
     * @param array $arguments
     * @throws \PHPSpec\Runner\Cli\Error
     */
    public function checkValueIsInNextArgument($arguments)
    {
        $option = ltrim($arguments->current(), '-');
        $arguments->next();
        $this->setOption($option, $arguments->current());
    }
    
    /**
     * Whether this parameter takes a value
     * 
     * @param string $option
     * @return boolean
     */
    protected function takesAValue($option)
    {
        return in_array($option, $this->_takesAValue);
    }
    
    /**
     * Whether this is a long option
     * 
     * @param string $option
     * @return boolean
     */
    protected function isLongOption($option)
    {
        return substr($option, 0, 2) === '--';
    }
    
    /**
     * Whether this is a short option
     * 
     * @param string $option
     * @return boolean
     */
    protected function isShortOption($option)
    {
        return substr($option, 0, 1) === '-';
    }
    
    /**
     * Gets the arguments
     * @return array
     */
    public function getArguments()
    {
        return $this->_arguments;
    }
    
    /**
     * Gets an option
     * 
     * @param string $name
     * @return NULL|string
     */
    public function getOption($name)
    {
        if ($this->hasOption($name)) {
            return $this->_options[$name];
        }
    }
    
    /**
     * Checks whether an option exists
     * 
     * @param string $name
     * @return bool
     */
    public function hasOption($name)
    {
        return isset($this->_options[trim($name)]);
    }
    
    /**
     * Sets an option
     * 
     * @param string $name
     * @param string $value
     */
    public function setOption($name, $value)
    {
        if (!$this->hasOption($name)) {
            throw new \PHPSpec\Runner\Cli\Error("Invalid option $name");
        }
        
        $option = $this->getOptionLongVersion($name);
        
        $setter = "set" . str_replace("-", "", ucfirst($option));
        $this->$setter($value);
    }
    
    /**
     * Gets a long version of an option
     * 
     * @param string $name
     * @return string
     */
    protected function getOptionLongVersion($name)
    {
        if ($this->isOneLetterOption($name)) {
            if ($this->hasMultipleAliases($name)) {
                return $this->_aliases[trim($name[0])][0];
            }
            return $this->_aliases[trim($name[0])];
        }
        return $name;
    }
    
    /**
     * Whether this is an one letter option
     * 
     * @param string $name
     * @return boolean
     */
    public function isOneLetterOption($name)
    {
        return strlen(trim($name)) === 1;
    }
    
    /**
     * Whether this has multiple aliases
     * 
     * @param string $name
     * @return boolean
     */
    public function hasMultipleAliases($name)
    {
        return is_array($this->_aliases[trim($name[0])]);
    }
    
    /**
     * Sets the formatter
     * @param string $formatter
     */
    public function setFormatter($formatter)
    {
        if (in_array($formatter, $this->_validFormatters)) {
            $this->_options['f'] = $this->_options['formatter'] = $formatter;
        } else {
            throw new \PHPSpec\Runner\Cli\Error(
                'Invalid argument for formatter'
            );
        }
        
    }
    
    /**
     * Sets the colour
     * 
     * @param boolean $color
     */
    public function setColor($color)
    {
        $this->_options['c'] = $this->_options['colour'] =
        $this->_options['color'] = $color;
    }
    
    /**
     * Sets the help option
     * 
     * @param boolean $help
     */
    public function setHelp($help)
    {
        $this->_options['h'] = $this->_options['help'] = $help;
    }
    
    /**
     * Sets the version option
     * 
     * @param boolean $version
     */
    public function setVersion($version)
    {
        $this->_options['version'] = $version;
    }
    
    /**
     * Sets the autospec option
     * 
     * @param boolean $autospec
     */
    public function setAutospec($autospec)
    {
        $this->_options['a'] = $this->_options['autospec'] = $autospec;
    }
    
    /**
     * Sets the backtrace option
     * 
     * @param boolean $backtrace
     */
    public function setBacktrace($backtrace)
    {
        $this->_options['b'] = $this->_options['backtrace'] = $backtrace;
    }
    
    /**
     * Sets a fail fast option
     * 
     * @param boolean $failfast
     */
    public function setFailfast($failfast)
    {
        $this->_options['failfast'] = $failfast;
    }
    
    /**
     * Sets the example option
     * 
     * @param boolean $example
     */
    public function setExample($example)
    {
        $this->_options['e'] = $this->_options['example'] = $example;
    }
    
    /**
     * Sets the bootstrap option
     *
     * @param string $filename
     */
    public function setBootstrap($filename)
    {
        if (empty($filename) || is_null($filename)) {
            throw new \PHPSpec\Runner\Cli\Error(
                'Bootstrap file should be given for bootstrap option'
            );
        }
        
        $this->_options['bootstrap'] = $filename;
    }
    
    /**
     * Sets the Include Matchers option
     *
     * @param string $matchersPaths 
     */
     public function setIncludeMatchers($matchersPaths)
     {
         $this->_options['include-matchers'] = explode(':', $matchersPaths);
     }
}