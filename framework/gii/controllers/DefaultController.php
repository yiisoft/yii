<?php

class DefaultController extends CController
{
	public $layout='/layouts/column1';

	public function getPageTitle()
	{
		if($this->action->id==='index')
			return 'Gii: a Web-based code generator for Yee';
		else
			return 'Gii - '.ucfirst($this->action->id).' Generator';
	}

	public function actionIndex()
	{
		$this->render('index');
	}

	public function actionError()
	{
	    if($error=Yee::app()->errorHandler->error)
	    {
	    	if(Yee::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=Yee::createComponent('gii.models.LoginForm');

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(array('index'));
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yee::app()->user->logout(false);
		$this->redirect(array('index'));
	}
}