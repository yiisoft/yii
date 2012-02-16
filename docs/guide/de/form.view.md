Erstellen des Formulars
=======================

Der `login`-View ist schnell erstellt. Man beginnt mit dem `form` Tag,
dessen action-Attribut die URL der eben beschriebenen `login`-Action enthält.
Dann werden Label und Eingabefelder für alle Attribute der `LoginForm`-Klasse 
hinzugefügt. Ein Absendebutton schließt das Formular ab. All das lässt sich 
mit reinem HTML-Code bewerkstelligen.

Yii bietet allerdings auch einige nützliche Hilfsklassen für das Erstellen
von Views an. Textfelder kann man z.B. mit [CHtml::textField()] erzeugen, 
Auswahllisten mit [CHtml::dropDownList()].

> Info: Vielleicht fragen Sie sich, wo der Sinn dieser Helfer liegt,
> wo man doch fast genausoviel Text schreiben muss, wie der erzeugte HTML-Code.
> Die Helfer leisten aber mehr als das. Mit folgendem Beispiel kann man 
> z.B. ein Textfeld erstellen, dessen Formular automatisch abgesendet wird,
> sobald sein Inhalt verändert wird.

> ~~~
> [php]
> CHtml::textField($name,$value,array('submit'=>''));
> ~~~
>
> Ohne Helferklasse müsste man überall umständlich Javascriptcode einfügen.

Im folgenden Beispiel zeigen wir, wie man das Anmeldeformular mit Hilfe von [CHtml] 
erstellt. Die Variable `$model` soll hierbei ein `LoginForm`-Model enthalten.

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

Damit wird ein dynamisches Formular erzeugt. [CHtml::activeLabel()] 
generiert zum Beispiel ein Label für das angegebene Modelattribut.
Tritt bei diesem Attribut ein Eingabefehler auf, erhält das Label die
zusätzliche CSS-Klasse `error`. Über entsprechende CSS-Stile ändert sich so
bei fehlerhaften Eingaben die Formatierung des Labels. Das selbe gilt für Textfelder
die mit [CHtml::activeTextField()] generiert wurden.

Wenn man die vom `yiic`-Befehl angelegte CSS-Datei `form.css` verwendet,
sieht das erzeugte Formular folgendermaßen aus:


![Die Anmeldeseite](login1.png)

![Die Anmeldeseite mit Fehlern](login2.png)

Seit Version 1.1.1 kann man Formulare auch mit dem neuen [CActiveForm]-Widget
erstellen. Damit wird es möglich, eine einheitliche Validierung wahlweise auf 
Client- oder auf Serverseite durchzuführen. Mit [CActiveForm] wird der obige Code dann zu:

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
