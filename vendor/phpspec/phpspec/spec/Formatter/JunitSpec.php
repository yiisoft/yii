<?php

namespace Spec\PHPSpec\Runner\Formatter;

use PHPSpec\Runner\ReporterEvent;

use PHPSpec\Runner\Cli\Reporter;

use PHPSpec\Runner\Formatter\Junit;

class DescribeJunit extends \PHPSpec\Context {
    
    private $_reporter;
    private $_formatter;
    private $_doc;
    private $_msg = 'The message. Doesn\'t matter what it is as long as it is
                    shown in the failure';
    private $_filename = 'DummySpec.php';
    
    public function before() {
        $this->_reporter = $this->mock('PHPSpec\Runner\Cli\Reporter');
        
        $formatter = new Junit($this->_reporter);
        $formatter->update($this->_reporter, new ReporterEvent(
            'start',
            '',
            'Dummy',
            0,
            $this->_filename
        ));
        $this->_formatter = $formatter;

        $this->_doc = new \SimpleXMLElement('<testsuites></testsuites>');
    }
    
    public function itFormatsPassesInJunitFormat()
    {
        $this->_updateFormatterWithException(
            '.',
            'example1',
            null,
            '0.01',
            '2',
            130,
            $this->_filename
        );
        $this->_finishSuite();
        
        $suite = $this->_createSuite('Dummy', 1, 0, 0, '0.01', 2,
                                     $this->_filename);
        $case = $this->_createCase($suite, 'Dummy', 'example1', '0.01', 2, 130,
                                   $this->_filename);

        $this->_compare();
    }
    
    public function itFormatsPendingInJunitFormat()
    {
        $failure_e = $this->_getFailureException();

        $this->_updateFormatterWithException(
            '*',
            'example1',
            $failure_e,
            '0.01',
            '2',
            50,
            $this->_filename
        );
        $this->_finishSuite();
        
        $suite = $this->_createSuite('Dummy', 1, 1, 0, '0.01', 2,
                                     $this->_filename);
        $case = $this->_createCase($suite, 'Dummy', 'example1', '0.01', 2, 50,
                                   $this->_filename);
        // @todo try to change the type to Failure or something else
        $fail = $this->_createFailure($case, 'example1', $failure_e, '*');

        $this->_compare();
    }
    
    public function itFormatsFailuresInJunitFormat()
    {
        $failure_e = $this->_getFailureException();

        $this->_updateFormatterWithException(
            'F',
            'example1',
            $failure_e,
            '0.01',
            '2',
            250,
            $this->_filename
        );
        $this->_finishSuite();
        
        $suite = $this->_createSuite('Dummy', 1, 1, 0, '0.01', 2,
                                     $this->_filename);
        $case = $this->_createCase($suite, 'Dummy', 'example1', '0.01', 2, 250,
                                   $this->_filename);
        // @todo try to change the type to Failure or something else
        $fail = $this->_createFailure($case, 'example1', $failure_e, 'F');

        $this->_compare();
    }
    
    public function itFormatsErrorsInJunitFormat()
    {
        $failure_e = $this->_getFailureException();
        $this->_updateFormatterWithException(
            'E',
            'example1',
            $failure_e,
            '0.01',
            '2',
            180,
            $this->_filename
        );
        $this->_finishSuite();
        
        $suite = $this->_createSuite('Dummy', 1, 0, 1, '0.01', 2,
                                     $this->_filename);
        $case = $this->_createCase($suite, 'Dummy', 'example1', '0.01', 2, 180,
                                   $this->_filename);
        $fail = $this->_createFailure($case, 'example1', $failure_e, 'E');

        $this->_compare();
    }

    public function itAddsUpTestsAssertionsAndTimeForSuiteFromExamples()
    {
        $this->_updateFormatterWithException(
            '.',
            'example1',
            null,
            '0.01',
            '2',
            130,
            $this->_filename
        );
        $this->_updateFormatterWithException(
            '.',
            'example2',
            null,
            '0.01653',
            '5',
            10,
            $this->_filename
        );
        $this->_finishSuite();
        
        $suite = $this->_createSuite('Dummy', 2, 0, 0, '0.02653', 7,
                                     $this->_filename);
        $case = $this->_createCase($suite, 'Dummy', 'example1', '0.01', 2, 130,
                                   $this->_filename);
        $case = $this->_createCase($suite, 'Dummy', 'example2', '0.01653', 5,
                                   10, $this->_filename);

        $this->_compare();
    }
    
