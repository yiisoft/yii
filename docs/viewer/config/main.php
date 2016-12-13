<?php
Yee::setPathOfAlias('docs',dirname(dirname(dirname(__FILE__))));

return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'The Yee Documentation Viewer',
	'defaultController'=>'guide',
	'import'=>array(
		'application.components.*',
	),
);