<?php

/**
 * This is the Phing command line launcher. It starts up the system evironment
 * tests for all important paths and properties and kicks of the main command-
 * line entry point of phing located in phing.Phing
 */

// Use composers autoload.php if available
use Phing\Exception\ConfigurationException;
use Phing\Phing;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../autoload.php')) {
    require_once __DIR__ . '/../../../autoload.php';
}

// Set any INI options for PHP
// ---------------------------

/* set include paths */
set_include_path(
            realpath(__DIR__ . '/../src') .
            PATH_SEPARATOR .
            get_include_path()
        );

/**
* Code from Symfony/Component/Console/Output/StreamOutput.php
*/
function hasColorSupport()
{
    if (DIRECTORY_SEPARATOR == '\\') {
        return 0 >= version_compare('10.0.10586', PHP_WINDOWS_VERSION_MAJOR.'.'.PHP_WINDOWS_VERSION_MINOR.'.'.PHP_WINDOWS_VERSION_BUILD)
        || false !== getenv('ANSICON')
        || 'ON' === getenv('ConEmuANSI')
        || 'xterm' === getenv('TERM');
    }
    return function_exists('posix_isatty') && @posix_isatty(STDOUT);
}

// default logger
if (!in_array('-logger', $argv) && hasColorSupport()) {
    array_splice($argv, 1, 0, ['-logger', 'Phing\Listener\AnsiColorLogger']);
}

try {

    /* Setup Phing environment */
    Phing::startup();

    // Set phing.home property to the value from environment
    // (this may be NULL, but that's not a big problem.)
    Phing::setProperty('phing.home', getenv('PHING_HOME'));
    // Grab and clean up the CLI arguments
    $args = isset($argv) ? $argv : $_SERVER['argv']; // $_SERVER['argv'] seems to not work (sometimes?) when argv is registered
    array_shift($args); // 1st arg is script name, so drop it

    // Invoke the commandline entry point
    Phing::fire($args);
} catch (ConfigurationException $x) {
    Phing::shutdown();
    Phing::printMessage($x);
    exit(-1); // This was convention previously for configuration errors.
} catch (Exception $x) {
    Phing::shutdown();

    // Assume the message was already printed as part of the build and
    // exit with non-0 error code.

    exit(1);
}
