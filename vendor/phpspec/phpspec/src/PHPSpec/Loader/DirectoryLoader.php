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
class DirectoryLoader extends ClassLoader
{
    
    /**
     * Loads the directory recursively
     * 
     * @param string $specDir
     * @param array  $ignore
     * @return array
     */
    public function load($specDir, $ignore = array())
    {
        $ignore = $this->lookForIgnoreConfig($specDir, $ignore);
        $directory = new \DirectoryIterator($specDir);
        $loaded = array();

        foreach ($directory as $file) {
            if ($file->isDot()) {
                continue;
            }
            if ($this->fileIsNotInIgnoreList($file, $ignore)) { 
                if ($file->isDir()) {
                    $loaded = array_merge(
                        $loaded, $this->load($file->getRealpath(), $ignore)
                    );
                } else {
                    $example = parent::load($file->getRealpath());
                    if ($example !== false && $example !== array(false)) {
                        if (!is_array($example)) {
                            $example = array($example);
                        }
                        $loaded = array_merge($loaded, $example);
                    }
                }
            }
        }

        return $loaded;
    }
    
    /**
     * Whether the file is not in the .specignore
     * 
     * @param string $file
     * @param array  $ignore
     * @return boolean
     */
    private function fileIsNotInIgnoreList($file, $ignore)
    {
        return !in_array($file->getRealpath(), $ignore);
    }
    
    /**
     * Looks for ignore configuration in the spec directory
     * 
     * @param unknown_type $specDir
     * @param unknown_type $ignore
     * @return multitype:
     */
    private function lookForIgnoreConfig($specDir, $ignore = array())
    {
        $ignored = $ignore;
        if (empty($ignore) && file_exists($specDir . '/.specignore')) {
            $ignored = array();
            $ignore = array_merge($ignore, file($specDir . '/.specignore'));
            foreach ($ignore as $path) {
                $ignored[] = trim(realpath($specDir) . ltrim($path, '.'));
            }
        }
        return $ignored;
    }
}