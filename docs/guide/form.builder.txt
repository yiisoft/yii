Using Form Builder
==================

When creating HTML forms, we often find that we are writing a lot of repetitive view code
which is difficult to be reused in a different project. For example, for every
input field, we need to associate it with a text label and display possible validation errors.
To improve the reusability of these code, we can use the form builder feature.


Basic Concepts
--------------

The Yii form builder uses a [CForm] object to represent the specifications needed to describe
an HTML form, including which data models are associated with the form,
what kind of input fields there are in the form, and how to render the whole form. Developers mainly
need to create and configure this [CForm] object, and then call its rendering method to display
the form.

Form input specifications are organized in terms of a form element hierarchy.
At the root of the hierarchy, it is the [CForm] object. The root form object maintains
its children in two collections: [CForm::buttons] and [CForm::elements]. The former
contains the button elements (such as submit buttons, reset buttons), while the latter
contains the input elements, static text and sub-forms. A sub-form is a [CForm] object contained
in the [CForm::elements] collection of another form. It can have its own data model,
[CForm::buttons] and [CForm::elements] collections.

When users submit a form, the data entered into the input fields of the whole form hierarchy are submitted,
including those input fields that belong to the sub-forms. [CForm] provides convenient methods
that can automatically assign the input data to the corresponding model attributes and perform
data validation.


Creating a Simple Form
----------------------

In the following, we show how to use the form builder to create a login form.

First, we write the login action code:

~~~
[php]
public function actionLogin()
{
	$model = new LoginForm;
	$form = new CForm('application.views.site.loginForm', $model);
	if($form->submitted('login') && $form->validate())
		$this->redirect(array('site/index'));
	else
		$this->render('login', array('form'=>$form));
}
~~~

In the above code, we create a [CForm] object using the specifications pointed to
by the path alias `application.views.site.loginForm` (to be explained shortly).
The [CForm] object is associated with the `LoginForm` model as described in
[Creating Model](/doc/guide/form.model).

As the code reads, if the form is submitted and all inputs are validated without
any error, we would redirect the user browser to the `site/index` page. Otherwise,
we render the `login` view with the form.

The path alias `application.views.site.loginForm` actually refers to the PHP file
`protected/views/site/loginForm.php`. The file should return a PHP array representing
the configuration needed by [CForm], as shown in the following:

~~~
[php]
return array(
	'title'=>'Please provide your login credential',

    'elements'=>array(
        'username'=>array(
            'type'=>'text',
            'maxlength'=>32,
        ),
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),
        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),

    'buttons'=>array(
        'login'=>array(
            'type'=>'submit',
            'label'=>'Login',
        ),
    ),
);
~~~

The configuration is an associative array consisting of name-value pairs that are
used to initialize the corresponding properties of [CForm]. The most important properties
to configure, as we aformentioned, are [CForm::elements] and [CForm::buttons]. Each
of them takes an array specifying a list of form elements. We will give more details on
how to configure form elements in the next sub-section.

Finally, we write the `login` view script, which can be as simple as follows,

~~~
[php]
<h1>Login</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~

> Tip: The above code `echo $form;` is equivalent to `echo $form->render();`.
> This is because [CForm] implements `__toString` magic method which calls
> `render()` and returns its result as the string representation of the form object.


Specifying Form Elements
------------------------

Using the form builder, the majority of our effort is shifted from writing view script code
to specifying the form elements. In this sub-section, we describe how to specify the [CForm::elements]
property. We are not going to describe [CForm::buttons] because its configuration is nearly
the same as [CForm::elements].

The [CForm::elements] property accepts an array as its value. Each array element specifies a single
form element which can be an input element, a static text string or a sub-form.

### Specifying Input Element

An input element mainly consists of a label, an input field, a hint text and an error display.
It must be associated with a model attribute. The specification for an input element is represented
as a [CFormInputElement] instance. The following code in the [CForm::elements] array
specifies a single input element:

~~~
[php]
'username'=>array(
    'type'=>'text',
    'maxlength'=>32,
),
~~~

It states that the model attribute is named as `username`, and the input field type is `text` whose
`maxlength` attribute is 32.

