<?php

namespace Spec\PHPSpec\Loader;

class DescribeClassLoader extends \PHPSpec\Context
{
    protected $loader;
    
    function before()
    {
        $this->loader = $this->spec(new \PHPSpec\Loader\ClassLoader);
    }
    
    function itReturnsAnEmptyArrayIfTheConventionDoesNotApply()
    {
        $convention = $this->mock('\PHPSpec\Loader\ApplyConvention', array(), array('spec'));
        $convention->shouldReceive('apply')->andReturn(false);
        
        $factory = $this->mock('\PHPSpec\Loader\ConventionFactory');
        $factory->shouldReceive('create')->andReturn($convention);
        
        $this->loader->setConventionFactory($factory);
        $loaded = $this->loader->load(__DIR__ . '/_files/NoConvention.php');
        
        $loaded->should->equal(array());
    }
    
    function itReturnsTheLoadedExampleGroupIfConventionIsFollowed()
    {
        $loader = new \PHPSpec\Loader\ClassLoader;
        $loaded = $loader->load(__DIR__ . '/_files/FooSpec.php');
        $this->spec($loaded[0])->should->beAnInstanceOf('DescribeFoo');
    }
    
    function itThrowsAnErrorIfFileDoesNotExist()
    {
        $loader = $this->loader;
        $file = realpath (__DIR__ . '/_files') . '/NoFooSpec.php';
        $this->spec(function() use ($loader, $file) {
            $loader->load($file);    
        })->should->throwException('\PHPSpec\Runner\Error', "Could not include file \"$file\"");
    }
    
    function itThrowsAnErrorIfFileIsNotReadable()
    {
        $loader = $this->loader;
        $file = realpath (__DIR__ . '/_files') . '/NoFooSpec.php';
        touch($file);
        chmod($file, 0000);
        $this->spec(function() use ($loader, $file) {
            $loader->load($file);
        })->should->throwException('\PHPSpec\Runner\Error', "Could not include file \"$file\"");
        unlink($file);
    }

    function itThrowsAnErrorIfItCannotFindTheConventionClassInIt()
    {
        $loader = $this->loader;
        $file = realpath (__DIR__ . '/_files/') . '/NoFooSpec.php';
        touch($file);
        $class = 'DescribeNoFoo';
        $this->spec(function() use ($loader, $class, $file) {
            $loader->load($file);
        })->should->throwException('\PHPSpec\Runner\Error', "Could not find class \"$class\" in file \"$file\"");
        unlink($file);
    }
    
    function after()
    {
        if (file_exists(realpath(__DIR__ . '/_files') . '/NoFooSpec.php')) {
            unlink(realpath(__DIR__ . '/_files') . '/NoFooSpec.php');
        }
    }
}