<?php

namespace Spec\PHPSpec\Runner\Cli;

use \PHPSpec\Runner\Cli\Parser as CliParser;

class DescribeParser extends \PHPSpec\Context
{
    function before()
    {
        global $argv;
        $this->parser = $this->spec(new CliParser);
        $this->executable = $argv[0];
    }
    
    function itRemovesTheProgramNameFromArguments()
    {
        $args = array($this->executable, '-h');
        $this->parser->parse($args);
        $this->parser->getArguments()->should->be(array('-h'));
    }
    
    function itExtractsTheSpecFileOutOfTheFirstArgument()
    {
        $args = array($this->executable, 'MySpec.php', '-f', 'd', '-c');
        $this->parser->parse($args);
        $this->parser->getOption('specFile')->should->be('MySpec.php');
    }
    
    function itComplainsWhenCalledWithNoArgumentsApartFromProgramName()
    {
        $parser = $this->parser;
        $executable = $this->executable;
        $this->spec( function() use ($parser, $executable) {
            $parser->parse(array($executable));
        })->should->throwException(
            '\PHPSpec\Runner\Cli\Error',
            'phpspec: Invalid number of arguments. Type -h for help'
        );
    }
    
    function itConvertsOneLetterValidArgumentsIntoOptions()
    {
        $args = array($this->executable, 'MySpec.php', '-c', '-h');
        $this->parser->parse($args);
        $this->parser->getOption('c')->should->beTrue();
        $this->parser->getOption('h')->should->beTrue();
    }
    
    function itSavesOneLetterValidArgumentsIntoOptionLongNameVersion()
    {
        $args = array($this->executable, 'MySpec.php', '-c', '-h');
        $this->parser->parse($args);
        $this->parser->getOption('color')->should->beTrue();
        $this->parser->getOption('colour')->should->beTrue();
        $this->parser->getOption('help')->should->beTrue();
    }
    
    function itSavesLongVersionValidArgumentsIntoOptionOneLetterVersion()
    {
        $args = array(
            $this->executable,
            'MySpec.php',
            '--color',
            '--help'
        );
        $this->parser->parse($args);
        $this->parser->getOption('c')->should->beTrue();
        $this->parser->getOption('h')->should->beTrue();
    }
    
    function itConvertsUnseparatedOneLetterValidArgumentsIntoOptions()
    {
        $args = array($this->executable, 'MySpec.php', '-ch');
        $this->parser->parse($args);
        $this->parser->getOption('c')->should->beTrue();
        $this->parser->getOption('h')->should->beTrue();
    }
    
    function itConvertsSpaceSeparateFormatterOptionValueAppropriately()
    {
        $args = array($this->executable, 'MySpec.php', '-f', 'd');
        $this->parser->parse($args);
        $this->parser->getOption('f')->should->be('d');
        $this->parser->getOption('formatter')->should->be('d');
    }
    
    function itConvertsEqualSignSeparateLongFormatterOptionValueAppropriately()
    {
        $args = array($this->executable, 'MySpec.php', '--formatter', 'd');
        $this->parser->parse($args);
        $this->parser->getOption('f')->should->be('d');
        $this->parser->getOption('formatter')->should->be('d');
    }
    
    function itComplainsWhenFormatterIsNotGivenTheArgument()
    {
        $args = array($this->executable, 'MySpec.php', '-f');
        $parser = $this->parser;
        $this->spec(
            function() use ($parser, $args) {
                $parser->parse($args);
            }
        )->should->throwException(
            '\PHPSpec\Runner\Cli\Error', 'phpspec: Invalid argument for formatter'
        );
    }
    
    function itAcceptsFormatterToBePassedWithNoSpace()
    {
        $args = array($this->executable, 'MySpec.php', '-fd');
        $this->parser->parse($args);
        $this->parser->getOption('f')->should->be('d');
        $this->parser->getOption('formatter')->should->be('d');
    }
    
    function itRejectsInvalidFormatter()
    {
        $args = array($this->executable, 'MySpec.php', '-fx');
        $parser = $this->parser;
        $this->spec(
            function() use ($parser, $args) {
                $parser->parse($args);
            }
        )->should->throwException(
            '\PHPSpec\Runner\Cli\Error', 'phpspec: Invalid argument for formatter'
        );
    }
    
    function itRejectsInvalidFormatterFromLongOption()
    {
        $args = array($this->executable, 'MySpec.php', '--formatter', 'x');
        $parser = $this->parser;
        $this->spec(function() use ($parser, $args) {
            $parser->parse($args);
        })->should->throwException('\PHPSpec\Runner\Cli\Error',
            'phpspec: Invalid argument for formatter');
    }
    
    function itCanSpecifyShortOptionForExample()
    {
        $args = array($this->executable, 'MySpec.php', '-e', 'itShouldDoSomething');
        $this->parser->parse($args);
        $this->parser->getOption('e')->should->be('itShouldDoSomething');
    }

    function itCanSpecifyLongOptionForExample()
    {
        $args = array($this->executable, 'MySpec.php', '--example', 'itShouldDoSomething');
        $this->parser->parse($args);
        $this->parser->getOption('e')->should->be('itShouldDoSomething');
    }
    
    function itAcceptsBootstrapFile() {
        $args = array($this->executable, 'MySpec.php', '--bootstrap', 'bootstrap.php');
        $this->parser->parse($args);
        $this->parser->getOption('bootstrap')->should->be('bootstrap.php');
    }
    
    function itShouldComplainWhenBootstrapOptionIsSpecifiedWithoutFilename() {
        $args = array($this->executable, 'MySpec.php', '--bootstrap');
        //$this->parser->parse($args);
        $parser = $this->parser;
        //$this->parser->getOption('bootstrap')->should->be('bootstrap.php');
        $this->spec(
            function() use ($parser, $args) {
                $parser->parse($args);
            }
        )->should->throwException(
            'PHPSpec\Runner\Cli\Error', 'phpspec: Bootstrap file should be given for bootstrap option'
        );
    }
    
    function itShouldAcceptTextmateAsAValidFormatter()
    {
        $args = array($this->executable, 'MySpec.php', '--formatter', 'textmate');
        $parser = $this->parser;
        
        $this->spec(
            function() use ($parser, $args) {
                $parser->parse($args);
            }
        )->shouldNot->throwException('PHPSpec\Runner\Cli\Error', 'phpspec: Invalid argument for formatter');
    }
}