Error Handling
==============

Yii provides a complete error handling framework based on the PHP 5
exception mechanism. When the application is created to handle an incoming
user request, it registers its [handleError|CApplication::handleError]
method to handle PHP warnings and notices; and it registers its
[handleException|CApplication::handleException] method to handle uncaught
PHP exceptions. Consequently, if a PHP warning/notice or an uncaught
exception occurs during the application execution, one of the error
handlers will take over the control and start the necessary error handling
procedure.

> Tip: The registration of error handlers is done in the application's
constructor by calling PHP functions
[set_exception_handler](http://www.php.net/manual/en/function.set-exception-handler.php)
and [set_error_handler](http://www.php.net/manual/en/function.set-error-handler.php).
If you do not want Yii to handle the errors and exceptions, you may define
constant `YII_ENABLE_ERROR_HANDLER` and `YII_ENABLE_EXCEPTION_HANDLER` to
be false in the [entry script](/doc/guide/basics.entry).

By default, [handleError|CApplication::handleError] (or
[handleException|CApplication::handleException]) will raise an
[onError|CApplication::onError] event (or
[onException|CApplication::onException] event). If the error (or exception)
is not handled by any event handler, it will call for help from the
[errorHandler|CErrorHandler] application component.

Raising Exceptions
------------------

Raising exceptions in Yii is not different from raising a normal PHP
exception. One uses the following syntax to raise an exception when needed:

~~~
[php]
throw new ExceptionClass('ExceptionMessage');
~~~

Yii defines three exception classes: [CException], [CDbException] and
[CHttpException]. [CException] is a generic exception class. [CDbException]
represents an exception that is caused by some DB-related operations.
[CHttpException] represents an exception that should be displayed to end users
and carries a [statusCode|CHttpException::statusCode] property representing an HTTP
status code. The class of an exception determines how it should be
displayed, as we will explain next.

> Tip: Raising a [CHttpException] exception is a simple way of reporting
errors caused by user misoperation. For example, if the user provides an
invalid post ID in the URL, we can simply do the following to show a 404
error (page not found):
~~~
[php]
// if post ID is invalid
throw new CHttpException(404,'The specified post cannot be found.');
~~~

Displaying Errors
-----------------

When an error is forwarded to the [CErrorHandler] application component,
it chooses an appropriate view to display the error. If the error is meant
to be displayed to end users, such as a [CHttpException], it will use a
view named `errorXXX`, where `XXX` stands for the HTTP status code (e.g.
400, 404, 500). If the error is an internal one and should only be
displayed to developers, it will use a view named `exception`. In the
latter case, complete call stack as well as the error line information will
be displayed.

> Info: When the application runs in [production
mode](/doc/guide/basics.entry#debug-mode), all errors including those internal
ones will be displayed using view `errorXXX`. This is because the call
stack of an error may contain sensitive information. In this case,
developers should rely on the error logs to determine what is the real
cause of an error.

[CErrorHandler] searches for the view file corresponding to a view in the
following order:

   1. `WebRoot/themes/ThemeName/views/system`: this is the `system` view
directory under the currently active theme.

   2. `WebRoot/protected/views/system`: this is the default `system` view
directory for an application.

   3. `yii/framework/views`: this is the standard system view directory
provided by the Yii framework.

Therefore, if we want to customize the error display, we can simply create
error view files under the system view directory of our application or
theme. Each view file is a normal PHP script consisting of mainly HTML
code. For more details, please refer to the default view files under the
framework's `view` directory.


Handling Errors Using an Action
-------------------------------

Yii allows using a [controller action](/doc/guide/basics.controller#action)
to handle the error display work. To do so, we should configure the error handler
in the application configuration as follows:

~~~
[php]
return array(
	......
	'components'=>array(
		'errorHandler'=>array(
			'errorAction'=>'site/error',
		),
	),
);
~~~

In the above, we configure the [CErrorHandler::errorAction] property to be the route
`site/error` which refers to the `error` action in `SiteController`. We may use a different
route if needed.

We can write the `error` action like the following:

~~~
[php]
public function actionError()
{
	if($error=Yii::app()->errorHandler->error)
		$this->render('error', $error);
}
~~~

In the action, we first retrieve the detailed error information from [CErrorHandler::error].
If it is not empty, we render the `error` view together with the error information.
The error information returned from [CErrorHandler::error] is an array with the following fields:

 * `code`: the HTTP status code (e.g. 403, 500);
 * `type`: the error type (e.g. [CHttpException], `PHP Error`);
 * `message`: the error message;
 * `file`: the name of the PHP script file where the error occurs;
 * `line`: the line number of the code where the error occurs;
 * `trace`: the call stack of the error;
 * `source`: the context source code where the error occurs.

> Tip: The reason we check if [CErrorHandler::error] is empty or not is because
the `error` action may be directly requested by an end user, in which case there is no error.
Since we are passing the `$error` array to the view, it will be automatically expanded
to individual variables. As a result, in the view we can access directly the variables such as
`$code`, `$type`.


Message Logging
---------------

A message of level `error` will always be logged when an error occurs. If
the error is caused by a PHP warning or notice, the message will be logged
with category `php`; if the error is caused by an uncaught exception, the
category would be `exception.ExceptionClassName` (for [CHttpException] its
[statusCode|CHttpException::statusCode] will also be appended to the
category). One can thus exploit the [logging](/doc/guide/topics.logging)
feature to monitor errors happened during application execution.

<div class="revision">$Id$</div>
