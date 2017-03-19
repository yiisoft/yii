<?php

return array(
    'name' => 'Yii-Namespaced Dev Site [DEV]',
    'modules' => array(
        'gii'=>array(
            'class'=>'system.gii.GiiModule',
            'password'=>'root',
            'ipFilters'=>array('127.0.0.1','::1'),
            'generatorPaths' => array('lib.yii.gii'),
        ),
    ),
    'components' => array(
        'db' => array(
            'connectionString' => 'mysql:host=127.0.0.1;dbname=demo',
            'emulatePrepare' => FALSE,
            'username' => 'root',
            'password' => 'root',
            'charset'  => 'utf8',
        ),
    ),
);
