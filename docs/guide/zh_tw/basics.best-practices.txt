Best MVC Practices
==================

Although Model-View-Controller (MVC) is known by nearly every Web developer, how to properly use MVC in real application development still eludes many people. The central idea behind MVC is **code reusability and separation of concerns**. In this section, we describe some general guidelines on how to better follow MVC when developing a Yii application.

To better explain these guidelines, we assume a Web application consists of several sub-applications, such as

* front end: a public-facing website for normal end users;
* back end: a website that exposes administrative functionality for managing the application. This is usually restricted to administrative staff;
* console: an application consisting of console commands to be run in a terminal window or as scheduled jobs to support the whole application;
* Web API: providing interfaces to third parties for integrating with the application.

The sub-applications may be implemented in terms of [modules](/doc/guide/basics.module), or as a Yii application that shares some code with other sub-applications.


Model
-----

[Models](/doc/guide/basics.model) represent the underlying data structure of a Web application. Models are often shared among different sub-applications of a Web application. For example, a `LoginForm` model may be used by both the front end and the back end of an application; a `News` model may be used by the console commands, Web APIs, and the front/back end of an application. Therefore, models

* should contain properties to represent specific data;

* should contain business logic (e.g. validation rules) to ensure the represented data fulfills the design requirement;

* may contain code for manipulating data. For example, a `SearchForm` model, besides representing the search input data, may contain a `search` method to implement the actual search.

Sometimes, following the last rule above may make a model very fat, containing too much code in a single class. It may also make the model hard to maintain if the code it contains serves different purposes. For example, a `News` model may contain a method named `getLatestNews` which is only used by the front end; it may also contain a method named `getDeletedNews` which is only used by the back end. This may be fine for an application of small to medium size. For large applications, the following strategy may be used to make models more maintainable:

* Define a `NewsBase` model class which only contains code shared by different sub-applications (e.g. front end, back end);

* In each sub-application, define a `News` model by extending from `NewsBase`. Place all of the code that is specific to the sub-application in this `News` model.

So, if we were to employ this strategy in our above example, we would add a `News` model in the front end application that contains only the `getLatestNews` method, and we would add another `News` model in the back end application, which contains only the `getDeletedNews` method.

In general, models should not contain logic that deals directly with end users. More specifically, models

* should not use `$_GET`, `$_POST`, or other similar variables that are directly tied to the end-user request. Remember that a model may be used by a totally different sub-application (e.g. unit test, Web API) that may not use these variables to represent user requests. These variables pertaining to the user request should be handled by the Controller.

* should avoid embedding HTML or other presentational code. Because presentational code varies according to end user requirements (e.g. front end and back end may show the detail of a news in completely different formats), it is better taken care of by views.


View
----

[Views](/doc/guide/basics.view) are responsible for presenting models in the format that end users desire. In general, views

* should mainly contain presentational code, such as HTML, and simple PHP code to traverse, format and render data;

* should avoid containing code that performs explicit DB queries. Such code is better placed in models.

* should avoid direct access to `$_GET`, `$_POST`, or other similar variables that represent the end user request. This is the controller's job. The view should be focused on the display and layout of the data provided to it by the controller and/or model, but not attempting to access request variables or the database directly.

* may access properties and methods of controllers and models directly. However, this should be done only for the purpose of presentation.


Views can be reused in different ways:

* Layout: common presentational areas (e.g. page header, footer) can be put in a layout view.

* Partial views: use partial views (views that are not decorated by layouts) to reuse fragments of presentational code. For example, we use `_form.php` partial view to render the model input form that is used in both model creation and updating pages.

* Widgets: if a lot of logic is needed to present a partial view, the partial view can be turned into a widget whose class file is the best place to contain this logic. For widgets that generate a lot of HTML markup, it is best to use view files specific to the widget to contain the markup.

* Helper classes: in views we often need some code snippets to do tiny tasks such as formatting data or generating HTML tags. Rather than placing this code directly into the view files, a better approach is to place all of these code snippets in a view helper class. Then, just use the helper class in your view files. Yii provides an example of this approach. Yii has a powerful [CHtml] helper class that can produce commonly used HTML code. Helper classes may be put in an [autoloadable directory](/doc/guide/basics.namespace) so that they can be used without explicit class inclusion.


Controller
----------

[Controllers](/doc/guide/basics.controller) are the glue that binds models, views and other components together into a runnable application. Controllers are responsible for dealing directly with end user requests. Therefore, controllers

* may access `$_GET`, `$_POST` and other PHP variables that represent user requests;

* may create model instances and manage their life cycles. For example, in a typical model update action, the controller may first create the model instance; then populate the model with the user input from `$_POST`; after saving the model successfully, the controller may redirect the user browser to the model detail page. Note that the actual implementation of saving a model should be located in the model instead of the controller.

* should avoid containing embedded SQL statements, which are better kept in models.

* should avoid containing any HTML or any other presentational markup. This is better kept in views.


In a well-designed MVC application, controllers are often very thin, containing probably only a few dozen lines of code; while models are very fat, containing most of the code responsible for representing and manipulating the data. This is because the data structure and business logic represented by models is typically very specific to the particular application, and needs to be heavily customized to meet the specific application requirements; while controller logic often follows a similar pattern across applications and therefore may well be simplified by the underlying framework or the base classes.


<div class="revision">$Id$</div>