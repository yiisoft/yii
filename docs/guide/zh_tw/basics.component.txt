Component
=========

Yii applications are built upon components which are objects
written to a specification. A component is an instance of
[CComponent] or its derived class. Using a component mainly involves
accessing its properties and raising/handling its events. The base class
[CComponent] specifies how to define properties and events.

Component Property
------------------

A component property is like an object's public member variable. We can
read its value or assign a value to it. For example,

~~~
[php]
$width=$component->textWidth;     // get the textWidth property
$component->enableCaching=true;   // set the enableCaching property
~~~

To define a component property, we can simply declare a public member
variable in the component class. A more flexible way, however, is by
defining getter and setter methods like the following:

~~~
[php]
public function getTextWidth()
{
    return $this->_textWidth;
}

public function setTextWidth($value)
{
    $this->_textWidth=$value;
}
~~~

The above code defines a writable property named `textWidth` (the name is
case-insensitive). When reading the property, `getTextWidth()` is  invoked
and its returned value becomes the property value; Similarly, when writing
the property, `setTextWidth()` is invoked. If the setter method is not
defined, the property would be read-only and writing it would throw an
exception. Using getter and setter methods to define a property has the
benefit that additional logic (e.g. performing validation, raising events)
can be executed when reading and writing the property.

>Note: There is a slight difference between a property defined via getter/setter
methods and a class member variable. The name of the former
is case-insensitive while the latter is case-sensitive.

Component Event
---------------

Component events are special properties that take methods (called `event
handlers`) as their values. Attaching (assigning) a method to an event will
cause the method to be invoked automatically at the places where the event
is raised. Therefore, the behavior of a component can be modified in a way
that may not be foreseen during the development of the component.

A component event is defined by defining a method whose name starts with
`on`. Like property names defined via getter/setter methods, event names are
case-insensitive. The following code defines an `onClicked` event:

~~~
[php]
public function onClicked($event)
{
	$this->raiseEvent('onClicked', $event);
}
~~~

where `$event` is an instance of [CEvent] or its child class representing
the event parameter.

We can attach a method to this event as follows:

~~~
[php]
$component->onClicked=$callback;
~~~

where `$callback` refers to a valid PHP callback. It can be a global
function or a class method. If the latter, the callback must be given as an
array: `array($object,'methodName')`.

The signature of an event handler must be as follows:

~~~
[php]
function methodName($event)
{
    ......
}
~~~

where `$event` is the parameter describing the event (it originates from
the `raiseEvent()` call). The `$event` parameter is an instance of [CEvent] or
its derived class. At the minimum, it contains the information about who
raises the event.

An event handler can also be an anonymous function which is supported by PHP 5.3 or above. For example,

~~~
[php]
$component->onClicked=function($event) {
	......
}
~~~

If we call `onClicked()` now, the `onClicked` event will be raised (inside
`onClicked()`), and the attached event handler will be invoked
automatically.

An event can be attached with multiple handlers. When the event is raised,
the handlers will be invoked in the order that they are attached to the event.
If a handler decides to prevent the rest handlers from being invoked, it can set
[$event->handled|CEvent::handled] to be true.


Component Behavior
------------------

A component supports the [mixin](http://en.wikipedia.org/wiki/Mixin) pattern
and can be attached with one or several behaviors. A *behavior* is an object
whose methods can be 'inherited' by its attached component through the means of collecting
functionality instead of specialization (i.e., normal class inheritance).
A component can be attached with several behaviors and thus achieve 'multiple inheritance'.

Behavior classes must implement the [IBehavior] interface. Most behaviors can
extend from the [CBehavior] base class. If a behavior needs to be attached to
a [model](/doc/guide/basics.model), it may also extend from [CModelBehavior] or
[CActiveRecordBehavior] which implements additional features specifc for models.

To use a behavior, it must be attached to a component first by calling the behavior's
[attach()|IBehavior::attach] method. Then we can call a behavior method via the component:

~~~
[php]
// $name uniquely identifies the behavior in the component
$component->attachBehavior($name,$behavior);
// test() is a method of $behavior
$component->test();
~~~

An attached behavior can be accessed like a normal property of the component.
For example, if a behavior named `tree` is attached to a component, we can
obtain the reference to this behavior object using:

~~~
[php]
$behavior=$component->tree;
// equivalent to the following:
// $behavior=$component->asa('tree');
~~~

A behavior can be temporarily disabled so that its methods are not available via the component.
For example,

~~~
[php]
$component->disableBehavior($name);
// the following statement will throw an exception
$component->test();
$component->enableBehavior($name);
// it works now
$component->test();
~~~

It is possible that two behaviors attached to the same component have methods of the same name.
In this case, the method of the first attached behavior will take precedence.

When used together with [events](/doc/guide/basics.component#component-event), behaviors are even more powerful.
A behavior, when being attached to a component, can attach some of its methods to some events
of the component. By doing so, the behavior gets a chance to observe or change the normal
execution flow of the component.

A behavior's properties can also be accessed via the component it
is attached to. The properties include both the public member variables and the properties defined
via getters and/or setters of the behavior. For example, if a behavior has a property named `xyz`
and the behavior is attached to a component `$a`. Then we can use the expression `$a->xyz` to access
the behavior's property.

<div class="revision">$Id$</div>