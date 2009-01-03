<?php

class MainMenu extends Portlet
{
	public $title='Main Menu';

	protected function renderContent()
	{
		$this->render('mainMenu');
	}
}