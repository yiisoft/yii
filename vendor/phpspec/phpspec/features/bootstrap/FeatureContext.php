<?php

use Behat\Behat\Exception\PendingException,
    Behat\Gherkin\Node\PyStringNode;

require_once 'SpecContext.php';
require_once 'CommandLineContext.php';
require_once 'ExampleGroupsContext.php';

class FeatureContext extends SpecContext
{
    public function __construct(array $params)
    {
        $this->useContext('command_line', new CommandContext($params));
        $this->useContext('example_groups', new ExampleGroupsContext($params));
    }
}