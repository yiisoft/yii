This directory contains scripts for automated test runs via the [Travis CI](http://travis-ci.org) build service. They are used for the preparation of worker instances by setting up needed extensions and configuring database access.

These scripts might be used to configure your own system for test runs. But since their primary purpose remains to support Travis in running the test cases, you would be best advised to stick to the setup notes in the tests themselves.

The scripts are:

 - [`memcache-setup.sh`](memcache-setup.sh)
   Compiles and installs the [memcache pecl extension](http://pecl.php.net/package/memcache)
 - [`mysql-setup.sh`](mysql-setup.sh)
   Prepares the [MySQL](http://www.mysql.com) server instance by creating the test database and granting access to it
 - [`postgresql-setup.sh`](postgresql-setup.sh)
   Same for [PostgreSQL](http://www.postgresql.org/)
