创建表单
=============

编写  `login` 视图是很简单的，我们以一个 `form` 标记开始，它的 action 属性应该是前面讲述的 `login` 动作的URL。
然后我们需要为 `LoginForm` 类中声明的属性插入标签和表单域。最后，
我们插入一个可由用户点击提交此表单的提交按钮。所有这些都可以用纯HTML代码完成。

Yii 提供了几个助手（helper）类简化视图编写。例如，
要创建一个文本输入域，我们可以调用 [CHtml::textField()]；
要创建一个下拉列表，则调用 [CHtml::dropDownList()]。

> Info|信息: 你可能想知道使用助手的好处，如果它们所需的代码量和直接写纯HTML的代码量相当的话。
> 答案就是助手可以提供比 HTML 代码更多的功能。例如，
> 如下代码将生成一个文本输入域，它可以在用户修改了其值时触发表单提交动作。
> ~~~
> [php]
> CHtml::textField($name,$value,array('submit'=>''));
> ~~~
> 不然的话你就需要写一大堆 JavaScript 。

下面，我们使用 [CHtml] 创建一个登录表单。我们假设变量 `$model` 是 `LoginForm` 的实例。

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

上述代码生成了一个更加动态的表单，例如，
[CHtml::activeLabel()] 生成一个与指定模型的特性相关的标签。
如果此特性有一个输入错误，此标签的CSS class 将变为 `error`，通过 CSS 样式改变了标签的外观。
相似的， [CHtml::activeTextField()] 为指定模型的特性生成一个文本输入域，并会在错误发生时改变它的 CSS class。

如果我们使用由 `yiic` 脚本生提供的 CSS 样式文件，生成的表单就会像下面这样：

![登录页](login1.png)

![含有错误信息的登录页](login2.png)

从版本 1.1.1 开始，提供了一个新的小物件 [CActiveForm] 以简化表单创建。
这个小物件可同时提供客户端及服务器端无缝的、一致的验证。使用 [CActiveForm],
上面的代码可重写为：

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

<div class="revision">$Id: form.view.txt 1751 2010-01-25 17:21:31Z qiang.xue $</div>