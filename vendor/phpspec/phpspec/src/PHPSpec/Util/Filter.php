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

/**
 * @category   PHPSpec
 * @package    PHPSpec
 * @copyright  Copyright (c) 2007-2009 Pádraic Brady, Travis Swicegood
 * @copyright  Copyright (c) 2010-2012 Pádraic Brady, Travis Swicegood,
 *                                     Marcello Duarte
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU Lesser General Public Licence Version 3
 */
class Filter
{
    /**
     * Converst camel case to dash
     *
     * @param string $pattern
     * @return string
     */
    public static function camelCaseToDash($pattern)
    {
        return self::camelCaseToSeparator($pattern, '-');
    }
    
    /**
     * Converst camel case to space
     *
     * @param string $pattern
     * @return string
     */
    public static function camelCaseToSpace($pattern)
    {
        return self::camelCaseToSeparator($pattern, ' ');
    }
    
    /**
     * Converst camel case to separator
     *
     * @param string $pattern
     * @return string
     */
    public static function camelCaseToSeparator($pattern, $sep)
    {
        $terms = preg_split(
            "/(?=[[:upper:]]|[[:digit:]])/", $pattern, -1, PREG_SPLIT_NO_EMPTY
        );

        $termsLowerCase = array_map('strtolower', self::concatNumbers($terms));
        return implode($sep, $termsLowerCase);
    }
    
    /**
     * Concatenate separated numberic array elements into one numberic element
     *
     * ex.: array ('a', '1', '0', 'b') becomes array ('a', '10', 'b')
     *
     * @param array $terms 
     * @return array
     */
    private static function concatNumbers(array $terms)
    {
        $newTerms = array();
        $term = current($terms);
        while ($term !== false) {
            if (is_numeric($term)) {
                while (isset($terms[key($terms) + 1]) &&
                       is_numeric($terms[key($terms) + 1])) {
                    $term = $term . $terms[key($terms) + 1];
                    next($terms);
                }
            }
            $newTerms[] = $term;
            $term = next($terms);
        }
        return $newTerms;
    }
}