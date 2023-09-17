<?php

namespace Spec\PHPSpec\Loader;

use \PHPSpec\Loader\DirectoryLoader,
    \PHPSpec\Util\SpecIterator;

class DescribeDirectoryLoader extends \PHPSpec\Context
{
    function itLoadsAllExampleGroupsUnderADirectory()
    {
        $loader = new DirectoryLoader;
        $examples = $loader->load(__DIR__ . '/_files/Bar');
        $this->spec(count($examples))->should->be(3);

        foreach ($examples as $example) {
            $class = get_class($example);
            if (!in_array($class, array('DescribeA', 'DescribeB', 'DescribeC'))) {
                $this->fail("$class is not meant to be loaded");
            }
        }
    }
}
