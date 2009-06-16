This directory contains unit and functional tests for the blog demo.

 - fixtures: contains fixture data for relevant database tables.
   Each file is used to set up the fixture data for a particular table.
   The file name is the same as the table name.

 - functional: contains functional test cases.

 - unit: contains unit test cases.


In order to run these tests, the following requirements must be met:

 - PHPUnit 3.3 or higher
 - Selenium RC 1.0 or higher


Depending on your installation of Yii release, you may need to modify
the file "bootstrap.php" so that the "TEST_BASE_URL" constant contains
correct value.

To run these tests, please refer to PHPUnit documentation. The followings
are some examples:

 - Executes all unit tests:

	phpunit unit

 - Executes all functional tests (make sure Selenium RC is running):

	phpunit functional

 - Executes a particular functional test:

	phpunit functional/PostTest.php
