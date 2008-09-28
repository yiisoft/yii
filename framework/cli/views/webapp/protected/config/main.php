<?php

// This is the main application configuration. Any writable
// application properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Web Application',
	'import'=>array(
		'application.models.*',
	),
	'defaultController'=>'home',
	'components'=>array(
		'user'=>array(
			'class'=>'application.components.WebUser',
			'allowAutoLogin'=>true,
			'loginUrl'=>array('user/login'),
		),
	),
);