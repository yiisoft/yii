Creating Action
===============

Once we have a model, we can start to write logic that is needed to
manipulate the model. We place this logic inside a controller action. For
the login form example, the following code is needed:

~~~
[php]
public function actionLogin()
{
	$model=new LoginForm;
	if(isset($_POST['LoginForm']))
	{
		// collects user input data
		$model->attributes=$_POST['LoginForm'];
		// validates user input and redirect to previous page if validated
		if($model->validate())
			$this->redirect(Yii::app()->user->returnUrl);
	}
	// displays the login form
	$this->render('login',array('model'=>$model));
}
~~~

In the above, we first create a `LoginForm` model instance; if the request is a
POST request (meaning the login form is submitted), we populate `$model`
with the submitted data `$_POST['LoginForm']`; we then validate the input
and if successful, redirect the user browser to the page that previously
needed authentication. If the validation fails, or if the action is
initially accessed, we render the `login` view whose content is to be
described in the next subsection.

> Tip: In the `login` action, we use `Yii::app()->user->returnUrl` to get the
URL of the page that previously needed authentication. The component
`Yii::app()->user` is of type [CWebUser] (or its child class) which
represents user session information (e.g. username, status). For more details,
see [Authentication and Authorization](/doc/guide/topics.auth).

Let's pay special attention to the following PHP statement that appears in
the `login` action:

~~~
[php]
$model->attributes=$_POST['LoginForm'];
~~~

As we described in [Securing Attribute Assignments](/doc/guide/form.model#securing-attribute-assignments),
this line of code populates the model with the user submitted data.
The `attributes` property is defined by [CModel] which
expects an array of name-value pairs and assigns each value to the
corresponding model attribute. So if `$_POST['LoginForm']` gives us
such an array, the above code would be equivalent to the following lengthy
one (assuming every needed attribute is present in the array):

~~~
[php]
$model->username=$_POST['LoginForm']['username'];
$model->password=$_POST['LoginForm']['password'];
$model->rememberMe=$_POST['LoginForm']['rememberMe'];
~~~

> Note: In order to let `$_POST['LoginForm']` to give us an array instead of a
string, we stick to a convention when naming input fields in the view. In
particular, for an input field corresponding to attribute `a` of model
class `C`, we name it as `C[a]`. For example, we would use
`LoginForm[username]` to name the input field corresponding to the
`username` attribute.

The remaining task now is to create the `login` view which should contain
an HTML form with the needed input fields.

<div class="revision">$Id$</div>