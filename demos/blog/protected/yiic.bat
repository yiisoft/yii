@echo off

rem -------------------------------------------------------------
rem  Yee command line script for Windows.
rem  This is the bootstrap script for running yeec on Windows.
rem -------------------------------------------------------------

@setlocal

set BIN_PATH=%~dp0

if "%PHP_COMMAND%" == "" set PHP_COMMAND=php.exe

%PHP_COMMAND% "%BIN_PATH%yeec.php" %*

@endlocal