Creare formular
===============

Scrierea view-ului `login` este destul de directa. Pornim cu tagul `form`
al carui atribut action ar trebui sa fie URL-ul action-ului `login` descris anterior.
Apoi inseram label-uri si campuri input pentru atributele declarate in clasa
`LoginForm`. La sfarsit, inseram un buton submit care poate fi apasat de utilizator
pentru a trimite datele. Toate acestea pot fi facute in HTML simplu.

Yii pune la dispozitie unele clase ajutatoare (helper) pentru a facilita compunerea view-urilor.
De exemplu, pentru a crea un camp input text, putem apela [CHtml::textField()]; pentru a crea
o lista drop-down, apelam [CHtml::dropDownList()].

> Info: Ar putea aparea intrebarea: 'Dar care este beneficiul folosirii helper-elor daca
> este nevoie de o cantitate similara de cod comparativ cu codul simplu HTML?'. Raspunsul
> este ca helper-ele pot pune la dispozitie mai multe lucruri decat cod HTML. De exemplu,
> urmatorul cod va genera un camp input text care va declansa submiterea formularului
> in cazul in care valoarea sa este schimbata de catre utilizator:
> ~~~
> [php]
> CHtml::textField($name,$value,array('submit'=>''));
> ~~~
> Altfel, ar fi nevoie de scrierea de javascript peste tot.

Mai jos, folosim [CHtml] pentru a crea formularul de logare. Presupunem ca variabila
`$user` reprezinta instanta `LoginForm`.

~~~
[php]
<div class="yiiForm">
<?php echo CHtml::form(); ?>

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

</form>
</div><!-- yiiForm -->
~~~

Codul de mai sus genereaza un formular mai dinamic. De exemplu,
[CHtml::activeLabel()] genereaza un label asociat cu atributul specificat al modelului.
Daca atributul are o eroare la intrare, clasa CSS a label-ului va fi `error`, ceea ce va
schimba aspectul label-ului cu stiluri CSS corespunzatoare. In mod similar,
[CHtml::activeTextField()] genereaza un camp input text pentru atributul specificat al modelului
si ii schimba clasa CSS in cazul unei erori de intrare.

Daca folosim fisierul cu stiluri CSS `form.css` pus la dispozitie de unealta `yiic`, formularul
generat va arata in felul urmator:

![Pagina login](login1.png)

![Pagina login cu erori](login2.png)

<div class="revision">$Id: form.view.txt 772 2009-02-28 18:23:17Z qiang.xue $</div>