Yee Web Programming Framework
=============================

Thank you for choosing Yee - a high-performance component-based PHP framework.

[![Build Status](https://secure.travis-ci.org/yeesoft/yee.png)](http://travis-ci.org/yeesoft/yee)

INSTALLATION
------------

Please make sure the release file is unpacked under a Web-accessible
directory. You shall see the following files and directories:

      demos/               demos
      framework/           framework source files
      requirements/        requirement checker
      CHANGELOG            describing changes in every Yee release
      LICENSE              license of Yee
      README               this file
      UPGRADE              upgrading instructions


REQUIREMENTS
------------

The minimum requirement by Yee is that your Web server supports
PHP 5.1.0 or above. Yee has been tested with Apache HTTP server
on Windows and Linux operating systems.

Please access the following URL to check if your Web server reaches
the requirements by Yee, assuming "YeePath" is where Yee is installed:

      http://hostname/YeePath/requirements/index.php


QUICK START
-----------

Yee comes with a command line tool called "yeec" that can create
a skeleton Yee application for you to start with.

On command line, type in the following commands:

        $ cd YeePath/framework                (Linux)
        cd YeePath\framework                  (Windows)

        $ ./yeec webapp ../testdrive          (Linux)
        yeec webapp ..\testdrive              (Windows)

The new Yee application will be created at "YeePath/testdrive".
You can access it with the following URL:

        http://hostname/YeePath/testdrive/index.php


WHAT'S NEXT
-----------

Please visit the project website for tutorials, class reference
and join discussions with other Yee users.



The Yee Developer Team
http://www.yeeframework.com
