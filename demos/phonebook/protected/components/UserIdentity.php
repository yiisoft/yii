<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	/**
	 * Validates the username and password.
	 * This method should check the validity of the provided username
	 * and password in some way. In case of any authentication failure,
	 * set errorCode and errorMessage with appropriate values and return false.
	 * @param string username
	 * @param string password
	 * @return boolean whether the username and password are valid
	 */
	public function authenticate()
	{
		if($this->username==='demo' && $this->password==='demo')
			$this->errorCode=self::ERROR_NONE;
		else
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		return !$this->errorCode;
	}
}