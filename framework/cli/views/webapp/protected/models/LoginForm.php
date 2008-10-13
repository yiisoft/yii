<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe;
	public $verifyCode;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
		return array(
			array('username, password', 'required'),
			array('verifyCode', 'captcha', 'allowEmpty'=>!extension_loaded('gd')),
			array('password', 'authenticate'),
		);
	}

	/**
	 * Declares attribute labels.
	 * If not declared here, an attribute would have a label
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'verifyCode'=>'Verification Code',
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate($attribute,$params)
	{
		if(!$this->hasErrors())  // we only want to authenticate when no input errors
		{
			$identity=new UserIdentity($this->username,$this->password);
			if($identity->authenticate())
			{
				$duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
				Yii::app()->user->login($identity,$duration);
			}
			else
				$this->addError('password','Incorrect password.');
		}
	}
}
