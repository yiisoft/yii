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
class Color
{
    /**
     * Existing colours in PHPSpec with the equivalent ansi escape code
     * 
     * @var unknown_type
     */
    protected static $_colors = array(
        'green' => "\033[32m%s\033[m",
        'red' => "\033[31m%s\033[m",
        'grey' => "\033[37m%s\033[m",
        'yellow' => "\033[33m%s\033[m",
    );
    
    /**
     * Decorates with green ansi colour
     * @param string $output
     * 
     * @return string
     */
    public static function green($output)
    {
        return sprintf(self::$_colors['green'], $output);
    }
    
    /**
     * Decorates with red ansi colour
     * @param string $output
     * 
     * @return string
     */
    public static function red($output)
    {
        return sprintf(self::$_colors['red'], $output);        
    }
    
    /**
     * Decorates with yellow ansi colour
     * @param string $output
     * 
     * @return string
     */
    public static function yellow($output)
    {
        return sprintf(self::$_colors['yellow'], $output);        
    }
    
    /**
     * Decorates with grey ansi colour
     * @param string $output
     * 
     * @return string
     */
    public static function grey($output)
    {
        return sprintf(self::$_colors['grey'], $output);
    }
}