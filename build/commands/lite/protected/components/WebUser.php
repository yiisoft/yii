<?php

/**
 * WebUser class.
 * WebUser extends CWebUser by implementing authentication
 * and other user-session related features.
 */
class WebUser extends CWebUser
{
	/**
	 * Validates the username and password.
	 * This method is invoked by {@link CWebUser::login}.
	 * The default implementation simply checks if both
	 * username and password are 'demo'.
	 * In real applications, you normally validate them
	 * against a database.
	 * @param string username
	 * @param string password
	 * @return boolean whether the username and password are valid
	 */
	public function validate($username,$password)
	{
		return ($username==='demo' && $password==='demo');
	}
}