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
class ClassLoader
{
    /**
     * Namespace used for the current class
     * 
     * @var string
     */
    protected $_namespace = '';
    
    /**
     * Convention factory
     * 
     * @var \PHPSpec\Loader\ConvetionFactory
     */
    protected $_convention;
    
    /**
     * Loads a example group object and returns it inside an array
     * 
     * @param string $fullPath
     * @return array 
     */
    public function load($fullPath)
    {
        $realPath   = realpath($fullPath);
        $specFile   = basename($realPath);
        $pathToFile = str_replace(
            DIRECTORY_SEPARATOR . "$specFile", '', $realPath
        );
        $convention = $this->getConventionFactory()->create($specFile);
        
        if ($realPath && !$convention->apply()) {
            return array();
        } elseif (!$realPath) {
            $this->assertFileIsAccessible($fullPath);
        }
        
        return array($this->loadExample(
            $pathToFile . DIRECTORY_SEPARATOR . $convention->getClassFile(),
            $convention->getClass()
        ));
    }
    
    /**
     * Creates an instance of a class and returns it. Adding the current
     * namespace to it
     * 
     * @param string $file
     * @param string $class
     * @return \PHPSpec\Specification\ExampleGroup
     */
    private function loadExample($file, $class)
    {
        $this->assertFileIsAccessible($file);

        $this->includeSpec($file, $class);
        $specClass = new \ReflectionClass($this->_namespace . $class);

        $this->_namespace = '';
        $specObject = $specClass->newInstance();
        return $specObject;
    }
    
    /**
     * Whether file is accessible
     * 
     * @param string $file
     * @throws \PHPSpec\Runner\Error
     */
    private function assertFileIsAccessible($file)
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new \PHPSpec\Runner\Error(
                "Could not include file \"$file\""
            );
        }
    }
    
    /**
     * Includes the file and throws an exception if class cannot be found
     * in file
     * 
     * @param string $file
     * @param string  $class
     * @throws \PHPSpec\Runner\Error
     * @return boolean
     */
    private function includeSpec($file, $class)
    {
        require_once $file;
        
        $classes = get_declared_classes();
        foreach ($classes as $declared) {
            if ($this->foundClass($declared, $class, $file)) {
                return true;
            }
        }
        throw new \PHPSpec\Runner\Error(
            "Could not find class \"$class\" in file \"$file\""
        );
    }
    
    /**
     * Whether the class can be found in declared class list taking namespace
     * into account
     * 
     * @param string $declared
     * @param string $class
     * @return boolean
     */
    private function foundClass($declared, $class, $file)
    {
        return $this->declaredContainsClassName($declared, $class)
               && ($this->declaredAndClassNamesAreTheSame($declared, $class)
               || $this->differenceIsANamespace($declared, $class, $file));
    }
    
    /**
     * Whether a declared class contains the name of my class
     * 
     * @param string $declared
     * @param string $class
     * @return boolean
     */
    private function declaredContainsClassName($declared, $class)
    {
        return strpos($declared, $class) !== false;
    }
    
    /**
     * Whether my class matches a declared class exactly
     * 
     * @param string $declared
     * @param string $class
     * @return boolean
     */
    private function declaredAndClassNamesAreTheSame($declared, $class)
    {
        return $declared === $class;
    }
    
    /**
     * Whether the difference between my class and a declared class is the
     * namespace
     * 
     * @param string $declared
     * @param string $class
     * @return boolean
     */
    private function differenceIsANamespace($declared, $class, $file)
    {
        $differenceIsANamespace = substr(
            $declared, 0 - strlen($class)
        ) === $class;
        if ($differenceIsANamespace) {
            $this->_namespace = $this->extractNamespace(
                $declared, $class, $file
            );
        }
        return $differenceIsANamespace;
    }
    
    /**
     * Extracts the namespace from a declared class, considering that the
     * namespace is the difference between it and my given class
     * 
     * @param string $declared
     * @param string $class
     * @return string
     */
    private function extractNamespace($declared, $class, $file)
    {
        $source = file_get_contents($file);
        preg_match('/namespace (.*);/', $source, $matches);
        return isset($matches[1]) ? $matches[1] . "\\" : '';
    }
    
    /**
     * Gets the convention factory
     * 
     * @return \PHPSpec\Loader\ConventionFactory
     */
    public function getConventionFactory()
    {
        if ($this->_convention === null) {
            $this->_convention = new ConventionFactory();
        }
        return $this->_convention;
    }
    
    /**
     * Sets the convention factory
     * 
     * @param \PHPSpec\Loader\ConventionFactory $convention
     */
    public function setConventionFactory(ConventionFactory $convention)
    {
        $this->_convention = $convention;
    }
}