    private function _createFailure($case, $example, \Exception $e, $type) {
        switch ($type) {
        case 'E':
            $error_type = 'ERROR';
            $tag = 'error';
            break;
        case 'F':
            $error_type = 'FAILED';
            $tag = 'failure';
            break;
        case '*':
            $error_type = 'PENDING';
            $tag = 'failure';
            break;
        default:
            throw new \Exception("Invalid type $type");
        }
        $failure_msg = $this->_generateFailureMessage('example1', $this->_msg, $e, $error_type);

        $fail = $case->addChild($tag, $failure_msg);
        $fail->addAttribute('type', 'Exception');
    }

    private function _getFailureException() {
        $failure_e = new \Exception($this->_msg);

        return $failure_e;
    }

    private function _generateFailureMessage($name, $msg, \Exception $exception, $type) {
        $failure_msg = PHP_EOL . "$name ($type)" . PHP_EOL;
        $failure_msg .= $msg . PHP_EOL;

        if ($type != 'PENDING') {
            $failure_msg .= $exception->getTraceAsString() . PHP_EOL;
        }

        return $failure_msg;
    }

    private function _updateFormatterWithException($status, $example,
        $exception=null, $time, $assertions, $line, $file) {
        if (is_null($exception)) {
            $this->_formatter->update($this->_reporter, new ReporterEvent(
                'status',
                $status,
                $example,
                $time,
                $file,
                $line,
                $assertions,
                '',
                '',
                null
            ));
        } else {
            $this->_formatter->update($this->_reporter, new ReporterEvent(
                'status',
                $status,
                $example,
                $time,
                $file,
                $line,
                $assertions,
                $exception->getMessage(),
                $exception->getTraceAsString(),
                $exception
            ));
        }
    }

    private function _updateFormatter($status, $example, $message,
        $traceString, $exception, $time, $assertions, $line, $file) {
        $this->_formatter->update($this->_reporter, new ReporterEvent(
            'status',
            $status,
            $example,
            $time,
            $file,
            $line,
            $assertions,
            $message,
            $traceString,
            $exception
        ));
    }

    private function _compare() {
        ob_start();
        $this->_formatter->output();
        $output = ob_get_clean();

        $dom = new \DOMDocument('1.0');
        $dom->preserveWhitespace = false;
        $dom->formatOutput = true;
        $dom->loadXml($this->_doc->asXml());

        $this->spec($output)
            ->should->be($dom->saveXml());
    }

    private function _finishSuite() {
        $this->_formatter->update($this->_reporter, new ReporterEvent('finish', '', 'Dummy'));
    }
    
    private function _createSuite($name, $tests, $failures, $errors,
                                  $time, $assertions, $file)
    {
        $suite = $this->_doc->addChild('testsuite');
        $suite->addAttribute('name', $name);
        $suite->addAttribute('file', $file);
        
        $suite->addAttribute('tests', $tests);
        $suite->addAttribute('assertions', $assertions);
        $suite->addAttribute('failures', $failures);
        $suite->addAttribute('errors', $errors);
        $suite->addAttribute('time', $time);
        
        return $suite;
    }
    
    public function _createCase($suite, $class, $example, $time, $assertions,
                                $line, $file)
    {
        $case = $suite->addChild('testcase');
        $case->addAttribute('name', $example);
        $case->addAttribute('class', $class);
        $case->addAttribute('file', $file);
        $case->addAttribute('line', $line);
        $case->addAttribute('assertions', $assertions);
        $case->addAttribute('time', $time);
        
        return $case;
    }
    
    private function _buildExpectation($expected) {
        $output = $this->_formatStart;
        $output .= $expected;
        $output .= $this->_formatEnd;
        
        return $output;
    }
    
}