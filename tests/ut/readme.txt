This folder contains unit tests of Yii framework.
PHPUnit 3.2.11+ and xdebug (for code coverage report) are required.

To run a single unit test, use:
>> php test.php [-c] TestName
where TestName could be 'CMap', 'CMapTest', or 'CMapTest.php'
if we want to test CMap class.

To run all tests under a directory, use:
>> php test.php [-c] DirName/

To run all available tests, use:
>> php test.php [-c] *

In the above, option -c means running code coverage report
whose results will be saved under "./reports' directory.