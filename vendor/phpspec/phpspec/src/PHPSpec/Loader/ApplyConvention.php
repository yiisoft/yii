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
namespace PHPSpec\Loader;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class ApplyConvention
{
    /**
     * The name of the spec class
     * 
     * @var string
     */
    protected $_class;
    
    /**
     * The name of the spec file
     * 
     * @var string
     */
    protected $_classFile;
    
    /**
     * The spec name passed in the command line
     * 
     * @var string
     */
    protected $_spec;
    
    public function __construct($spec)
    {
        $this->_spec = $spec;
    }
    
    /**
     * Sets the class and class file based on the spec argument
     * 
     * Convention; For loading spec files and classes on command line
     * 
     * Convention #1: Specs are reflected in Filenames which follow the
     * format of "Describe*", e.g. "DescribeNewBowlingGame" defined in
     * "DescribeNewBowlingGame.php".
     * 
     * Convention #2: Specs are reflected in the Filename by removing
     * the "Describe" prefix and appending a "Spec" suffix, e.g.
     * "DescribeNewBowlingGame" defined in "NewBowlingGameSpec.php".
     * 
     * Conventions are case sensitive. Both Spec and Describe are expected
     * to commence with a capital letter. On the command line, the .php
     * prefix is optional.
     * 
     * @throws \PHPSpec\Exception
     */
    public function apply()
    {
        $follow = false;
        if ($this->isPhpFile()) {
            $this->_classFile = $this->_spec;
            
            if ($this->endsWithExtensionAndSpec()) {
                $this->_class = 'Describe' . $this->stripExtensionAndSpec();
                $follow = true;
            } else {
                if ($this->startsWithDescribe()) {
                    $follow = true;
                }
                $this->_class = $this->stripExtension();
            }
            
        } else {
            
            $this->_classFile = $this->_spec . '.php';
            $this->_class     = $this->_spec;
            
            if ($this->endsWithSpec()) {
                $this->_class = 'Describe' . $this->stripSpec();
                $follow = true;
            } elseif ($this->startsWithDescribe()) {
                $follow = true;
            }
        }
        
        return $follow;
    }
    
    /**
     * Gets the class
     * 
     * @return string
     */
    public function getClass()
    {
        return $this->_class;
    }
    
    /**
     * Gets the class file
     * 
     * @return string
     */
    public function getClassFile()
    {
        return $this->_classFile;
    }
    
    /**
     * Checks whether spec is a PHP file
     * 
     * @return boolean
     */
    private function isPhpFile()
    {
        return substr($this->_spec, -4) === '.php';
    }
    
    /**
     * Checks whether spec ends with "Spec.php"
     * 
     * @return boolean
     */
    private function endsWithExtensionAndSpec()
    {
        return substr($this->_spec, -8) === 'Spec.php';
    }
    
    /**
     * Removes the .php from the spec file
     * 
     * @return string
     */
    private function stripExtensionAndSpec()
    {
        if (substr($this->_spec, -8) === 'Spec.php') {
            return substr($this->_spec, 0, strlen($this->_spec) - 8);
        }
        return $this->_spec;
    }
    
    /**
     * Removes the .php from the spec file
     * 
     * @return string
     */
    private function stripExtension()
    {
        if ($this->isPhpFile()) {
            return substr($this->_spec, 0, strlen($this->_spec) - 4);
        }
        return $this->_spec;
    }
    
    /**
     * Checks whether spec ends with "Spec"
     * 
     * @return boolean
     */
    private function endsWithSpec()
    {
        return substr($this->_spec, -4) === 'Spec';
    }
    
    /**
     * Checks whether spec starts with "Describe"
     * 
     * @return boolean
     */
    private function startsWithDescribe()
    {
        return strpos($this->_spec, 'Describe') === 0;
    }
}