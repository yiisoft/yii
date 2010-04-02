<?php

class FormGenerator extends CCodeGenerator
{
	public $codeModel='gii.generators.form.FormCode';

	public function getSuccessMessage($model)
	{
		return "The form has been generated successfully. You may add this to your controller:
		<pre>". $model->getActionFunction() . "</pre>.";
	}
}