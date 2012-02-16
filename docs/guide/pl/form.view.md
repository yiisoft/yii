Tworzenie formularza
=============

Stworzenie widoku `login` jest bardzo proste. Rozpoczynamy od tagu `form`, którego 
atrybut akcji powinien być adresem URL poprzednio opisanej akcji `login`.
Następnie wstawiamy etykiety oraz pola wejściowe dla atrybutów zadeklarowanych 
w klasie `LoginForm`. Na końcu wstawiamy przycisk wysyłający, który może zostać 
kliknięty przez użytkownika celem przesłania formularza. Wszystko to może zostać
zrobione za pomocą czystego kody HTML.

Yii dostarcza kilka klas helperów, aby ułatwić komponowanie widoku. Na przykład, 
aby stworzyć pole tekstowe, możemy wywołać metodę [CHtml::textField()]; aby utworzyć
listę rozwijaną (ang. drop-down) wywołujemy metodę [CHtml::dropDownList()].

> Info|Info: Można się zastanawiać, jaka jest korzyść płynąca z używania helperów 
> jeśli wymagają podobnej ilości kodu w porównaniu do zwykłego kodu HTML.
> Odpowiedź jest prosta, helpery potrafią więcej niż kod HTML. Na przykład, 
> następujący kod wygeneruje pole tekstowe, które może wywołać przesłanie formularza
> jeśli jego wartość zostanie zmieniona przez użytkownika.
> ~~~
> [php]
> CHtml::textField($name,$value,array('submit'=>''));
> ~~~
> W przeciwnym razie będzie to wymagało pisania wszędzie pokracznego kodu JavaScript.

W następnym przykładzie używamy klasy [CHtml] aby utworzyć formularz logowania. 
Zakładamy, że zmienna `$model` reprezentuje instancje `LoginForm`.

~~~
[php]
<div class="yiiForm">
<?php echo CHtml::beginForm(); ?>

<?php echo CHtml::errorSummary($user); ?>

<div class="simple">
<?php echo CHtml::activeLabel($user,'username'); ?>
<?php echo CHtml::activeTextField($user,'username'); ?>
</div>

<div class="simple">
<?php echo CHtml::activeLabel($user,'password'); ?>
<?php echo CHtml::activePasswordField($user,'password');
?>
</div>

<div class="action">
<?php echo CHtml::activeCheckBox($user,'rememberMe'); ?>
Remember me next time<br/>
<?php echo CHtml::submitButton('Login'); ?>
</div>

<?php echo CHtml::endForm(); ?>
</div><!-- yiiForm -->
~~~

Powyższy kod generuje bardziej dynamiczny formularz. Na przykład, 
metoda [CHtml::activeLabel()] generuje etykietę powiązaną z określonym atrybutem modelu.
Jeśli atrybut posiada błąd w danych wejściowych, klasa CSS etykiety będzie zmieniona 
na `error`, co zmieni wygląd etykiety odpowiednio do stylu CSS. Podobnie metoda 
[CHtml::activeTextField()] wygeneruje pole wejściowe dla określonego atrybutu modelu 
oraz zmieni jego klasę CSS w momencie wystąpienia błędów we wprowadzanych danych.

Jeśli używamy pliku stylu CSS `form.css` dostarczonego przez skrypt `yiic` wygenerowany
formularz będzie podobny do następującego:

![Strona logowania](login1.png)

![Logowania wraz ze stroną błędów](login2.png)

Podczynając od wersji 1.1.1 został dostarczony nowy widżet [CActiveForm]
aby ułatwić tworzenie formularzy. Widżet jest w stanie wspierać spójne 
i nierozróżnialne sprawdzanie poprawności zarówno po stronie klienta jak i serwera.
Przy użyciu [CActiveForm] powyższy kod widoku moze zostać przepisany na:

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