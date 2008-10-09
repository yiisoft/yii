<?php

// This is the main Web application configuration. Any writable
// application properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Yii Framework: Phone Book Demo',
	'defaultController'=>'site',

	// autoloading model classes
	'import'=>array(
		'application.models.*',
	),

	// application components
	'components'=>array(
		'user'=>array(
			'class'=>'application.components.WebUser',
			'allowAutoLogin'=>true,
			'loginUrl'=>array('user/login'),
		),
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/phonebook.db',
		),
	),
);