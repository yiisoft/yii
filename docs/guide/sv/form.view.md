Skapa Form
==========

Att skriva `login`-vyn är okomplicerat. Man börjar med en `form`-tagg vars 
action-attribut skall vara URL:en för `login`-åtgärden som tidigare beskrivits. 
Därefter infogas ledtexter och inmatningsfält för de attribut som deklarerats i 
klassen `LoginForm`. Till sist infogas en skicka-knapp som användaren klickar på 
för att posta formuläret. Allt detta kan göras i renodlad HTML-kod.

Yii tillhandahåller några hjälpklasser för att underlätta sammansättning av 
vyer. Till exempel, för att skapa ett textinmatningsfält kan 
[CHtml::textField()] användas; för att skapa en drop-down listkontroll används 
[CHtml::dropDownList()].

> Info: Man kan fråga sig vilken vinsten är med att använda hjälpklasser om de 
> erfordrar samma mängd kod jämfört med ren HTML-kod. Svaret är att hjälpklasser 
> kan leverera mer än bara HTML-kod. Till exempel, följande kod kan generera ett 
> textinmatningsfält som kan sätta igång inskickning av formuläret om dess värde 
> ändras av användare. 
> ~~~ 
> [php] 
> CHtml::textField($name,$value,array('submit'=>'')); 
> ~~~ 
> Detta skulle annars kräva att man skriver skrymmande Javascriptkod överallt.

I det följande används [CHtml] till att skapa login-formuläret. Antag att 
variabeln `$model` representerar `LoginForm`-instansen.

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

Ovanstående kod genererar ett mer dynamiskt formulär. Till exempel, 
[CHtml::activeLabel()] genererar en ledtext associerad till det specificerade 
attributet i modellen. Om attributet har ett inmatningsfel, ändras ledtextens 
CSS-klass till `error`, vilket förändrar ledtextens utseende med hjälp av 
tillämpliga CSS-stilar. På liknande sätt genererar [CHtml::activeTextField()] 
ett textinmatningsfält för det specificerade attributet i modellen och ändrar 
dess CSS-klass i händelse av inmatningsfel.

Om CSS-stilarna som används är de som finns i filen `form.css`, tillhandahållen 
av `yiic`-skriptet, kommer det genererade formuläret att se ut i stil med följande:

![Loginsidan](login1.png)

![Loginsidan med felmeddelanden](login2.png)

Med start från version 1.1.1, erbjuds en ny widget kallad [CActiveForm]
för formulärgenerering. Widgeten är kapabel att transparent och konsekvent 
stödja validering både på klient- och serversidan. Med hjälp av [CActiveForm],
kan ovanstående vy skrivas om som nedan:

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