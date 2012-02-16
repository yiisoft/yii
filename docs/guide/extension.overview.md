Extending Yii
=============

Extending Yii is a common activity during development. For example, when
you write a new controller, you extend Yii by inheriting its  [CController]
class; when you write a new widget, you are extending [CWidget] or an existing
widget class. If the extended code is designed to be reused by third-party
developers, we call it an *extension*.

An extension usually serves for a single purpose. In Yii's terms, it can be
classified as follows,

 * [application component](/doc/guide/basics.application#application-component)
 * [behavior](/doc/guide/basics.component#component-behavior)
 * [widget](/doc/guide/basics.view#widget)
 * [controller](/doc/guide/basics.controller)
 * [action](/doc/guide/basics.controller#action)
 * [filter](/doc/guide/basics.controller#filter)
 * [console command](/doc/guide/topics.console)
 * validator: a validator is a component class extending [CValidator].
 * helper: a helper is a class with only static methods. It is like global
   functions using the class name as their namespace.
 * [module](/doc/guide/basics.module): a module is a self-contained software unit that consists of [models](/doc/guide/basics.model), [views](/doc/guide/basics.view), [controllers](/doc/guide/basics.controller) and other supporting components. In many aspects, a module resembles to an [application](/doc/guide/basics.application). The main difference is that a module is inside an application. For example, we could have a module that provides user management functionalities.

An extension can also be a component that does not fall into any of the above
categories. As a matter of fact, Yii is carefully designed such that nearly
every piece of its code can be extended and customized to fit for individual
needs.

<div class="revision">$Id$</div>
