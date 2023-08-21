<?php
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 * Test for ability to run one selenese file.
 *
 * @author Lex Vjatkin
 */
class SeleneseFileTest extends Tests_SeleniumTestCase_BaseTestCase
{
    public static $seleneseDirectory = './selenium-1-tests/selenese/test_selenese_directory.html';
}
