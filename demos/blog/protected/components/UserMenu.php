<?php

class UserMenu extends Portlet
{
	public function init()
	{
		if(isset($_POST['command']) && $_POST['command']==='logout')
		{
			Yii::app()->user->logout();
			$this->controller->redirect(Yii::app()->homeUrl);
		}
		$this->title=CHtml::encode(Yii::app()->user->name);
		parent::init();
	}

	protected function renderContent()
	{
		$this->render('userMenu');
	}
}