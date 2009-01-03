<?php

class TagCloud extends Portlet
{
	public $title='Tags';

	public function getTagWeights()
	{
		return Tag::model()->findTagWeights();
	}

	protected function renderContent()
	{
		$this->render('tagCloud');
	}
}