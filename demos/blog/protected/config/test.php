<?php
/**
 * This is the configuration used by unit and functional tests.
 */
return CMap::mergeArray(
	require(dirname(__FILE__).'/main.php'),
	array(
		'components'=>array(
			'db'=>array(
				'connectionString'=>'sqlite:'.dirname(__FILE__).'/../data/blog-test.db',
			),
			'fixture'=>array(
				'class'=>'system.test.CDbFixtureManager',
			),
		),
	)
);
