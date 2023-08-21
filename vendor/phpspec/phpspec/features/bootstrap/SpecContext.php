<?php

if (!defined('PHPSPEC_AUTOLOAD')) {
    define('PHPSPEC_AUTOLOAD', 'PHPSPEC_AUTOLOAD');
    require_once 'PHPSpec/Loader/UniversalClassLoader.php';
    $loader = new \PHPSpec\Loader\UniversalClassLoader();
    $loader->registerNamespace('PHPSpec', '/usr/share/pear');
    $loader->register();
}

use Behat\Behat\Context\BehatContext,
    \PHPSpec\Specification\Interceptor\InterceptorFactory;

class SpecContext extends BehatContext
{
    public function spec($actualValue)
    {
        return InterceptorFactory::create($actualValue);
    }
}