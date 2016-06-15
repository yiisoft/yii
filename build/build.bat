@echo off

rem -------------------------------------------------------------
rem  build script for Windows.
rem
rem  This is the bootstrap script for running build on Windows.
rem
rem  @author Qiang Xue <qiang.xue@gmail.com>
rem  @link http://www.yeeframework.com/
rem  @copyright 2008 Yee Software LLC
rem  @license http://www.yeeframework.com/license/
rem  @version $Id$
rem -------------------------------------------------------------

@setlocal

set BUILD_PATH=%~dp0

if "%PHP_COMMAND%" == "" set PHP_COMMAND=php.exe

%PHP_COMMAND% "%BUILD_PATH%build" %*

@endlocal