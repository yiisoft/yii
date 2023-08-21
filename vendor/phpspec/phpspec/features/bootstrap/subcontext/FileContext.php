<?php

use Behat\Behat\Context\BehatContext,
    Behat\Gherkin\Node\PyStringNode;

class FileContext extends BehatContext
{
    const TMP_DIR = './tmp/';
    
    /**
     * @Given /^a file named "([^"]*)" with:$/
     */
    public function aFileNamedWith($filename, PyStringNode $content)
    {
        $content = strtr((string) $content, array("'''" => '"""'));
        $this->createFile($filename, $content);
    }
    
    public function createFile($filename, $content)
    {
        if (!is_dir(static::TMP_DIR)) {
            mkdir(static::TMP_DIR);
        }
        file_put_contents(static::TMP_DIR . $filename, $content);
    }
    
    public static function deleteTemporaryDir()
    {
        $f = function ($dir) use (&$f) {
            foreach (glob($dir . '/*') as $file) {
                if (is_dir($file)) {
                    $f($file);
                } else {
                    unlink($file);
                }
            }
            if (is_dir ($dir)) {
                rmdir($dir);
            }
        };
        $f(static::TMP_DIR);
    }
}