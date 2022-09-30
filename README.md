Yii Web Programming Framework
=============================

Thank you for choosing Yii - a high-performance component-based PHP framework.

[![Build Status](https://github.com/yiisoft/yii/workflows/build/badge.svg)](https://github.com/yiisoft/yii/actions)

> Note that Yii 1.1 [has reached end of life](https://www.yiiframework.com/news/90/update-on-yii-1-1-support-and-end-of-life/)
  and will only receive necessary security fixes and fixes to adjust the code for compatibility with PHP 7 and 8 if they do not cause breaking changes.
  This allows you to keep your servers PHP version up to date in the environments where old Yii 1.1 applications are hosted and stay within the [version ranges supported by the PHP team](http://php.net/supported-versions.php).
> 
> Currently tested and supported [up to PHP 8.1](https://github.com/yiisoft/yii/blob/master/.github/workflows/build.yml#L33).

INSTALLATION
------------

Please make sure the release file is unpacked under a Web-accessible
directory. You shall see the following files and directories:

      demos/               demos
      framework/           framework source files
      requirements/        requirement checker
      CHANGELOG            describing changes in every Yii release
      LICENSE              license of Yii
      README               this file
      UPGRADE              upgrading instructions


REQUIREMENTS
------------

The minimum requirement by Yii is that your Web server supports
PHP 5.1.0 or above. Yii has been tested with Apache HTTP server
on Windows and Linux operating systems.

Please access the following URL to check if your Web server reaches
the requirements by Yii, assuming "YiiPath" is where Yii is installed:

      http://hostname/YiiPath/requirements/index.php


QUICK START
-----------

Yii comes with a command line tool called "yiic" that can create
a skeleton Yii application for you to start with.

On command line, type in the following commands:

        $ cd YiiPath/framework                (Linux)
        cd YiiPath\framework                  (Windows)

        $ ./yiic webapp ../testdrive          (Linux)
        yiic webapp ..\testdrive              (Windows)

The new Yii application will be created at "YiiPath/testdrive".
You can access it with the following URL:

        http://hostname/YiiPath/testdrive/index.php


WHAT'S NEXT
-----------

Please visit the project website for tutorials, class reference
and join discussions with other Yii users.



The Yii Developer Team
https://www.yiiframework.com
