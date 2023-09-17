<?php
namespace CustomMatchers;

use \PHPSpec\Matcher;

/**
 * Dummy matcher for testing purposes
 *
 * @package default
 */
class DummyMatcher implements Matcher {
    /**
     * Matcher is constructed with the value you are comparing with
     *
     * @param string $expected
     */
    public function __construct($expected)
    {
        $this->_expected = $expected;
    }
    /**
     * Returns failure message in case we are using should
     * 
     * @return string
     */
    public function getFailureMessage()
    {
        return 'expected dummy, got dummer.';
    }

    /**
     * Returns failure message in case we are using should not
     * 
     * @return string
     */
    public function getNegativeFailureMessage()
    {
        return 'expected not to be dummer, but it is not dummy';
    }

    /**
     * Returns the matcher description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'dummy';
    }

    /**
     * Checks whether actual value is equal to the expected
     *
     * @param mixed $actual
     * @return boolean
     */
    public function matches($actual)
    {
        return true;
    }
}