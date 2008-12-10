<?php
/**
 * This is the configuration for generating message translations
 * for the Yii framework. It is used by the 'yiic message' command.
 */
return array(
	'sourcePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'messagePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'messages',
	'languages'=>array('zh','zh_cn','de','es'),
	'fileTypes'=>array('php'),
	'exclude'=>array(
		'.svn',
		'yiilite.php',
		'/i18n/data',
		'/messages',
		'/vendors',
		'/web/js',
	),
);