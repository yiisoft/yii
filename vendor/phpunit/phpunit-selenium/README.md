This package contains a base Testcase Class that can be used to run end-to-end tests against Selenium 2 (using its Selenium 1 backward compatible Api).

**Please direct pull requests to [giorgiosironi/phpunit-selenium](https://github.com/giorgiosironi/phpunit-selenium) for automated testing upon merging**.

A feature branch containing all the commits you want to propose works best.

Running the test suite
---

#### Manually

To run the test suite for this package, you should serve selenium-1-tests via HTTP:
```
selenium-1-tests/ $ python -m SimpleHTTPServer 8080
```
and configure the constant that you will be asked for accordingly if you do not run the server on localhost:8080.
You also need to run a Selenium Server:
```
$ java -jar  selenium-server-standalone-2.x.xjar
```
or with xvfb:
```
$ sudo xvfb-run java -jar bin/selenium-server-standalone-2.x.x.jar
```
Take a look at `before_script.sh` for an automated way to setup the HTTP and Selenium servers.

Dependencies are managed via Composer, so you must grab them like this:
```
$ composer install --dev
```
The tests can then be run with:
```
$ vendor/bin/phpunit Tests
```
You can copy phpunit.xml.dist to phpunit.xml and setup a custom configuration for browsers, but the test suite is based on Firefox on an Ubuntu machine.


#### Via Vagrant

Just run `vagrant up` (a minimal version of `v1.1` is required) and everything will be set up for you. The first start will take some time which depends on the speed of your connection (and less - speed of your computer). It will take about 160Mb to set up the VM environment and about 300Mb to download the `hashicorp/precise32` vagrant box (in case if you don't have it downloaded yet).

After the command finishes its execution just run the following:

    vagrant up
    # then it's recommended to increase RAM and CPUs (see the note below)

    vagrant ssh

    cd /vagrant
    vendor/bin/phpunit Tests
 
and you must see the `phpunit` testing `phpunit-selenium` project.

##### IMPORTANT NOTE about `Vagrant` usage
After `vagrant` has initialized the VM it makes sense to change amount of memory (and number of CPUs) manually from 384Mb by default to something near 2Gb (and 2 CPUs accordingly). I did nto do that in `Vagrantfile` deliberately since not every configuration might afford allocating 2Gb and 2 CPUs. Otherwise VM will swap hardly and at least one test will crash due to Out Of Memory.
