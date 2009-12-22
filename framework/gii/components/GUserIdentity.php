<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$module = Yii::app()->getModule('gii');
		
        if (strcmp($module->username, $this->username)==0 && strcmp($module->password, $this->password)){
			$this->errorCode=self::ERROR_NONE;
            $user->save(false);
            $this->setState('iduser', $user->idusuario);
        }else{
            $this->errorCode = self::ERROR_UNKNOWN_IDENTITY;
        }
		return !$this->errorCode;
	}
}