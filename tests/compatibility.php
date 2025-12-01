<?php
/*
 * Ensures compatibility with PHPUnit < 9.x
 */

if(!class_exists('PHPUnit_Framework_Constraint') && class_exists('PHPUnit\Framework\Constraint\Constraint'))
{
    class_alias('PHPUnit\Framework\Constraint\Constraint','PHPUnit_Framework_Constraint');
}

if(!class_exists('PHPUnit_Framework_TestCase') && class_exists('PHPUnit\Framework\TestCase'))
{
    class_alias('PHPUnit\Framework\TestCase','PHPUnit_Framework_TestCase');
}

if(!class_exists('PHPUnit_Runner_Version') && class_exists('PHPUnit\Runner\Version'))
{
    class_alias('PHPUnit\Runner\Version','PHPUnit_Runner_Version');
}
