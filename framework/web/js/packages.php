<?php

$packages=array(
	'jquery'=>array(
		YII_DEBUG ? 'jquery.js' : 'jquery.min.js',
	),
	'yii'=>array(
		'jquery.yii.js',
	),
	'bgiframe'=>array(
		'jquery.bgiframe.js',
	),
	'dimensions'=>array(
		'jquery.dimensions.js',
	),
	'ajaxqueue'=>array(
		'jquery.ajaxqueue.js',
	),
	'autocomplete'=>array(
		'jquery.autocomplete.js',
	),
	'maskedinput'=>array(
		'jquery.maskedinput.js',
	),
	'cookie'=>array(
		'jquery.cookie.js',
	),
	'treeview'=>array(
		'jquery.treeview.js',
		'jquery.treeview.async.js',
	),
	'multifile'=>array(
		'jquery.multifile.js',
	),
);

$dependencies=array(
	'yii'=>array(
		'jquery',
	),
	'bgiframe'=>array(
		'jquery',
	),
	'dimensions'=>array(
		'jquery',
	),
	'ajaxqueue'=>array(
		'jquery',
	),
	'autocomplete'=>array(
		'jquery',
		'bgiframe',
		'dimensions',
		'ajaxqueue',
	),
	'maskedinput'=>array(
		'jquery',
	),
	'cookie'=>array(
		'jquery',
	),
	'treeview'=>array(
		'jquery',
		'cookie',
	),
	'multifile'=>array(
		'jquery',
	),
);

return array($packages,$dependencies);