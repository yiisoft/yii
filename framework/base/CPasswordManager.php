<?php

/**
 * CPasswordManager provides a simple API for secure password hashing and verification,
 * useful, for example, in storing passwords in a database.
 *
 *
 * CPasswordManager can be used as follows:
 *
 * Configure the application component in the application config file:
 * <code>
 *    return array(
 *        ...
 *        'components' => array(
 *            'passwordManager' => array(
 *                'class' => 'CPasswordManager',
 *                'cost' => 14,
 *            ),
 *            ....
 * </code>
 *
 * Get an instance. CPasswordManager will throw an exception here if the Blowfish hash algorithm is not
 * available in PHP's crypt() function:
 * <code>
 *    $pm = Yii::app()->passwordManager;
 * </code>
 *
 * Generate a hash from a password:
 * <code>
 *    $hash = $pw->hashPassword($password);
 * </code>
 * This hash can be stored in a database field such as: CHAR(64) CHARACTER SET latin1. The
 * hash is usually generated and saved to the database when the user provides a new password.
 * But it can also be useful to do so after validating a user's password in order to change
 * the cost or refresh the salt.
 *
 * To verify a password, fetch the user's saved hash from the database (into $hash) and:
 * <code>
 *    if ($pw->verifyPassword($password, $hash)
 *        // password is good
 *    else
 *        // password is bad
 * </code>
 *
 * CPasswordManager uses the Blowfish hash algorithm available in many PHP runtime
 * environments through the PHP {@link http://php.net/manual/en/function.crypt.php `crypt()`}
 * built-in function. At present (2012) this is the strongest algorithm available in PHP
 * and the only algorithm without some security concerns surrounding it. For this reason,
 * CPasswordManager fails to initialize when run in and environment that does not have
 * crypt() and its Blowfish option. Systems that have crypt()-Blowfish include:
 *  - Most *nix systems since PHP 4 (the algorithm is part of the library function crypt(3))
 *  - All PHP systems since 5.3.0
 *  - All PHP systems with the {@link http://www.hardened-php.net/suhosin/ Suhosin patch}
 *
 *
 *
 * @property int $cost Cost parameter of the Blowfish hash algorithm. The higher the cost,
 * the longer it takes to generate a hash and  to verify a password, consequently it also
 * slows down a brute-force attack. For best protection, set it to the highest value that
 * is tolerable on production servers.
 */
class CPasswordManager extends CApplicationComponent implements IApplicationComponent
{
    protected  $cost = 12;

    /**
     * @throws CException if the runtime system does not have required features.
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

    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Generate a secure hash from a password and a random salt using the
     * PHP {@link http://php.net/manual/en/function.crypt.php `crypt()`} built-in function
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
     * The PHP {@link http://php.net/manual/en/function.crypt.php `crypt()`} built-in function
     * requires, for the Blowfish hash algorithm, a salt string in a specific format:
     *  - "$2a$", in which the "a" may be replaced by "x" or "y", see PHP manual for details
     *  - a two digit cost parameter
     *  - "$"
     *  - 22 characters from the alphabet "./0-9A-Za-z".
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
