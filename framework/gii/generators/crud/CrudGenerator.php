<?php

class CrudGenerator extends CCodeGenerator
{
	public $codeModel='gii.generators.crud.CrudCode';

	public function getSuccessMessage($model)
	{
		$link=CHtml::link('try it now', Yii::app()->createUrl($model->controller), array('target'=>'_blank'));
		return "The controller has been generated successfully. You may $link.";
	}
}