<?php

class RecentComments extends Portlet
{
	public $title='Recent Comments';

	public function getRecentComments()
	{
		return Comment::model()->findRecentComments();
	}

	protected function renderContent()
	{
		$this->render('recentComments');
	}
}