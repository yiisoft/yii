<?php
/**
 * This file contains classes implementing password manager functions.
 *
 * @author Tom Worster <fsb@thefsb.org>
 */

/**
 * CPasswordManager provides a simple API for secure password hashing and verification.
 *
 * CPasswordManager uses the Blowfish hash algorithm available in many PHP runtime
 * environments through the PHP {@link http://php.net/manual/en/function.crypt.php crypt()}
 * built-in function. As of Dec 2012 it is the strongest algorithm available in PHP
 * and the only algorithm without some security concerns surrounding it. For this reason,
 * CPasswordManager fails to initialize when run in and environment that does not have
 * crypt() and its Blowfish option. Systems with the option include:
 * (1) Most *nix systems since PHP 4 (the algorithm is part of the library function crypt(3));
 * (2) All PHP systems since 5.3.0; (3) All PHP systems with the
 * {@link http://www.hardened-php.net/suhosin/ Suhosin patch}.
 * For more information about password hashing, crypt() and Blowfish, please read
 * the Yii Wiki article
 * {@link http://www.yiiframework.com/wiki/425/use-crypt-for-password-storage/ Use crypt() for password storage}.
 * and the
 * PHP RFC {@link http://wiki.php.net/rfc/password_hash Adding simple password hashing API}.
 *
 * CPasswordManager can be used as follows. Configure the application component in the application configuration:
 * <pre>
 * 'components' => array(
 *     'passwordManager' => array(
 *         'class' => 'CPasswordManager',
 *         'cost' => 14,
 *     ),
 *     ....
 * </pre>
 * When you first use the CPasswordManager application component, it will throw an
 * exception if the Blowfish hash algorithm is not
 * available in PHP's crypt() function.
 *
 * Generate a hash from a password:
 * <pre>
 * $hash = Yii::app()->passwordManager->hashPassword($password);
 * </pre>
 * This hash can be stored in a database (e.g. CHAR(64) CHARACTER SET latin1). The
 * hash is usually generated and saved to the database when the user enters a new password.
 * But it can also be useful to generate and save a hash after validating a user's
 * password in order to change the cost or refresh the salt.
 *
 * To verify a password, fetch the user's saved hash from the database (into $hash) and:
 * <pre>
 * if (Yii::app()->passwordManager->verifyPassword($password, $hash)
 *     // password is good
 * else
 *     // password is bad
 * </pre>
 *
 * @property int $cost Cost parameter of the Blowfish hash algorithm.
 *
 * @author Tom Worster <fsb@thefsb.org>
 * @package system.base
 * @since 1.1.14
 */
class CPasswordManager extends CApplicationComponent implements IApplicationComponent
{
    /**
     * @var int Cost parameter used by the Blowfish hash algorithm.
     * The higher the cost,
     * the longer it takes to generate a hash and to verify a password, consequently it also
     * slows down a brute-force attack. For best protection, set it to the highest value that
     * is tolerable on production servers.
     */
    protected  $cost = 12;

    /**
     * Initialize the CPasswordManager application component.
     * @throws CException if the runtime system does not have PHP crypt() with the Blowfish hash option.
     */
    public function init()
    {
        if (!function_exists('crypt')) {
            throw new CException(Yii::t('yii',
                    '{class} requires the PHP crypt() function. This system does not have it.',
                    array("{class}" => __CLASS__)
                )
            );
        }
        if (!defined('CRYPT_BLOWFISH') || !CRYPT_BLOWFISH) {
            throw new CException(Yii::t('yii',
                    '{class} requires the Blowfish option of the PHP crypt() function. This system does not have it.',
                    array("{class}" => __CLASS__)
                )
            );
        }
        parent::init();
    }

    /**
     * Set the cost parameter used by the Blowfish hash algorithm.
     * @param int $cost cost parameter to use when hashing passwords.
     * @throws CException if cost parameters is invalid.
     */
    public function setCost($cost)
    {
        if (!is_numeric($cost)) {
            throw new CException(Yii::t('yii',
                '{class}::$cost must be a number.',
                array("{class}" => __CLASS__)
            ));
        }
        $cost = (int) $cost;
        if ($cost < 4 || $cost > 30) {
            throw new CException(Yii::t('yii',
                '{class}::$cost must be between 4 and 31.',
                array("{class}" => __CLASS__)
            ));
        }
        $this->cost = $cost;
    }

    /**
     * Return the cost parameter used by the Blowfish hash algorithm.
     * @return int cost parameter used when hashing passwords.
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Generate a secure hash from a password and a random salt using the
     * PHP {@link http://php.net/manual/en/function.crypt.php crypt()} built-in function
     * with the Blowfish hash option.
     *
     * @param string $password The password to be hashed.
     *
     * @throws CException on bad password parameter.
     * @return string The password hash string, ASCII and not longer than 64 characters.

     */
    public function hashPassword($password)
    {
        if (!is_string($password) || $password === '') {
            throw new CException(Yii::t('yii', 'Cannot hash a password that is empty or not a string.'));
        }
        return crypt($password, $this->generateSalt());
    }

    /**
     * Verify a password against a hash.
     *
     * @param string $password The password to verify.
     * @param string $hash The hash to verify the password against.
     *
     * @return bool True if the password matches the hash.
     * @throws CException on bad hash parameter.
     */
    public function verifyPassword($password, $hash)
    {
        if (!$password) {
            return false;
        }
        if (!preg_match('{^\$2[axy]\$(\d\d)\$[\./0-9A-Za-z]{22}}', $hash, $matches)
            || $matches[1] < 4
            || $matches[1] > 30
        ) {
            throw new CException(Yii::t('yii', 'Unrecognized hash format. ' . __CLASS__ . ' '));
        }
        return $hash === crypt($password, $hash);
    }

    /**
     * Generates a salt that can be used to generate a password hash.
     *
     * The PHP {@link http://php.net/manual/en/function.crypt.php crypt()} built-in function
     * requires, for the Blowfish hash algorithm, a salt string in a specific format:
     *  "$2a$" (in which the "a" may be replaced by "x" or "y" see PHP manual for details),
     *  a two digit cost parameter,
     *  "$",
     *  22 characters from the alphabet "./0-9A-Za-z".
     *
     * @return string the salt
     */
    protected function generateSalt()
    {
        // Get 20 * 8bits of pseudo-random entropy from mt_rand().
        $rand = '';
        for ($i = 0; $i < 20; ++$i) {
            $rand .= chr(mt_rand(0, 255));
        }
        // Add the microtime for a little more entropy.
        $rand .= microtime();
        // Mix the bits cryptographically into a 20-byte binary string.
        $rand = sha1($rand, true);
        // Form the prefix that specifies Blowfish algo and cost parameter.
        $salt = sprintf("$2a$%02d$", (int) $this->cost);
        // Append the random salt data in the required base64 format.
        $salt .= str_replace('+', '.', substr(base64_encode($rand), 0, 22));
        return $salt;
    }
}
