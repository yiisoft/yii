Entry Script
============

The entry script is the bootstrap PHP script that handles user requests
initially. It is the only PHP script that end users can directly request to
execute.

In most cases, the entry script of a Yii application contains code that
is as simple as this:

~~~
[php]
// remove the following line when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// include Yii bootstrap file
require_once('path/to/yii/framework/yii.php');
// create application instance and run
$configFile='path/to/config/file.php';
Yii::createWebApplication($configFile)->run();
~~~

The script first includes the Yii framework bootstrap file `yii.php`. It
then creates a Web application instance with the specified configuration
and runs it.

Debug Mode
----------

A Yii application can run in either debug or production mode, as determined by 
the value of the constant `YII_DEBUG`. By default, this constant value is defined
as `false`, meaning production mode. To run in debug mode, define this
constant as `true` before including the `yii.php` file. Running the application
in debug mode is less efficient because it keeps many internal logs. On the
other hand, debug mode is also more helpful during the development stage
because it provides richer debugging information when an error occurs.

<div class="revision">$Id$</div>
