<?php

class FormGenerator extends CCodeGenerator
{
	public $codeModel='gii.generators.form.FormCode';

	public function getSuccessMessage($model)
	{
		$output=<<<EOD
<p>The form has been generated successfully.</p>
<p>You may add the following code in an appropriate controller class to invoke the view:</p>
EOD;
		$code="<?php\n".$model->render($model->templatePath.'/action.php');
		return $output.highlight_string($code,true);
	}
}