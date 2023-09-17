<?php

namespace Spec\PHPSpec\Runner\Cli;

require_once __DIR__ . '/../../WorldBuilder.php';

use \PHPSpec\Runner\Cli\Runner as CliRunner,
    \Spec\PHPSpec\WorldBuilder;

class DescribeRunner extends \PHPSpec\Context
{
    function before()
    {
        $this->runner = $this->spec(new CliRunner);
    }
    
    function itHaltsTheRunAndSetsVersionMessageIfVersionOptionIsSet()
    {
        $worldBuilder = new WorldBuilder;
        
        $world = $worldBuilder->withVersion()
                              ->build();
                              
        $worldBuilder->getReporter()->shouldReceive('setMessage')
                                    ->with(CliRunner::VERSION);

        $this->runner->run($world);
    }
    
    function itHaltsTheRunAndSetsHelpMessageIfHelpOptionIsSet()
    {
        $worldBuilder = new WorldBuilder;
        
        $world = $worldBuilder->withHelp()
                              ->build();

        $worldBuilder->getReporter()->shouldReceive('setMessage')
                                    ->with(CliRunner::USAGE);

        $this->runner->run($world);
    }
    
    function itSetsTheFormatterToDisplayColours()
    {
        $worldBuilder = new WorldBuilder;
        $world = $worldBuilder->withColours()
                              ->withSpecFile('FooSpec.php')
                              ->build();
        
        $worldBuilder->getFormatter()->shouldReceive('setShowColors')
                                     ->with(true)->once();
        
        
        $this->runner->run($world);
    }
    
    function itSetsTheFormatterToDisplayBacktrace()
    {
        $worldBuilder = new WorldBuilder;
        $world = $worldBuilder->withBacktrace()
                              ->withSpecFile('FooSpec.php')
                              ->build();
                              
        $worldBuilder->getFormatter()->shouldReceive('setEnableBacktrace')
                                     ->with(true)->once();
        
        $this->runner->run($world);
    }
    
    function itTellsTheReporterToFailFast()
    {
        $worldBuilder = new WorldBuilder;
        
        $world = $worldBuilder->withSpecFile('FooSpec.php')
                              ->withFailFast()
                              ->build();
                              
        $worldBuilder->getReporter()->shouldReceive('setFailFast')
                                    ->with(true)->once();
        
        $this->runner->run($world);
    }
    
    function itSetsTheExampleToBeRunIntoTheRunner()
    {
        $worldBuilder = new WorldBuilder;
        $world = $worldBuilder->withExample('itDoesSomething')
                              ->withSpecFile('FooSpec.php')
                              ->build();
        $this->runner->setExampleRunner($worldBuilder->exampleRunner);
        
        $worldBuilder->exampleRunner->shouldReceive('runOnly')
                                    ->with('itDoesSomething')->once();
        
        $this->runner->run($world);
    }
    
    function itSetsTheErrorHandler()
    {
        $worldBuilder = new WorldBuilder;
        $world = $worldBuilder->withSpecFile('SomethingSpec.php')
                              ->withErrorHandler()
                              ->build();
                              
        $error = $this->mock('\SomeErrorHandler');
        $error->shouldReceive('someMethod')->times(1);
        $this->runner->setErrorHandler(array($error, 'someMethod'));
        
        $this->runner->run($world);
    }
    
    function itRunsAllSpecsReturnedByTheLoader()
    {
        $worldBuilder = new WorldBuilder;
        $files = __DIR__ . '/_files';
        $world = $worldBuilder->withNoOptionsAndSpecFile($files)
                              ->build();
        
        include_once $files . '/FooSpec.php';
        include_once $files . '/SomethingSpec.php';
        
        $loader = $this->mock();
        $loader->shouldReceive('load')
               ->andReturn(array(new \Spec\Runner\Cli\Files\DescribeFoo,
                                 new \Spec\Runner\Cli\Files\DescribeSomething))
               ->once();
        
        $loaderFactory = $this->mock('\PHPSpec\Loader\Loader');
        $loaderFactory->shouldReceive('factory')
                      ->with($files)
                      ->andReturn($loader);
        
        $this->runner->setLoader($loaderFactory);
        
        $this->runner->run($world);
    }
    
    function itLoadsBootstrapFileIfSpecified() {
        
        $tmp_dir = sys_get_temp_dir();
        $tmpfname = tempnam("/tmp", "phpspec_bootstrap.php");
        $str_bootstrap = '<?php class BootstrapTester {}';
        file_put_contents($tmpfname, $str_bootstrap);
        
        $spec_file = tempnam("/tmp", "SpecFake.php");
        $str_spec = '<?php class DescriveFake extends \PHPSpec\Context {}';
        file_put_contents($spec_file, $str_spec);
        
        $reporter = $this->mock('\PHPSpec\Runner\Cli\Reporter');
        $reporter->shouldReceive('setMessage')->andReturn(CliRunner::VERSION);
        
        $formatter = $this->mock('\SplObserver');
        $reporter->shouldReceive('attach')->with($formatter);
        $reporter->shouldReceive('getFormatters')->andReturn(array($formatter));
        $reporter->shouldReceive('setRuntimeStart');
        $reporter->shouldReceive('setRuntimeEnd');
        
        $world = $this->mock('\PHPSpec\World');
        $world->shouldReceive('getOption')->with('bootstrap')->andReturn($tmpfname);
        $world->shouldReceive('getOption')->with('specFile')->andReturn($spec_file);
        $world->shouldReceive('getOption')->andReturn();
        $world->shouldReceive('getReporter')->andReturn($reporter);
        
        $this->runner->run($world);
        
        $this->spec(class_exists('BootstrapTester'))->should->beTrue();
        
        unlink($tmpfname);
        unlink($spec_file);
    }
}
