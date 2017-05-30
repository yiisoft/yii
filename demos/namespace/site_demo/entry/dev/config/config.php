<?php

\Yii::setPathOfAlias('lib', __DIR_LIB_BASE__);
\Yii::setPathOfAlias('site', __DIR_SITE_BASE__);

define('__ENV__', \lib\constant\IEnv::DEV);

return \lib\site\Config::load(array(
    __DIR__.'/config-BASE.php'
    ,__DIR__.'/config-'.__ENV__.'.php'
));
