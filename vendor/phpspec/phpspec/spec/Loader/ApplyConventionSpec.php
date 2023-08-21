<?php

namespace Spec\PHPSpec\Loader;

use \PHPSpec\Loader\ApplyConvention;

class DescribeApplyConvention extends \PHPSpec\Context
{
    function itAcceptsFileAndClassToStartBothWithDescribe()
    {
        $convention = $this->spec(new ApplyConvention('DescribeFoo.php'));
        $convention->apply();
        $convention->getClass()->should->be('DescribeFoo');
    }
    
    function itAcceptsFileToEndWithSpecAndClassToStartWithDescribe()
    {
        $convention = $this->spec(new ApplyConvention('FooSpec.php'));
        $convention->apply();
        $convention->getClass()->should->be('DescribeFoo');
    }
    
    function itAcceptsFileAndClassToStartBothWithDescribeSmallCaps()
    {
        $convention = $this->spec(new ApplyConvention('describe_foo.php'));
        $convention->apply();
        $convention->getClass()->should->be('describe_foo');
    }

    function itIgnoresSpecFilesThatDoNotStartWithDescribeOrEndWithSpec()
    {
        $convention = $this->spec(new ApplyConvention('Foo.php'));
        $convention->apply()->should->beFalse();
    }
}