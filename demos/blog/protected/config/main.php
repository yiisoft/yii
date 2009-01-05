<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'blog',
	'defaultController'=>'post',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	// using 'classic' theme
	'theme'=>'classic',

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>require(dirname(__FILE__).'/params.php'),

	// application components
	'components'=>array(
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
			),
		),
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
			// force 401 HTTP error if authentication needed
			'loginUrl'=>null,
		),
		'db'=>array(
			'connectionString'=>'sqlite:'.dirname(__FILE__).'/../data/blog.db',
			/* uncomment the following to use MySQL as database
			'connectionString'=>'mysql:host=localhost;dbname=blog',
			'username'=>'xyz',
			'password'=>'xxx',
			*/
		),
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'tag/<tag>'=>'post/list',
				'posts'=>'post/list',
				'post/<id:\d+>'=>'post/show',
				'post/update/<id:\d+>'=>'post/update',
			),
		),
	),
);