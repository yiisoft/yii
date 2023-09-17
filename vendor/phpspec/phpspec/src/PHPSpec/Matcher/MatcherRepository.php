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

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class MatcherRepository
{
    /**
     * User defined matchers
     * 
     * @var array
     */
    protected static $_matchers;
    
    /**
     * Adds matchers to the repository
     * 
     * @param string  $matcher
     * @param Closure $definition
     */
    public static function add($matcher, $definition)
    {
        self::$_matchers[$matcher] = $definition;
    }
    
    /**
     * Checks if there is a matcher of a given name
     * 
     * @param string $matcher
     */
    public static function has($matcher)
    {
        return isset(self::$_matchers[$matcher]);
    }
    
    /**
     * Returns the matcher identified by a given name
     * 
     * @param string $matcher
     * @return Closure
     */
    public static function get($matcher)
    {
        return self::$_matchers[$matcher];
    }
}