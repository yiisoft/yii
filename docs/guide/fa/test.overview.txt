Testing
=======

Testing is an indispensable process of software development. Whether we are aware of it or not, we conduct testing all the time when we are developing a Web application. For example, when we write a class in PHP, we may use some `echo` or `die` statement to show that we implement a method correctly; when we implement a Web page containing a complex HTML form, we may try entering some test data to ensure the page interacts with us as expected. More advanced developers would write some code to automate this testing process so that each time when we need to test something, we just need to call up the code and let the computer to perform testing for us. This is known as *automated testing*, which is the main topic of this chapter.

The testing support provided by Yii includes *unit testing* and *functional testing*.

A unit test verifies that a single unit of code is working as expected. In object-oriented programming, the most basic code unit is a class. A unit test thus mainly needs to verify that each of the class interface methods works properly. That is, given different input parameters, the test verifies the method returns expected results. Unit tests are usually developed by people who write the classes being tested.

A functional test verifies that a feature (e.g. post management in a blog system) is working as expected. Compared with a unit test, a functional test sits at a higher level because a feature being tested often involves multiple classes. Functional tests are usually developed by people who know very well the system requirements (they could be either developers or quality engineers).


Test-Driven Development
-----------------------

Below we show the development cycles in the so-called [test-driven development (TDD)](http://en.wikipedia.org/wiki/Test-driven_development):

 1. Create a new test that covers a feature to be implemented. The test is expected to fail at its first execution because the feature has yet to be implemented.
 2. Run all tests and make sure the new test fails.
 3. Write code to make the new test pass.
 4. Run all tests and make sure they all pass.
 5. Refactor the code that is newly written and make sure the tests still pass.

Repeat step 1 to 5 to push forward the functionality implementation.


Test Environment Setup
----------------------

The testing supported provided by Yii requires [PHPUnit](http://www.phpunit.de/) 3.5+ and [Selenium Remote Control](http://seleniumhq.org/projects/remote-control/) 1.0+. Please refer to their documentation on how to install PHPUnit and Selenium Remote Control.

When we use the `yiic webapp` console command to create a new Yii application, it will generate the following files and directories for us to write and perform new tests:

~~~
testdrive/
   protected/                containing protected application files
      tests/                 containing tests for the application
         fixtures/           containing database fixtures
         functional/         containing functional tests
         unit/               containing unit tests
         report/             containing coverage reports
         bootstrap.php       the script executed at the very beginning
         phpunit.xml         the PHPUnit configuration file
         WebTestCase.php     the base class for Web-based functional tests
~~~

As shown in the above, our test code will be mainly put into three directories: `fixtures`, `functional` and `unit`, and the directory `report` will be used to store the generated code coverage reports.

To execute tests (whether unit tests or functional tests), we can execute the following commands in a console window:

~~~
% cd testdrive/protected/tests
% phpunit functional/PostTest.php    // executes an individual test
% phpunit --verbose functional       // executes all tests under 'functional'
% phpunit --coverage-html ./report unit
~~~

In the above, the last command will execute all tests under the `unit` directory and generate a code-coverage report under the `report` directory. Note that [xdebug extension](http://www.xdebug.org/) must be installed and enabled in order to generate code-coverage reports.


Test Bootstrap Script
--------------------

Let's take a look what may be in the `bootstrap.php` file. This file is so special because it is like the [entry script](/doc/guide/basics.entry) and is the starting point when we execute a set of tests.

~~~
[php]
$yiit='path/to/yii/framework/yiit.php';
$config=dirname(__FILE__).'/../config/test.php';
require_once($yiit);
require_once(dirname(__FILE__).'/WebTestCase.php');
Yii::createWebApplication($config);
~~~

In the above, we first include the `yiit.php` file from the Yii framework, which initializes some global constants and includes necessary test base classes.  We then create a Web application instance using the `test.php` configuration file. If we check `test.php`, we shall find that it inherits from the `main.php` configuration file and adds a `fixture` application component whose class is [CDbFixtureManager]. We will describe fixtures in detail in the next section.

~~~
[php]
return CMap::mergeArray(
	require(dirname(__FILE__).'/main.php'),
	array(
		'components'=>array(
			'fixture'=>array(
				'class'=>'system.test.CDbFixtureManager',
			),
			/* uncomment the following to provide test database connection
			'db'=>array(
				'connectionString'=>'DSN for test database',
			),
			*/
		),
	)
);
~~~

When we run tests that involve database, we should provide a test database so that the test execution does not interfere with normal development or production activities. To do so, we just need to uncomment the `db` configuration in the above and fill in the `connectionString` property with the DSN (data source name) to the test database.

With such a bootstrap script, when we run unit tests, we will have an application instance that is nearly the same as the one that serves for Web requests. The main difference is that it has the fixture manager and is using the test database.


<div class="revision">$Id$</div>
