<?php

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

namespace Phing\Util;

use function filter_var;
use function is_bool;
use function is_string;
use function preg_match;
use function strlen;
use function strpos;
use function strrev;
use function trigger_error;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;

/**
 * String helper utility class.
 *
 * This class includes some Java-like functions for parsing strings,
 * as well as some functions for getting qualifiers / unqualifying phing-style
 * classpaths.  (e.g. "phing.util.StringHelper").
 *
 * @author Hans Lellelid <hans@xmpl.org>
 */
class StringHelper
{
    /**
     * Converts a string to a boolean according to Phing rules.
     *
     * This method has no type hints to avoid "type coercion".
     *
     * The following values are considered "true":
     *
     * - 'on' (string)
     * - 'true' (string)
     * - 'yes' (string)
     * - '1' (string)
     * - 1 (int)
     * - 1.0 (float)
     * - true (boolean)
     *
     * Everything else is "false". Also, string values are trimmed and case-insensitive.
     *
     * @param mixed $s Value to be converted
     *
     * @return bool
     */
    public static function booleanValue($s)
    {
        return filter_var($s, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * tests if a string is a representative of a boolean.
     *
     * This method has no type hints to avoid "type coercion".
     *
     * Rules:
     *
     * - Valid boolean values: true, false, 'true', 'false', 'on', 'off', 'yes', 'no', '1' and '0'.
     * - Anything else must not be considered boolean.
     * - This method is case-insensitive.
     * - Strings are trimmed.
     *
     * @param mixed $s The value to convert to a bool value
     *
     * @return bool
     */
    public static function isBoolean($s)
    {
        if (is_bool($s)) {
            return true; // it already is boolean
        }

        if (!is_string($s) || '' === trim($s)) {
            return false; // not a valid string for testing
        }

        return null !== filter_var($s, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    /**
     * tests if a string starts with a given string.
     *
     * @param string $check
     * @param string $string
     *
     * @return bool
     */
    public static function startsWith($check, $string)
    {
        if ('' === $check || $check === $string) {
            return true;
        }

        return 0 === strpos((string) $string, $check);
    }

    /**
     * tests if a string ends with a given string.
     *
     * @param string $check
     * @param string $string
     *
     * @return bool
     */
    public static function endsWith($check, $string)
    {
        if ('' === $check || $check === $string) {
            return true;
        }

        return 0 === strpos(strrev($string), strrev($check));
    }

    /**
     * a natural way of getting a subtring, php's circular string buffer and strange
     * return values suck if you want to program strict as of C or friends.
     *
     * @param string $string
     * @param int    $startpos
     * @param int    $endpos
     *
     * @return string
     */
    public static function substring($string, $startpos, $endpos = -1)
    {
        $len = strlen($string);
        $endpos = (int) ((-1 === $endpos) ? $len - 1 : $endpos);
        if ($startpos > $len - 1 || $startpos < 0) {
            trigger_error("substring(), Startindex out of bounds must be 0<n<{$len}", E_USER_ERROR);
        }
        if ($endpos > $len - 1 || $endpos < $startpos) {
            trigger_error("substring(), Endindex out of bounds must be {$startpos}<n<" . ($len - 1), E_USER_ERROR);
        }
        if ($startpos === $endpos) {
            return (string) $string[$startpos];
        }

        $len = $endpos - $startpos;

        return substr((string) $string, $startpos, $len + 1);
    }

    /**
     * Does the value correspond to a slot variable?
     *
     * @param string $value
     *
     * @return bool|int
     */
    public static function isSlotVar($value)
    {
        $value = trim($value);
        if ('' === $value) {
            return false;
        }

        return preg_match('/^%\{([\w\.\-]+)\}$/', $value);
    }

    /**
     * Extracts the variable name for a slot var in the format %{task.current_file}.
     *
     * @param string $var the var from build file
     *
     * @return string extracted name part
     */
    public static function slotVar($var)
    {
        return trim($var, '%{} ');
    }
}
