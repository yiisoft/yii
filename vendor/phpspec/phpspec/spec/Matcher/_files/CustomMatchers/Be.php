<?php
namespace CustomMatchers;

use \PHPSpec\Matcher;

/**
 * Fake be matcher for testing purposes
 *
 * @package default
 */
class Be implements Matcher {
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
        return 'This is fake be.';
    }

    /**
     * Returns failure message in case we are using should not
     * 
     * @return string
     */
    public function getNegativeFailureMessage()
    {
        return "This ain't the no fake be.";
    }

    /**
     * Returns the matcher description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'Fake be.';
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