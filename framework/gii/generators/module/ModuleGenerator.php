<?php

class ModuleGenerator extends CCodeGenerator
{
	public $codeModel='gii.generators.module.ModuleCode';

	public function getSuccessMessage($model)
	{
		if(Yii::app()->hasModule($model->moduleID))
			return 'The module has been generated successfully. You may '.CHtml::link('try it now', Yii::app()->createUrl($model->moduleID), array('target'=>'_blank')).'.';

		$output=<<<EOD
<p>The module has been generated successfully.</p>
<p>To access the module, you need to modify the application configuration as follows:</p>
EOD;
		$code=<<<EOD
<?php
return array(
    'modules'=>array(
        '{$model->moduleID}',
    ),
    ......
);
EOD;

		return $output.highlight_string($code,true);
	}
}