<?php
Yii::setPathOfAlias('docs',dirname(dirname(__DIR__)));

return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'The Yii Documentation Viewer',
	'defaultController'=>'guide',
	'import'=>array(
		'application.components.*',
	),
);
