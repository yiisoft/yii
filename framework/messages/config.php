<?php
/**
 * This is the configuration for generating message translations
 * for the Yii framework. It is used by the 'yiic message' command.
 */
return array(
	'sourcePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'messagePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'messages',
	'languages'=>array('ar','bg','bs','ca','cs','da','de','el','es','fa_ir','fi','fr','he','hu','id','it','ja','kk','ko_kr','lt','lv','nl','no','pl','pt','pt_br','ro','ru','sk','sr_sr','sr_yu','sv','ta_in','th','tr','uk','vi','zh_cn','zh_tw'),
	'fileTypes'=>array('php'),
	'overwrite'=>true,
	'exclude'=>array(
		'.svn',
		'.gitignore',
		'yiilite.php',
		'yiit.php',
		'/i18n/data',
		'/messages',
		'/vendors',
		'/web/js',
	),
);
