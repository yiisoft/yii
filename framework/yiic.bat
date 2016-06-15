@echo off

rem -------------------------------------------------------------
rem  Yee command line script for Windows.
rem
rem  This is the bootstrap script for running yeec on Windows.
rem
rem  @author Qiang Xue <qiang.xue@gmail.com>
rem  @link http://www.yeeframework.com/
rem  @copyright 2008 Yee Software LLC
rem  @license http://www.yeeframework.com/license/
rem  @version $Id$
rem -------------------------------------------------------------

@setlocal

set YII_PATH=%~dp0

if "%PHP_COMMAND%" == "" set PHP_COMMAND=php.exe

"%PHP_COMMAND%" "%YII_PATH%yeec" %*

@endlocal