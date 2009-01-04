<?php

class UserLogin extends Portlet
{
	public $title='Login';

	protected function renderContent()
	{
		$user=new LoginForm;
		if(isset($_POST['LoginForm']))
		{
			$user->attributes=$_POST['LoginForm'];
			if($user->validate())
				$this->controller->refresh();
		}
		$this->render('userLogin',array('user'=>$user));
	}
}
