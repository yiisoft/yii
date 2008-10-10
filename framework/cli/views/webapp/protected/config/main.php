<?php

// This is the main Web application configuration. Any writable
// application properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'My Web Application',
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
			'loginUrl'=>array('site/login'),
		),
		// uncomment the following to set up database
		/*
		'db'=>array(
			'connectionString'=>'Your DSN',
		),
		*/
	),
);