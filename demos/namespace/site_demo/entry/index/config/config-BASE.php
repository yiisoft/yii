<?php

return array(
    'name' => 'Yii-Namespaced Site',

    'basePath' => __DIR_SITE_BASE__.'/entry/'.__ENTRY_NAME__,

    'controllerPath'      => __DIR_SITE_BASE__.'/entry/'.__ENTRY_NAME__.'/controllers/',
    'controllerNamespace' => 'site_demo\\entry\\'.__ENTRY_NAME__.'\\controllers',
    'viewPath'            => __DIR_SITE_BASE__.'/entry/'.__ENTRY_NAME__.'/views/',

    'defaultController' => 'Site/Index',

    'components' => array(
        'user' => array(
            'class'          => 'CWebUser', //'lib\site\BaseUser',
            // 'stateKeyPrefix' => '',
            'allowAutoLogin' => FALSE,
        ),
    ),
    'params' => array(
    ),
);
