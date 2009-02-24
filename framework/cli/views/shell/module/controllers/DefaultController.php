<?php

class DefaultController extends CController
{
	public function actionIndex()
	{
		$this->render('index');
	}
}