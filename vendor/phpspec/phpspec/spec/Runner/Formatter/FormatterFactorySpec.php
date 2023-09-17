<?php

namespace Spec\PHPSpec\Runner\Formatter;

use PHPSpec\Runner\Formatter\Factory as FormatterFactory;

class DescribeFormatterFactory extends \PHPSpec\Context
{
    protected $_builtInFormatters = array(
        'p' => 'Progress',
        'd' => 'Documentation',
        'h' => 'Html',
        'j' => 'Junit',
        't' => 'Textmate'
    );
    
    public function itRecogniseBuiltInFormatters()
    {
        $factory = $this->spec(new FormatterFactory);
        $reporter = $this->mock('PHPSpec\Runner\Reporter');
        foreach ($this->_builtInFormatters as $formatter) {
            $factory->create($formatter, $reporter);
        }
    }
    
}