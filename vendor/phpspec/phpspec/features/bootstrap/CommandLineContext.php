<?php

use Behat\Behat\Exception\PendingException,
    Behat\Gherkin\Node\PyStringNode;

require_once 'SpecContext.php';

class CommandContext extends SpecContext
{
    /**
     * @When /^I run "([^"]*)"$/
     */
    public function iRun($command)
    {
        $dir = getcwd();
        if (is_dir('./tmp')) {
            chdir('./tmp');
        }
        $this->output = $this->spec(shell_exec($command));
        chdir($dir);
    }

   /**
     * @Then /^the output should contain:$/
     */
    public function theOutputShouldContain(PyStringNode $output)
    {
        $this->output->should->containText($output->__toString());
    }
}