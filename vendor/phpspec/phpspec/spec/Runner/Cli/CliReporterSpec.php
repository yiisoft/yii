<?php

use PHPSpec\Runner\Cli\Reporter;
use PHPSpec\Runner\ReporterEvent;

class DescribeCliReporter extends \PHPSpec\Context {
	
	private $_reporter;
	private $_formatter;
	private $_example;
	private $_reporterEvent;
        private $_exception;

	public function before() {
		$this->_formatter = $this->mock('SplObserver');
		$this->_reporter = new Reporter();
		$this->_reporter->attach($this->_formatter);

		$this->_example = $this->mock('PHPSpec\Specification\Example');
		$this->_example->shouldReceive('getSpecificationText')
			->andReturn('example1');
		$this->_example->shouldReceive('getExecutionTime')
			->andReturn('0.01');
		$this->_example->shouldReceive('getNoOfAssertions')
			->andReturn(2);
		$this->_example->shouldReceive('getFile')
			->andReturn('DummySpec.php');
		$this->_example->shouldReceive('getLine')
			->andReturn(100);

		$this->_reporterEvent = new ReporterEvent(
			'status',
			'.',
			'example1',
                        '0.01',
                        null,
                        null,
                        2
		);
	}

	public function itNotifiesFormattersOfPassingTests() {
		$this->_reporterEvent->file = 'DummySpec.php';
        $this->_reporterEvent->line = 100;

		$reporterEvent = new Mockery\Matcher\MustBe($this->_reporterEvent);

		$this->_formatter->shouldReceive('update')
			->with($this->_reporter, $reporterEvent);

		$this->_reporter->addPass($this->_example);
	}

	public function itNotifiesFormattersOfFailingTests() {
        $e = new \PHPSpec\Specification\Result\Failure('Fake message');
    
        $this->_reporterEvent->status = 'F';
        $this->_reporterEvent->exception = $e;
        $this->_reporterEvent->message = $e->getMessage();
        $this->_reporterEvent->backtrace = PHPSpec\Util\Backtrace::pretty($e->getTrace());
        $this->_reporterEvent->file = 'DummySpec.php';
        $this->_reporterEvent->line = 100;
                
		$reporterEvent = new Mockery\Matcher\MustBe($this->_reporterEvent);

		$this->_formatter->shouldReceive('update')
			->with($this->_reporter, $reporterEvent);

		$this->_reporter->addFailure($this->_example, $e);
	}

	public function itNotifiesFormattersOfErrorsInTests() {
        $e = new \PHPSpec\Specification\Result\Error('Fake message');
    
        $this->_reporterEvent->status = 'E';
        $this->_reporterEvent->exception = $e;
        $this->_reporterEvent->message = $e->getMessage();
        $this->_reporterEvent->backtrace = PHPSpec\Util\Backtrace::pretty($e->getTrace());
        $this->_reporterEvent->file = 'DummySpec.php';
        $this->_reporterEvent->line = 100;
                
		$reporterEvent = new Mockery\Matcher\MustBe($this->_reporterEvent);

		$this->_formatter->shouldReceive('update')
			->with($this->_reporter, $reporterEvent);

		$this->_reporter->addError($this->_example, $e);
	}

	public function itNotifiesFormattersOfExceptionsInTests() {
        $e = new \Exception('Fake message');
    
        $this->_reporterEvent->status = 'E';
        $this->_reporterEvent->exception = $e;
        $this->_reporterEvent->message = $e->getMessage();
        $this->_reporterEvent->backtrace = PHPSpec\Util\Backtrace::pretty($e->getTrace());
        $this->_reporterEvent->file = 'DummySpec.php';
        $this->_reporterEvent->line = 100;
                
		$reporterEvent = new Mockery\Matcher\MustBe($this->_reporterEvent);

		$this->_formatter->shouldReceive('update')
			->with($this->_reporter, $reporterEvent);

		$this->_reporter->addException($this->_example, $e);
	}

	public function itNotifiesFormattersOfPendingTests() {
        $e = new \PHPSpec\Specification\Result\Pending('Fake message');
    
        $this->_reporterEvent->status = '*';
        $this->_reporterEvent->message = $e->getMessage();
        $this->_reporterEvent->file = 'DummySpec.php';
        $this->_reporterEvent->line = 100;
                
		$reporterEvent = new Mockery\Matcher\MustBe($this->_reporterEvent);

		$this->_formatter->shouldReceive('update')
			->with($this->_reporter, $reporterEvent);

		$this->_reporter->addPending($this->_example, $e);
	}

	public function itNotifiesFormattersToExitIfFailFastIsOnAndATestFails() {
        $e = new \Exception('Fake message');
    
        $this->_reporterEvent->status = 'E';
        $this->_reporterEvent->exception = $e;
        $this->_reporterEvent->message = $e->getMessage();
        $this->_reporterEvent->backtrace = PHPSpec\Util\Backtrace::pretty($e->getTrace());
        $this->_reporterEvent->file = 'DummySpec.php';
        $this->_reporterEvent->line = 100;
                
		$reporterEvent = new Mockery\Matcher\MustBe($this->_reporterEvent);
		$exitEvent = new Mockery\Matcher\MustBe( new ReporterEvent(
			'exit',
			'',
			''
		));

		$this->_reporter->setFailFast(true);

		$this->_formatter->shouldReceive('update')
			->with($this->_reporter, $reporterEvent)->once();
		$this->_formatter->shouldReceive('update')
			->with($this->_reporter, $exitEvent)->once();

		$this->_reporter->addException($this->_example, $e);
	}

}