Creating Form
=============

Writing the `login` view is straightforward. We start with a `form` tag
whose action attribute should be the URL of the `login` action described
previously. We then insert labels and input fields for the attributes
declared in the `LoginForm` class. At the end we insert a submit button
which can be clicked by users to submit the form. All these can be done in
pure HTML code.

Yii provides a few helper classes to facilitate view composition. For
example, to create a text input field, we can call [CHtml::textField()]; to
create a drop-down list, call [CHtml::dropDownList()].

> Info: One may wonder what is the benefit of using helpers if they
> require similar amount of code when compared with plain HTML code. The
> answer is that the helpers can provide more than just HTML code. For
> example, the following code would generate a text input field which can
> trigger form submission if its value is changed by users.
> ~~~
> [php]
> CHtml::textField($name,$value,array('submit'=>''));
> ~~~
> It would otherwise require writing clumsy JavaScript everywhere.

In the following, we use [CHtml] to create the login form. We assume that
the variable `$model` represents `LoginForm` instance.

~~~
[php]
<div class="form">
<?php echo CHtml::beginForm(); ?>

	<?php echo CHtml::errorSummary($model); ?>

	<div class="row">
		<?php echo CHtml::activeLabel($model,'username'); ?>
		<?php echo CHtml::activeTextField($model,'username') ?>
	</div>

	<div class="row">
		<?php echo CHtml::activeLabel($model,'password'); ?>
		<?php echo CHtml::activePasswordField($model,'password') ?>
	</div>

	<div class="row rememberMe">
		<?php echo CHtml::activeCheckBox($model,'rememberMe'); ?>
		<?php echo CHtml::activeLabel($model,'rememberMe'); ?>
	</div>

	<div class="row submit">
		<?php echo CHtml::submitButton('Login'); ?>
	</div>

<?php echo CHtml::endForm(); ?>
</div><!-- form -->
~~~

The above code generates a more dynamic form. For example,
[CHtml::activeLabel()] generates a label associated with the specified
model attribute. If the attribute has an input error, the label's CSS class
will be changed to `error`, which changes the appearance of the label with
appropriate CSS styles. Similarly, [CHtml::activeTextField()] generates a
text input field for the specified model attribute and changes its CSS
class upon any input error.

If we use the CSS style file `form.css` provided by the `yiic` script, the
generated form would be like the following:

![The login page](login1.png)

![The login with error page](login2.png)

Starting from version 1.1.1, a new widget called [CActiveForm] is provided
to facilitate form creation. The widget is capable of supporting seamless and
consistent validation on both client and server sides. Using [CActiveForm],
the above view code can be rewritten as:

~~~
[php]
<div class="form">
<?php $form=$this->beginWidget('CActiveForm'); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->label($model,'username'); ?>
		<?php echo $form->textField($model,'username') ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'password'); ?>
		<?php echo $form->passwordField($model,'password') ?>
	</div>

	<div class="row rememberMe">
		<?php echo $form->checkBox($model,'rememberMe'); ?>
		<?php echo $form->label($model,'rememberMe'); ?>
	</div>

	<div class="row submit">
		<?php echo CHtml::submitButton('Login'); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
~~~

<div class="revision">$Id$</div>