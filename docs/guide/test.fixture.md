Defining Fixtures
=================

Automated tests need to be executed many times. To ensure the testing process is repeatable, we would like to run the tests in some known state called *fixture*. For example, to test the post creation feature in a blog application, each time when we run the tests, the tables storing relevant data about posts (e.g. the `Post` table, the `Comment` table) should be restored to some fixed state. The [PHPUnit documentation](http://www.phpunit.de/manual/current/en/fixtures.html) has described well about generic fixture setup. In this section, we mainly describe how to set up database fixtures, as we just described in the example.

Setting up database fixtures is perhaps one of the most time-consuming parts in testing database-backed Web applications. Yii introduces the [CDbFixtureManager] application component to alleviate this problem. It basically does the following things when running a set of tests:

 * Before all tests run, it resets all tables relevant to the tests to some known state.
 * Before a single test method runs, it resets the specified tables to some known state.
 * During the execution of a test method, it provides access to the rows of the data that contribute to the fixture.

To use [CDbFixtureManager], we configure it in the [application configuration](/doc/guide/basics.application#application-configuration) as follows,

~~~
[php]
return array(
	'components'=>array(
		'fixture'=>array(
			'class'=>'system.test.CDbFixtureManager',
		),
	),
);
~~~

We then provide the fixture data under the directory `protected/tests/fixtures`. This directory may be customized to be a different one by configuring the [CDbFixtureManager::basePath] property in the application configuration. The fixture data is organized as a collection of PHP files called fixture files. Each fixture file returns an array representing the initial rows of data for a particular table. The file name is the same as the table name. The following is an example of the fixture data for the `Post` table stored in a file named `Post.php`:

~~~
[php]
<?php
return array(
	'sample1'=>array(
		'title'=>'test post 1',
		'content'=>'test post content 1',
		'createTime'=>1230952187,
		'authorId'=>1,
	),
	'sample2'=>array(
		'title'=>'test post 2',
		'content'=>'test post content 2',
		'createTime'=>1230952287,
		'authorId'=>1,
	),
);
~~~

As we can see, two rows of data are returned in the above. Each row is represented as an associative array whose keys are column names and whose values are the corresponding column values. In addition, each row is indexed by a string (e.g. `sample1`, `sample2`) which is called *row alias*. Later when we write test scripts, we can conveniently refer to a row by its alias. We will describe this in detail in the next section.

You may notice that we do not specify the `id` column values in the above fixture. This is because the `id` column is defined to be an auto-incremental primary key whose value will be filled up when we insert new rows.

When [CDbFixtureManager] is referenced for the first time, it will go through every fixture file and use it to reset the corresponding table. It resets a table by truncating the table, resetting the sequence value for the table's auto-incremental primary key, and then inserting the rows of data from the fixture file into the table.

Sometimes, we may not want to reset every table which has a fixture file before we run a set of tests, because resetting too many fixture files could take very long time. In this case, we can write a PHP script to do the initialization work in a customized way. The script should be saved in a file named `init.php` under the same directory that contains other fixture files. When [CDbFixtureManager] detects the existence of this script, it will execute this script instead of resetting every table.

It is also possible that we do not like the default way of resetting a table, i.e., truncating it and inserting it with the fixture data. If this is the case, we can write an initialization script for the specific fixture file. The script must be named as the table name suffixed with `.init.php`. For example, the initialization script for the `Post` table would be `Post.init.php`. When [CDbFixtureManager] sees this script, it will execute this script instead of using the default way to reset the table.

> Tip: Having too many fixture files could increase the test time dramatically. For this reason, you should only provide fixture files for those tables whose content may change during the test. Tables that serve as look-ups do not change and thus do not need fixture files.

In the next two sections, we will describe how to make use of the fixtures managed by [CDbFixtureManager] in unit tests and functional tests.

<div class="revision">$Id$</div>