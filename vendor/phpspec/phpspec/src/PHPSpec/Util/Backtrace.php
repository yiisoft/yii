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
namespace PHPSpec\Util;

use PHPSpec\PHPSpec;

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Backtrace
{
    /**
     * Number of lines to display in backtrace by default
     */
    const NUMBER_OF_LINES = 3;
    
    
    /**
     * Returns source code from a file and line in a given position of the
     * backtrace
     * 
     * @param array   $trace
     * @param integer $index
     * @return string
     */
    public static function code($trace, $index = 0)
    {
        $code = '';
        $traceLine = $trace[$index];
        if (self::lineHasFileAndLine($traceLine)) {

            if (self::lineHasEval($traceLine)) {
                return self::code($trace, ++$index);
            }

            return self::readLine($traceLine['file'], $traceLine['line']);
        } elseif (isset($traceLine['function']) &&
                  $traceLine['function'] === 'PHPSpec_ErrorHandler') {
            return self::readLine(
                $traceLine['args'][2], $traceLine['args'][3]
            );
        } elseif (count($trace) > $index + 1) {
            return self::code($trace, ++$index);
        }
        return $code;
    }
    
    /**
     * Gets a file and a line inside a trace
     * 
     * @param array   $trace
     * @param integer $index
     * @return array<'file' => string, 'line' => integer>
     */
    public static function getFileAndLine(array $trace, $index = 0)
    {
        $traceLine = $trace[$index];
        if (self::lineHasFileAndLine($traceLine)) {

            if (self::lineHasEval($traceLine)) {
                return self::getFileAndLine($trace, ++$index);
            }

            return array (
                'file' => $traceLine['file'],
                'line' => $traceLine['line']
            );
        }
        return array();
    }
    
    /**
     * Returns nicely formatted backtrace
     * 
     * @param array   $trace
     * @param integer $limit
     * @return string
     */
    public static function pretty($trace, $limit = self::NUMBER_OF_LINES)
    {
        $formatted = '';
        foreach ($trace as $line) {
            if ($limit !== null && $limit === 0) {
                 return $formatted;
            }
            
            $prettyLine = '';
            if (isset($line['file'])) {
                $file = self::shortenRelativePath($line['file']);
                $prettyLine = self::prettyLine($file, $line['line']);
            }
            
            if ($formatted && $prettyLine &&
                strpos($formatted, $prettyLine) !== false) {
                continue;
            }
                        
            if ($limit !== null && !PHPSpec::testingPHPSpec()) {
                if (stristr($prettyLine, 'phpspec')) {
                    continue;
                }
            }
            
            $formatted .= $prettyLine;
            
            if ($limit !== null && $prettyLine) {
                $limit--;
            }
        }
        return $formatted;
    }
    
    /**
     * Shortens a path, converting an absolute into a relative path
     * 
     * @param string $path
     * @return mixed
     */
    public static function shortenRelativePath($path)
    {
        $cwd = getcwd();
        if (strpos($path, $cwd) === 0) {
            $path = str_replace($cwd, '.', $path);
        }
        return $path;
    }
    
    /**
     * Returns a line of code from a file, given its path and line
     * 
     * @param string  $path
     * @param integer $line
     * @throws \OutOfBoundsException
     * @return string
     */
    public static function readLine($path, $line)
    {
        if (is_readable($path)) {
            $source = file($path);
            return trim($source[$line - 1]);
        }
        return '';
    }
    
    /**
     * Checks whether a line is a part of a eval code
     * 
     * @param array $traceLine
     * @return boolean
     */
    private static function lineHasEval($traceLine)
    {
        return strpos($traceLine['file'], ': eval()\'d code') !== false;
    }
    
    /**
     * Checks whether a backtrace line has info on file an line
     * 
     * @param array $traceLine
     * @return boolean
     */
    private static function lineHasFileAndLine($traceLine)
    {
        return isset($traceLine['file']) && isset($traceLine['line']);
    }
    
    /**
     * Returns a backtrace line prettified
     * 
     * @param string  $file
     * @param integer $line
     * @return string
     */
    private static function prettyLine($file, $line)
    {
        return '     # ' .  $file . ':' . $line . PHP_EOL;
    }
}