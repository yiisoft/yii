Создание формы
==============

Написание формы не должно вызвать никаких затруднений. Мы начинаем с тега `form`, атрибут 
`action` которого должен содержать URL действия `login`, рассмотренного ранее. Затем добавляем метки 
и поля ввода для атрибутов, объявленных в классе `LoginForm`. В завершении мы вставляем кнопку 
отправки данных формы. Все это без проблем пишется на чистом HTML коде.

Для упрощения процесса создания формы Yii предоставляет несколько классов помощников (helper). 
Например, для создания текстового поля, можно вызвать метод [CHtml::textField()], для выпадающего списка —
[CHtml::dropDownList()].

> Info|Информация: Безусловно, может возникнуть справедливый вопрос, а в чем преимущество использования 
> помощника, если объем используемого кода сравним с чистым HTML кодом? Ответ прост: использование 
> помощника дает большие возможности. Например, код, приведенный ниже, создает текстовое поле, отправляющее 
> данные формы на сервер, когда пользователь меняет её значение.
> ~~~
> [php]
> CHtml::textField($name,$value,array('submit'=>''));
> ~~~
> Заметьте, что все реализовано без единой строчки JavaScript.

Ниже мы создаем представление — форму авторизации — с помощью класса [CHtml]. Здесь переменная `$model` —
экземпляр класса `LoginForm`:

~~~
[php]
<div class="form">
<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($model); ?>

<div class="row">
<?php echo CHtml::activeLabel($model,'username'); ?>
<?php echo CHtml::activeTextField($model,'username'); ?>
</div>

<div class="row">
<?php echo CHtml::activeLabel($model,'password'); ?>
<?php echo CHtml::activePasswordField($model,'password'); ?>
</div>

<div class="row rememberMe">
<?php echo CHtml::activeCheckBox($model,'rememberMe'); ?>
<?php echo CHtml::activeLabel($model,'rememberMe'); ?>
</div>

<div class="row submit">
<?php echo CHtml::submitButton('Войти'); ?>
</div>

<?php echo CHtml::endForm(); ?>
</div><!-- form -->
~~~

Форма, которую мы создали выше, обладает куда большей динамичностью. К примеру, 
[CHtml::activeLabel()] создает метку, соответствующую атрибуту модели, а если при вводе данных была 
допущена ошибка, то CSS класс метки сменится на `error`, сменив внешний вид метки на 
соответствующий CSS стиль. Похожим образом метод [CHtml::activeTextField()] создает 
текстовое поле для соответствущего атрибута модели и графически выделяет ошибки ввода. 

Если использовать CSS стиль `form.css`, предоставляемый скриптом `yiic`, созданная форма будет 
выглядеть так:

![Страница авторизации](login1.png)

![Страница авторизации с сообщением об ошибке](login2.png)

Начиная с версии 1.1.1, для создания форм можно воспользоваться новым
виджетом [CActiveForm], который позволяет реализовать валидацию как на клиенте,
так и на сервере. При использовании [CActiveForm] код отображения будет выглядеть
следующим образом:

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
		<?php echo CHtml::submitButton('Войти'); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
~~~

<div class="revision">$Id: form.view.txt 1751 2010-01-25 17:21:31Z qiang.xue $</div>