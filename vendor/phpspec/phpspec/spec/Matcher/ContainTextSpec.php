<?php

namespace Spec\PHPSpec\Matcher;

use \PHPSpec\Matcher\ContainText;

class DescribeContainText extends \PHPSpec\Context
{
    private $matcher;

    function before()
    {
        $this->matcher = $this->spec(new ContainText('foo'));
        $this->matcher->matches('some text');
    }

    function itShouldReturnADescriptionWithExpectedValue()
    {
        $this->matcher->getDescription()->should->be('contain text \'some text\'');
    }

    function itShouldReturnAMeaningfulFailureMessageIfRequested()
    {
        $this->matcher->matches('bar');
        $this->matcher->getFailureMessage()->should->be(
            "expected to contain:" . PHP_EOL .
            "'foo', got:" . PHP_EOL . "'bar' (using containText())"
        );
    }

    function itShouldReturnAMeaningulNegativeFailureMessageIfRequired()
    {
        $this->matcher->matches('some foo text');
        $this->matcher->getNegativeFailureMessage()->should->be(
            "expected text:" . PHP_EOL .
            "'foo' to not be contained in:" . PHP_EOL .
            "'some foo text' (using containText())"
        );
    }
    
    function itReturnsTrueIfTextExists()
    {
        $this->matcher->matches('foo')->should->beTrue();
    }
    
    function itReturnsFalseIfTextDoesNotExist()
    {
        $this->matcher->matches('zoo')->should->beFalse();
    }
}
