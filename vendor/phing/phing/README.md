# ![Phing](https://github.com/phingofficial/phingofficial.github.io/blob/main/img/logo.gif?raw=true)

 [![Phing CI](https://github.com/phingofficial/phing/actions/workflows/build.yml/badge.svg)](https://github.com/phingofficial/phing/actions/workflows/build.yml)  [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phingofficial/phing/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/phingofficial/phing/?branch=main)
  [![codecov](https://codecov.io/gh/phingofficial/phing/branch/main/graph/badge.svg)](https://codecov.io/gh/phingofficial/phing)

  Thank you for using PHING!

  **PH**ing **I**s **N**ot **G**NU make; it's a PHP project build system or build tool based on Apache Ant. You can do anything with it that you could do with a traditional build system like GNU make, and its use of simple XML build files and extensible PHP "task" classes make it an easy-to-use and highly flexible build framework.

  Features include running PHPUnit unit tests (including test result and coverage reports), file transformations (e.g. token replacement, XSLT transformation, template transformations), file system operations, interactive build support, SQL execution, SCM operations (Git, Subversion and Mercurial), documentation generation (PhpDocumentor, ApiGen) and much, much more.

  If you find yourself writing custom scripts to handle the packaging, deploying, or testing of your applications, then we suggest looking at Phing. Pre-packaged with numerous out-of-the-box operation modules (tasks), and an easy-to-use OO model to extend or add your own custom tasks.

  For more information and documentation, you can visit our official website at <https://www.phing.info/>.

## Phing 3

  Phing 3 is a significant update with some breaking changes compared to Phing 2. For details, please refer to the [UPGRADING.md](UPGRADING.md) file.

## Supported PHP versions

  Phing 3.x is compatible with PHP 7.4 and higher.

## Installation

  1. **Composer**

  The preferred method to install Phing is through [Composer](https://getcomposer.org/).
  Add [phing/phing](https://packagist.org/packages/phing/phing) to the
  require-dev or require section of your project's `composer.json`
  configuration file, and run 'composer install':

         {
             "require-dev": {
                 "phing/phing": "3.0.x-dev"
             }
         }

  2. **Phar**

  Download the [Phar archive](https://www.phing.info/get/phing-latest.phar).
  The archive can then be executed by running:

         $ php phing-latest.phar

  3. **Docker** (experimental)

  The official Phing Docker image can be found on [Docker Hub](https://hub.docker.com/r/phing/phing/).

  To execute Phing inside a container and execute `build.xml` located in `/home/user`, run the following:

         $ docker run --rm -v /home/user:/opt phing/phing:3.0 -f /opt/build.xml

  4. **Phing GitHub Action**
  
  The official GitHub action [phingofficial/phing-github-action](https://github.com/phingofficial/phing-github-action) is available on [GitHub Marketplace](https://github.com/marketplace/actions/run-a-phing-build).
  
  To *Run a Phing Build* as an action, you need to setup a `.github/workflow/phing.yml` workflow file and paste the following snipped:

     name: CI
     on: [ push ]
     jobs:
       build-test:
         runs-on: ubuntu-latest

         steps:
           - uses: actions/checkout@v2
           - name: Phing Build
             uses: phingofficial/phing-github-action@v2.0.0
             with:
               version: 3.0.0-alpha4
               user-properties: prop=FooBar
               targets: foo
               verbose: true
   
   [README]([phingofficial/phing-github-action](https://github.com/phingofficial/phing-github-action)) for more info and documentation.

## Documentation

  Phing's documentation can be found at <https://www.phing.info/#docs>.

  For the source of the documentation, go to <https://github.com/phingofficial/guide>.

## Contact

  * Twitter: [@phingofficial](https://twitter.com/phingofficial)
  * Website: [https://www.phing.info](https://www.phing.info)
  * Slack:   [https://phing.slack.com/](https://slack.phing.info)
  * IRC:     Freenode, #phing
  * GitHub:  [https://www.github.com/phingofficial/phing](https://www.github.com/phingofficial/phing)

## Donations

Developing and maintaining Phing has cost many hours over the years. If you want to show your appreciation, you can use one of the following methods to donate something to the project maintainer, Michiel Rook:

  * Become a patron on [Patreon](https://www.patreon.com/michielrook)
  * [Flattr](https://flattr.com/thing/1350991/The-Phing-Project) Phing
  * Send money via [PayPal](https://www.paypal.me/MichielRook)
  * Choose something from the [Amazon Wishlist](https://www.amazon.com/hz/wishlist/ls/10DZLPG9U429I)

Thank you!

### Help us spot & fix bugs

We greatly appreciate it when users report issues or come up with feature requests. However, there are a few guidelines you should observe before submitting a new issue:

  * Make sure the issue has not already been submitted, by searching through the list of (closed) issues.
  * Support and installation questions should be asked on Twitter, Slack or IRC, not filed as issues.
  * Give a good description of the problem, this also includes the necessary steps to reproduce the problem!
  * If you have a solution - please tell us! This doesn't have to be code. We appreciate any snippets, thoughts, ideas, etc that can help us resolve the issue.

Issues can be reported on [GitHub](https://github.com/phingofficial/phing/issues).

### Pull requests

The best way to submit code to Phing is to [make a Pull Request on GitHub](https://help.github.com/articles/creating-a-pull-request).
Please help us merge your contribution quickly and keep your pull requests clean and concise: squash commits and don't introduce unnecessary (whitespace) changes.

Phing's source code is formatted according to the PSR-2 standard.

### Running the (unit) tests

If you'd like to contribute code to Phing, please make sure you run the tests before submitting your pull request. To successfully run all Phing tests, the following conditions have to be met:

  * PEAR installed, channel "pear.phing.info" discovered
  * Packages "python-docutils" and "subversion" installed
  * php.ini setting "phar.readonly" set to "Off"

Then, perform the following steps (on a clone/fork of Phing):

         $ composer install
         $ cd tests
         $ ../bin/phing

## Licensing

  This software is licensed under the terms you may find in the file
  named "LICENSE" in this directory.

Proud to use:

[![PhpStorm Logo](http://www.jetbrains.com/phpstorm/documentation/phpstorm_banners/phpstorm1/phpstorm468x60_violet.gif "Proud to use")](http://www.jetbrains.com/phpstorm)

## Contributing

We love contributions!

Thanks to all the people who already contributed!

<a href="https://github.com/phingofficial/phing/graphs/contributors">
  <img src="https://contributors-img.web.app/image?repo=phingofficial/phing" />
</a>
