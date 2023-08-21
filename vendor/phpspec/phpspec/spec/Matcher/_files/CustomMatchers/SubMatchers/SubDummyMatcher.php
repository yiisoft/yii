<?php
namespace CustomMatchers\SubMatchers;

use \PHPSpec\Matcher;


class SubDummyMatcher implements Matcher {
    /**
     * Returns failure message in case we are using should
     * 
     * @return string
     */
    public function getFailureMessage()
    {
        return 'expected subdummy, got subdummer.';
    }

    /**
     * Returns failure message in case we are using should not
     * 
     * @return string
     */
    public function getNegativeFailureMessage()
    {
        return 'expected not to be subdummer, but it is not subdummy';
    }

    /**
     * Returns the matcher description
     * 
     * @return string
     */
    public function getDescription()
    {
        return 'subdummy';
    }

    /**
     * Checks whether actual value is equal to the expected
     * 
     * @param mixed $actual
     * @return boolean
     */
    public function matches($actual, $epsilon = null)
    {
        return true;
    }
}