Any writable property of [CFormInputElement] can be configured like above. For example, we may specify
the [hint|CFormInputElement::hint] option in order to display a hint text, or we may specify the
[items|CFormInputElement::items] option if the input field is a list box, a drop-down list, a check-box list
or a radio-button list. If an option name is not a property of [CFormInputElement], it will be treated
the attribute of the corresponding HTML input element. For example, because `maxlength` in the above is not
a property of [CFormInputElement], it will be rendered as the `maxlength` attribute of the HTML text input field.

The [type|CFormInputElement::type] option deserves additional attention. It specifies the type of the input
field to be rendered. For example, the `text` type means a normal text input field should be rendered;
the `password` type means a password input field should be rendered. [CFormInputElement] recognizes the following
built-in types:

 - text
 - hidden
 - password
 - textarea
 - file
 - radio
 - checkbox
 - listbox
 - dropdownlist
 - checkboxlist
 - radiolist

Among the above built-in types, we would like to describe a bit more about the usage of those "list" types,
which include `dropdownlist`, `checkboxlist` and `radiolist`. These types require setting the [items|CFormInputElement::items]
property of the corresponding input element. One can do so like the following:

~~~
[php]
'gender'=>array(
    'type'=>'dropdownlist',
    'items'=>User::model()->getGenderOptions(),
    'prompt'=>'Please select:',
),

...

class User extends CActiveRecord
{
	public function getGenderOptions()
	{
		return array(
			0 => 'Male',
			1 => 'Female',
		);
	}
}
~~~

The above code will generate a drop-down list selector with prompt text "please select:". The selector options
include "Male" and "Female", which are returned by the `getGenderOptions` method in the `User` model class.

Besides these built-in types, the [type|CFormInputElement::type] option can also take a widget class name
or the path alias to it. The widget class must extend from [CInputWidget] or [CJuiInputWidget]. When rendering the input element,
an instance of the specified widget class will be created and rendered. The widget will be configured using
the specification as given for the input element.


### Specifying Static Text

In many cases, a form may contain some decorational HTML code besides the input fields. For example, a horizontal
line may be needed to separate different portions of the form; an image may be needed at certain places to
enhance the visual appearance of the form. We may specify these HTML code as static text in the [CForm::elements]
collection. To do so, we simply specify a static text string as an array element in the appropriate position in
[CForm::elements]. For example,

~~~
[php]
return array(
    'elements'=>array(
		......
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),

        '<hr />',

        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),
	......
);
~~~

In the above, we insert a horizontal line between the `password` input and the `rememberMe` input.

Static text is best used when the text content and their position are irregular. If each input element
in a form needs to be decorated similarly, we should customize the form rendering approach, as to be explained
shortly in this section.


### Specifying Sub-form

Sub-forms are used to divide a lengthy form into several logically connected portions. For example,
we may divide user registration form into two sub-forms: login information and profile information.
Each sub-form may or may not be associated with a data model. In the user registration form example,
if we store user login information and profile information in two separate database tables (and thus
two data models), then each sub-form would be associated with a corresponding data model. If we store
everything in a single database table, then neither sub-form has a data model because they share the
same model with the root form.

A sub-form is also represented as a [CForm] object. In order to specify a sub-form, we should configure
the [CForm::elements] property with an element whose type is `form`:

~~~
[php]
return array(
    'elements'=>array(
		......
        'user'=>array(
            'type'=>'form',
            'title'=>'Login Credential',
            'elements'=>array(
            	'username'=>array(
            		'type'=>'text',
            	),
            	'password'=>array(
            		'type'=>'password',
            	),
            	'email'=>array(
            		'type'=>'text',
            	),
            ),
        ),

        'profile'=>array(
        	'type'=>'form',
        	......
        ),
        ......
    ),
	......
);
~~~

Like configuring a root form, we mainly need to specify the [CForm::elements] property for a sub-form.
If a sub-form needs to be associated with a data model, we can configure its [CForm::model] property as well.

Sometimes, we may want to represent a form using a class other than the default [CForm]. For example,
as will show shortly in this section, we may extend [CForm] to customize the form rendering logic.
By specifying the input element type to be `form`, a sub-form will automatically be represented as an object
whose class is the same as its parent form. If we specify the input element type to be something like
`XyzForm` (a string terminated with `Form`), then the sub-form will be represented as a `XyzForm` object.


Accessing Form Elements
-----------------------

Accessing form elements is as simple as accessing array elements. The [CForm::elements] property returns
a [CFormElementCollection] object, which extends from [CMap] and allows accessing its elements like a normal
array. For example, in order to access the `username` element in the login form example, we can use the following
code:

