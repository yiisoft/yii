@echo off
REM PHPSpec
REM
REM GNU LESSER GENERAL PUBLIC LICENSE
REM Version 3, 29 June 2007
REM 
REM  Copyright (C) 2007 Free Software Foundation, Inc. <http://fsf.org/>
REM  Everyone is permitted to copy and distribute verbatim copies
REM  of this license document, but changing it is not allowed.
REM 
REM 
REM   This version of the GNU Lesser General Public License incorporates
REM the terms and conditions of version 3 of the GNU General Public
REM License, supplemented by the additional permissions listed below.
REM 
REM   0. Additional Definitions. 
REM 
REM   As used herein, "this License" refers to version 3 of the GNU Lesser
REM General Public License, and the "GNU GPL" refers to version 3 of the GNU
REM General Public License.
REM 
REM   "The Library" refers to a covered work governed by this License,
REM other than an Application or a Combined Work as defined below.
REM 
REM   An "Application" is any work that makes use of an interface provided
REM by the Library, but which is not otherwise based on the Library.
REM Defining a subclass of a class defined by the Library is deemed a mode
REM of using an interface provided by the Library.
REM 
REM   A "Combined Work" is a work produced by combining or linking an
REM Application with the Library.  The particular version of the Library
REM with which the Combined Work was made is also called the "Linked
REM Version".
REM 
REM   The "Minimal Corresponding Source" for a Combined Work means the
REM Corresponding Source for the Combined Work, excluding any source code
REM for portions of the Combined Work that, considered in isolation, are
REM based on the Application, and not on the Linked Version.
REM 
REM   The "Corresponding Application Code" for a Combined Work means the
REM object code and/or source code for the Application, including any data
REM and utility programs needed for reproducing the Combined Work from the
REM Application, but excluding the System Libraries of the Combined Work.
REM 
REM   1. Exception to Section 3 of the GNU GPL.
REM 
REM   You may convey a covered work under sections 3 and 4 of this License
REM without being bound by section 3 of the GNU GPL.
REM 
REM   2. Conveying Modified Versions.
REM 
REM   If you modify a copy of the Library, and, in your modifications, a
REM facility refers to a function or data to be supplied by an Application
REM that uses the facility (other than as an argument passed when the
REM facility is invoked), then you may convey a copy of the modified
REM version:
REM 
REM    a) under this License, provided that you make a good faith effort to
REM    ensure that, in the event an Application does not supply the
REM    function or data, the facility still operates, and performs
REM    whatever part of its purpose remains meaningful, or
REM 
REM    b) under the GNU GPL, with none of the additional permissions of
REM    this License applicable to that copy.
REM 
REM   3. Object Code Incorporating Material from Library Header Files.
REM 
REM   The object code form of an Application may incorporate material from
REM a header file that is part of the Library.  You may convey such object
REM code under terms of your choice, provided that, if the incorporated
REM material is not limited to numerical parameters, data structure
REM layouts and accessors, or small macros, inline functions and templates
REM (ten or fewer lines in length), you do both of the following:
REM 
REM    a) Give prominent notice with each copy of the object code that the
REM    Library is used in it and that the Library and its use are
REM    covered by this License.
REM 
REM    b) Accompany the object code with a copy of the GNU GPL and this license
REM    document.
REM 
REM   4. Combined Works.
REM 
REM   You may convey a Combined Work under terms of your choice that,
REM taken together, effectively do not restrict modification of the
REM portions of the Library contained in the Combined Work and reverse
REM engineering for debugging such modifications, if you also do each of
REM the following:
REM 
REM    a) Give prominent notice with each copy of the Combined Work that
REM    the Library is used in it and that the Library and its use are
REM    covered by this License.
REM 
REM    b) Accompany the Combined Work with a copy of the GNU GPL and this license
REM    document.
REM 
REM    c) For a Combined Work that displays copyright notices during
REM    execution, include the copyright notice for the Library among
REM    these notices, as well as a reference directing the user to the
REM    copies of the GNU GPL and this license document.
REM 
REM    d) Do one of the following:
REM 
REM        0) Convey the Minimal Corresponding Source under the terms of this
REM        License, and the Corresponding Application Code in a form
REM        suitable for, and under terms that permit, the user to
REM        recombine or relink the Application with a modified version of
REM        the Linked Version to produce a modified Combined Work, in the
REM        manner specified by section 6 of the GNU GPL for conveying
REM        Corresponding Source.
REM 
REM        1) Use a suitable shared library mechanism for linking with the
REM        Library.  A suitable mechanism is one that (a) uses at run time
REM        a copy of the Library already present on the user's computer
REM        system, and (b) will operate properly with a modified version
REM        of the Library that is interface-compatible with the Linked
REM        Version. 
REM 
REM    e) Provide Installation Information, but only if you would otherwise
REM    be required to provide such information under section 6 of the
REM    GNU GPL, and only to the extent that such information is
REM    necessary to install and execute a modified version of the
REM    Combined Work produced by recombining or relinking the
REM    Application with a modified version of the Linked Version. (If
REM    you use option 4d0, the Installation Information must accompany
REM    the Minimal Corresponding Source and Corresponding Application
REM    Code. If you use option 4d1, you must provide the Installation
REM    Information in the manner specified by section 6 of the GNU GPL
REM    for conveying Corresponding Source.)
REM 
REM   5. Combined Libraries.
REM 
REM   You may place library facilities that are a work based on the
REM Library side by side in a single library together with other library
REM facilities that are not Applications and are not covered by this
REM License, and convey such a combined library under terms of your
REM choice, if you do both of the following:
REM 
REM    a) Accompany the combined library with a copy of the same work based
REM    on the Library, uncombined with any other library facilities,
REM    conveyed under the terms of this License.
REM 
REM    b) Give prominent notice with the combined library that part of it
REM    is a work based on the Library, and explaining where to find the
REM    accompanying uncombined form of the same work.
REM 
REM   6. Revised Versions of the GNU Lesser General Public License.
REM 
REM   The Free Software Foundation may publish revised and/or new versions
REM of the GNU Lesser General Public License from time to time. Such new
REM versions will be similar in spirit to the present version, but may
REM differ in detail to address new problems or concerns.
REM 
REM   Each version is given a distinguishing version number. If the
REM Library as you received it specifies that a certain numbered version
REM of the GNU Lesser General Public License "or any later version"
REM applies to it, you have the option of following the terms and
REM conditions either of that published version or of any later version
REM published by the Free Software Foundation. If the Library as you
REM received it does not specify a version number of the GNU Lesser
REM General Public License, you may choose any version of the GNU Lesser
REM General Public License ever published by the Free Software Foundation.
REM 
REM   If the Library as you received it specifies that a proxy can decide
REM whether future versions of the GNU Lesser General Public License shall
REM apply, that proxy's public statement of acceptance of any version is
REM permanent authorization for you to choose that version for the
REM Library.
REM

if "%PHPBIN%" == "" set PHPBIN=@php_bin@
if not exist "%PHPBIN%" if "%PHP_PEAR_PHP_BIN%" neq "" goto USE_PEAR_PATH
GOTO RUN
:USE_PEAR_PATH
set PHPBIN=%PHP_PEAR_PHP_BIN%
:RUN
"%PHPBIN%" "@bin_dir@\phpspec" %*
