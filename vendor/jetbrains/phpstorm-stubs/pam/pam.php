<?php

use JetBrains\PhpStorm\Pure;

/**
 * Authorize against a PAM unix database.
 *
 * @param string $username <p>
 * The username to check.
 * </p>
 * @param string $password <p>
 * The user-supplied password to check.
 * </p>
 * @param string $error <p>
 * Output parameter to put any error messages in.
 * </p>
 * @param bool $check_account_management <p>
 * Call <b>pam_acct_mgmt()</b> to check account expiration and access. (Requires root access!)
 * </p>
 * @param string $service_name <p>
 * PAM service name to use. (Defaults to "php")
 * </p>
 * @return bool Returns a bool when complete. If false, <b>$error</b> contains any error messages generated.
 */
#[Pure]
function pam_auth(string $username, string $password, string $error, bool $check_account_management = true, string $service_name = 'php') {}

/**
 * Change a password for a PAM unix account.
 *
 * @param string $username <p>
 * The username to check.
 * </p>
 * @param string $old_password <p>
 * The current password for the account.
 * </p>
 * @param string $new_password <p>
 * The new password for the account.
 * </p>
 * @param string $error <p>
 * Output parameter to put any error messages in.
 * </p>
 * @param string $service_name <p>
 * PAM service name to use. (Defaults to "php")
 * </p>
 * @return bool Returns a bool when complete. If false, <b>$error</b> contains any error messages generated.
 */
#[Pure]
function pam_chpass(string $username, string $old_password, string $new_password, string $error, string $service_name = 'php') {}
