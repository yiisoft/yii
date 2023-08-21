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

use PHPSpec\Matcher\InvalidMatcher;

 /**
  * @category   PHPSpec
  * @package    PHPSpec
  * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
  * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
  *                                     Marcello Duarte
  * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
  */
class MatcherFactory
{

    const NAMESPACE_SEPARATOR = '\\';
    /**
     * Paths to matchers
     *
     * @var array
     */
    protected $_pathsToMatchers;

    /**
     * List of builtin matchers
     *
     * @var array
     */
    protected $_builtinMatchers = array();

    /**
     * Matchers registry
     *
     * @var associative array
     */
    protected $_matchers = array();

    /**
     * Namespace for the builtin matchers
     *
     * @var string
     */
    protected $_builtInNamespace;

    /**
     * Matcher factory is created with a path to matchers
     *
     * @param array $pathsToMatchers
     */
    public function __construct(array $pathsToMatchers = array())
    {
        $this->_pathsToMatchers = $pathsToMatchers;
        $this->_builtInNamespace = '\PHPSpec\Matcher\\';
    }

    /**
     * Create the matcher
     *
     * @param string $matcherName
     * @param string $expected
     * @return \PHPSpec\Matcher
     */
    public function create($matcherName, $expected = array())
    {
        if (empty($this->_matchers)) {
            $this->_buildRegistry();
        }

        if (!is_array($expected)) {
            $expected = array($expected);
        }

        if (!array_key_exists($matcherName, $this->_matchers)) {
            throw new InvalidMatcher(
                "Call to undefined method $matcherName"
            );
        }

        if ($this->_matchers[$matcherName]['path'] !== false) {
            require_once($this->_matchers[$matcherName]['path']);
        }
        $matcherClass = $this->_matchers[$matcherName]['namespace'] .
            strtoupper($matcherName[0]) . substr($matcherName, 1);
        $reflectedMatcher = new \ReflectionClass($matcherClass);
        if (!$reflectedMatcher->implementsInterface('PHPSpec\Matcher')) {
            throw new InvalidMatcherType(
                $this->_matchers[$matcherName]['namespace'] .
                strtoupper($matcherName[0]) . substr($matcherName, 1) .
                " must implement PHPSpec\Matcher"
            );
        }

        $expected = $expected === array() ? array(null) : $expected;
        $matcher = $reflectedMatcher->newInstanceArgs($expected);

        return $matcher;
    }

    /**
     * Builds the matchers registry
     *
     * @return void
     */
    private function _buildRegistry()
    {
        $this->_addBuiltinMatchersToRegistry();
        $this->_addCustomMatchersToRegistry();
    }

    /**
     * Adds builtin matchers to the registry
     *
     * @return void
     */
    private function _addBuiltinMatchersToRegistry()
    {
        if (empty($this->_builtInMatchers)) {
            $this->_builtInMatchers = $this->loadBuiltInMatchers();
        }

        foreach ($this->_builtInMatchers as $builtinMatcher) {
            $this->_matchers[$builtinMatcher] = array(
                'namespace' => $this->_builtInNamespace,
                'path' => false
            );
        }
    }

    protected function loadBuiltInMatchers()
    {
        $matchers = array();
        $dir = __DIR__;
        do {
            $dir = realpath(dirname($dir));
        } while (basename($dir) !== 'PHPSpec');

        $matcherDir = $dir . DIRECTORY_SEPARATOR . 'Matcher';

        $files = glob($matcherDir . DIRECTORY_SEPARATOR . '*.php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/implements Matcher/', $content)) {
                $fileName = basename(
                    $matcherDir . DIRECTORY_SEPARATOR . $file, '.php'
                );
                $matchers[] = strtolower($fileName[0]) . substr($fileName, 1);
            }
        }
        return $matchers;
    }

    /**
     * Adds custom matchers to the registry
     *
     * @return void
     */
    private function _addCustomMatchersToRegistry()
    {
        foreach ($this->_pathsToMatchers as $originalPath) {
            $this->_recursivelyRegisterMatchersOnFolder($originalPath);
        }
    }

    /**
     * Recursively registers matchers found on folder
     *
     * @param string $folder
     */
    private function _recursivelyRegisterMatchersOnFolder($originalPath)
    {
        $nameSpace = $this->_fromPathToNamespace($originalPath);
        $currentPath = $this->_findMatcherPath($originalPath);

        if ($currentPath !== false) {
            foreach (
                glob($currentPath . DIRECTORY_SEPARATOR . "*.php") as
                $matcherFile
            ) {
                $matcherName = basename($matcherFile, ".php");
                $matcherName = strtolower($matcherName[0]) .
                               substr($matcherName, 1);
                $this->_matchers[$matcherName] = array(
                    'namespace' => $nameSpace,
                    'path' => $matcherFile);
            }

            foreach (
                glob($currentPath . DIRECTORY_SEPARATOR . "*", GLOB_ONLYDIR)
                as $appendPath
            ) {
                $this->_recursivelyRegisterMatchersOnFolder(
                    $originalPath . DIRECTORY_SEPARATOR . basename($appendPath)
                );
            }
        }
    }

    /**
     * Find a namespace based on the path for the file
     *
     * @param string $path
     * @return string
     */
    private function _fromPathToNamespace($path)
    {
        $nameSpace = str_replace(
            DIRECTORY_SEPARATOR,
            self::NAMESPACE_SEPARATOR, $path
        );
        if (substr($nameSpace, -1) !== self::NAMESPACE_SEPARATOR) {
            $nameSpace .= self::NAMESPACE_SEPARATOR;
        }
        return $nameSpace;
    }

    /**
     * Find the path to a Matcher
     *
     * @param string $path
     * @return string|false
     */
    private function _findMatcherPath($path)
    {
        $includePaths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($includePaths as $child) {
            if (is_dir($child . DIRECTORY_SEPARATOR . $path)) {
                return $child . DIRECTORY_SEPARATOR . $path;
            }
        }
        return false;
    }
}