~~~
[php]
$username = $form->elements['username'];
~~~

And to access the `email` element in the user registration form example, we can use

~~~
[php]
$email = $form->elements['user']->elements['email'];
~~~

Because [CForm] implements array access for its [CForm::elements] property, the above code can be further
simplified as:

~~~
[php]
$username = $form['username'];
$email = $form['user']['email'];
~~~


Creating a Nested Form
----------------------

We already described sub-forms. We call a form with sub-forms a nested form. In this section,
we use the user registration form as an example to show how to create a nested form associated
with multiple data models. We assume the user credential information is stored as a `User` model,
while the user profile information is stored as a `Profile` model.

We first create the `register` action as follows:

~~~
[php]
public function actionRegister()
{
	$form = new CForm('application.views.user.registerForm');
	$form['user']->model = new User;
	$form['profile']->model = new Profile;
	if($form->submitted('register') && $form->validate())
	{
		$user = $form['user']->model;
		$profile = $form['profile']->model;
		if($user->save(false))
		{
			$profile->userID = $user->id;
			$profile->save(false);
			$this->redirect(array('site/index'));
		}
	}

	$this->render('register', array('form'=>$form));
}
~~~

In the above, we create the form using the configuration specified by `application.views.user.registerForm`.
After the form is submitted and validated successfully, we attempt to save the user and profile models.
We retrieve the user and profile models by accessing the `model` property of the corresponding sub-form objects.
Because the input validation is already done, we call `$user->save(false)` to skip the validation. We do
this similarly for the profile model.

Next, we write the form configuration file `protected/views/user/registerForm.php`:

~~~
[php]
return array(
	'elements'=>array(
		'user'=>array(
			'type'=>'form',
			'title'=>'Login information',
			'elements'=>array(
		        'username'=>array(
		            'type'=>'text',
		        ),
		        'password'=>array(
		            'type'=>'password',
		        ),
		        'email'=>array(
		            'type'=>'text',
		        )
			),
		),

		'profile'=>array(
			'type'=>'form',
			'title'=>'Profile information',
			'elements'=>array(
		        'firstName'=>array(
		            'type'=>'text',
		        ),
		        'lastName'=>array(
		            'type'=>'text',
		        ),
			),
		),
	),

    'buttons'=>array(
        'register'=>array(
            'type'=>'submit',
            'label'=>'Register',
        ),
    ),
);
~~~

In the above, when specifying each sub-form, we also specify its [CForm::title] property.
The default form rendering logic will enclose each sub-form in a field-set which uses this property
as its title.

Finally, we write the simple `register` view script:

~~~
[php]
<h1>Register</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~


Customizing Form Display
------------------------

The main benefit of using form builder is the separation of logic (form configuration stored in a separate file)
and presentation ([CForm::render] method). As a result, we can customize the form display by either overriding
[CForm::render] or providing a partial view to render the form. Both approaches can keep the form configuration
intact and can be reused easily.

When overriding [CForm::render], one mainly needs to traverse through the [CForm::elements] and [CForm::buttons]
collections and call the [CFormElement::render] method of each form element. For example,

~~~
[php]
class MyForm extends CForm
{
	public function render()
	{
		$output = $this->renderBegin();

		foreach($this->getElements() as $element)
			$output .= $element->render();

		$output .= $this->renderEnd();

		return $output;
	}
}
~~~

We may also write a view script `_form` to render a form:

~~~
[php]
<?php
echo $form->renderBegin();

foreach($form->getElements() as $element)
	echo $element->render();

echo $form->renderEnd();
~~~

To use this view script, we can simply call:

~~~
[php]
<div class="form">
$this->renderPartial('_form', array('form'=>$form));
</div>
~~~

If a generic form rendering does not work for a particular form (for example, the form needs some
irregular decorations for certain elements), we can do like the following in a view script:

~~~
[php]
some complex UI elements here

<?php echo $form['username']; ?>

some complex UI elements here

<?php echo $form['password']; ?>

some complex UI elements here
~~~

In the last approach, the form builder seems not to bring us much benefit, as we still need to write
similar amount of form code. It is still beneficial, however, that the form is specified using
a separate configuration file as it helps developers to better focus on the logic.


<div class="revision">$Id$</div>