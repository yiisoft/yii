Application
===========

The application object encapsulates the execution context within which a request is 
processed.  Its main task is to collect some basic information about the request, 
and dispatch it to an appropriate controller for further processing. It also serves 
as the central place for keeping application-level configuration settings. For this 
reason, the application object is also called the `front-controller`.

The application object is instantiated as a singleton by the [entry script](/doc/guide/basics.entry).
The application singleton can be accessed at any place via [Yii::app()|YiiBase::app].


Application Configuration
-------------------------

By default, the application object is an instance of [CWebApplication]. To customize
it, we normally provide a configuration settings file (or array) to initialize its
property values when it is being instantiated. An alternative way of customizing 
it is to extend [CWebApplication].

The configuration is an array of key-value pairs. Each key represents the name of 
a property of the application instance, and each value the corresponding property's 
initial value. For example, the following configuration array sets the [name|CApplication::name] 
and [defaultController|CWebApplication::defaultController] properties of the
application.

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

We usually store the configuration in a separate PHP script (e.g.
`protected/config/main.php`). Inside the script, we return the
configuration array as follows:

~~~
[php]
return array(...);
~~~

To apply the configuration, we pass the configuration file name as
a parameter to the application's constructor, or to [Yii::createWebApplication()]
in the following manner, usually in the [entry script](/doc/guide/basics.entry):

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Tip: If the application configuration is very complex, we can split it
into several files, each returning a portion of the configuration array.
Then, in the main configuration file, we can call PHP `include()` to include
the rest of the configuration files and merge them into a complete configuration
array.


Application Base Directory
--------------------------

The application base directory is the root directory under which all
security-sensitive PHP scripts and data reside. By default, it is a subdirectory
named `protected` that is located under the directory containing the entry
script. It can be customized by setting the [basePath|CWebApplication::basePath] 
property in the [application configuration](/doc/guide/basics.application#application-configuration).

Contents under the application base directory should be protected against being 
accessed by Web users. With [Apache HTTP server](http://httpd.apache.org/), 
this can be done easily by placing an `.htaccess` file under the base directory. 
The content of the `.htaccess` file would be as follows:

~~~
deny from all
~~~

Application Components
----------------------

The functionality of the application object can easily be customized and enriched 
using its flexible component architecture. The object manages a set of
application components, each implementing specific features.  For example, it 
performs some initial processing of a user request with the help of the [CUrlManager] 
and [CHttpRequest] components.

By configuring the [components|CApplication::components] property of
the application instance, we can customize the class and property values of any
application component used. For example, we can configure the [CMemCache] component 
so that it can use multiple memcache servers for caching, like this:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
)
~~~

In the above, we added the `cache` element to the `components` array. The
`cache` element states that the class of the component is
`CMemCache` and its `servers` property should be initialized as such.

To access an application component, use `Yii::app()->ComponentID`, where
`ComponentID` refers to the ID of the component (e.g. `Yii::app()->cache`).

An application component may be disabled by setting `enabled` to false
in its configuration. Null is returned when we access a disabled component.

> Tip: By default, application components are created on demand. This means
an application component may not be created at all if it is not accessed
during a user request. As a result, the overall performance may not be
degraded even if an application is configured with many components. Some
application components (e.g. [CLogRouter]) may need to be created regardless
of whether they are accessed or not. To do so, list their IDs in 
the [preload|CApplication::preload] application property.

Core Application Components
---------------------------

Yii predefines a set of core application components to provide features
common among Web applications. For example, the
[request|CWebApplication::request] component is used to collect 
information about a user request and provide information such as the 
requested URL and cookies.  By configuring the properties of these core 
components, we can change the default behavior of nearly every aspect 
of Yii.

Here is a list the core components that are pre-declared by [CWebApplication]:

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] -
manages the publishing of private asset files.

   - [authManager|CWebApplication::authManager]: [CAuthManager] - manages role-based access control (RBAC).

   - [cache|CApplication::cache]: [CCache] - provides data caching
functionality. Note, you must specify the actual class (e.g.
[CMemCache], [CDbCache]). Otherwise, null will be returned when you
access this component.

   - [clientScript|CWebApplication::clientScript]: [CClientScript] -
manages client scripts (javascript and CSS).

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] -
provides translated core messages used by the Yii framework.

   - [db|CApplication::db]: [CDbConnection] - provides the database
connection. Note, you must configure its
[connectionString|CDbConnection::connectionString] property in order
to use this component.

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - handles
uncaught PHP errors and exceptions.

   - [format|CApplication::format]: [CFormatter] - formats data values
for display purpose.

   - [messages|CApplication::messages]: [CPhpMessageSource] - provides
translated messages used by the Yii application.

   - [request|CWebApplication::request]: [CHttpRequest] - provides
information related to user requests.

   - [securityManager|CApplication::securityManager]: [CSecurityManager] -
provides security-related services, such as hashing and encryption.

   - [session|CWebApplication::session]: [CHttpSession] - provides
session-related functionality.

   - [statePersister|CApplication::statePersister]: [CStatePersister] -
provides the mechanism for persisting global state.

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - provides
URL parsing and creation functionality.

   - [user|CWebApplication::user]: [CWebUser] - carries identity-related
information about the current user.

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - manages themes.


Application Life Cycle
----------------------

When handling a user request, an application will undergo the following
life cycle:

   0. Pre-initialize the application with [CApplication::preinit()];

   1. Set up the class autoloader and error handling;

   2. Register core application components;

   3. Load application configuration;

   4. Initialize the application with [CApplication::init()]
       - Register application behaviors;
	   - Load static application components;

   5. Raise an [onBeginRequest|CApplication::onBeginRequest] event;

   6. Process the user request:
	   - Collect information about the request;
	   - Create a controller;
	   - Run the controller;

   7. Raise an [onEndRequest|CApplication::onEndRequest] event;

<div class="revision">$Id$</div>
