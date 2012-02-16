View
====

Un view este un fisier PHP care contine in principal elemente ale interfetei
cu utilizatorul. Poate contine instructiuni PHP, dar este recomandat ca aceste instructiuni
sa nu schimbe modelele de date si sa fie relativ simple. In spiritul de a mentine separarea
intre programare si prezentare, bucatile mari de programare ar trebui puse in controller sau
in model, nu in view.

Un view are un nume care este folosit pentru a indentifica fisierul atunci cand trebuie generat view-ul.
Numele unui view este acelasi cu numele fisierului view. De exemplu, view-ul `edit` se refera la
un fisier view cu numele `edit.php`. Pentru a genera un view, apelam [CController::render()]
cu numele view-ului. Metoda va cauta fisierul view corespunzator in directorul `protected/views/ControllerID`.

In fisierul view, putem accesa instanta controller-ului folosind `$this`. Putem astfel sa `primim` informatii
din afara view-ului, in special proprietatile controller-ului, prin evaluarea `$this->propertyName` in interiorul view-lui.

Putem de asemenea sa folosim metoda de a `trimite` date view-ului inainte de generarea lui:

~~~
[php]
$this->render('edit', array(
	'var1'=>$value1,
	'var2'=>$value2,
));
~~~

Atfel, metoda [render()|CController::render] va extrage al doilea array de parametri. Acesti parametri
vor deveni variabile in interiorul view-ului. Le vom putea accesa ca variabile locale, `$var1` si `$var2`.

Layout
------

Layout-ul este un view special. Este folosit pentru a crea un container unic pentru view-uri.
Poate sa contina portiuni ale interfetei utilizator care sunt la fel in mai multe view-uri.
De exemplu, un layout ar putea contine portiuni header si footer si sa includa la mijloc continutul
view-ului:

~~~
[php]
......aici se defineste header-ul......
<?php echo $content; ?>
......aici se defineste footer-ul......
~~~

`$content` contine rezultatul generat pentru un view.

Layout este implicit aplicat atunci cand se apeleaza [render()|CController::render].
Implicit, fisierul view `protected/views/layouts/main.php` este folosit ca layout.
Poate fi schimbat prin modificarea ori a [CWebApplication::layout]
ori a [CController::layout]. Pentru a genera un view fara sa ii aplicam un layout,
folosim [renderPartial()|CController::renderPartial].

Widget
------

Un widget este o instanta a clasei [CWidget] sau a unei clase derivate. Este o componenta
creata in special pentru scopuri de prezentare. Widget-urile sunt incluse de obicei
intr-un fisier view pentru a genera unele interfete utilizator complexe, dar de sine statatoare.
De exemplu, widget-ul calendar poate fi folosit pentru a genera un calendar complex.
Widget-urile ajuta la o mai buna separare si reutilizare a codului din interfata utilizator.

Pentru a folosi un widget intr-un fisier view, facem urmatoarele:

~~~
[php]
<?php $this->beginWidget('path.to.WidgetClass'); ?>
...continut body care poate fi capturat de catre widget...
<?php $this->endWidget(); ?>
~~~

sau

~~~
[php]
<?php $this->widget('cale.catre.WidgetClass'); ?>
~~~

A doua metoda este folosita atunci cand widget-ul nu necesita vreun continut body.

Pentru a le customiza comportamentul, widget-urile pot fi configurate prin setarea
valorilor initiale ale proprietatilor atunci cand se apeleaza
[CBaseController::beginWidget] sau [CBaseController::widget]. De exemplu,
atunci cand folosim widget-ul [CMaskedTextField], am vrea sa specificam un mask
care va fi folosit. Putem face acest lucru transmitand un array cu acele proprietati
si cu valorile lor initiale dupa cum urmeaza:

~~~
[php]
<?php
$this->widget('CMaskedTextField',array(
	'mask'=>'99/99/9999'
));
?>
~~~

Ca de obicei, array-ul contine perechi key-value. Key contine numele proprietatii,
iar value contine valoarea initiala a proprietatii respective.

Pentru a defini un nou widget, derivam [CWidget] si suprascriem metodele
[init()|CWidget::init] si [run()|CWidget::run]:

~~~
[php]
class MyWidget extends CWidget
{
	public function init()
	{
		// aceasta metoda este apelata de catre CController::beginWidget()
	}

	public function run()
	{
		// aceasta metoda este apelata de catre CController::endWidget()
	}
}
~~~

La fel ca un controller, un widget poate de asemenea avea un view personal.
Implicit, fisierele view ale widget-urilor se gasesc in subdirectorul
`views` al directorului care contine fisierul clasei widget-ului.
Aceste view-uri pot fi generate prin apelarea [CWidget::render()], la fel ca in controller.
Singura diferenta este ca la view-ul unui widget nu ii este aplicat nici un layout.

View-uri sistem
--------------

View-urile sistem se refera la view-urile folosite de platforma Yii pentru a
afisa informatii despre erori. De exemplu, atunci cand un utilizator cere un
controller sau un action inexistent, Yii va genera o exceptie, prin care se explica eroarea.
Yii afiseaza exceptia folosind un view sistem specific.

Denumirea view-urilor sistem se face dupa unele reguli.
Numele de genul `errorXXX` se refera la view-uri pentru afisarea [CHttpException]
cu codul de eroare `XXX`. De exemplu, daca este generat [CHttpException] cu codul de eroare
404, atunci va fi afisat view-ul `error404`. 

Yii furnizeaza un set de view-uri sistem implicite. Acestea sunt localizate in
directorul `framework/views`. Pot fi customizate prin crearea unor fisiere view cu acelasi nume
dar in directorul `protected/views/system`.

<div class="revision">$Id: basics.view.txt 416 2008-12-28 05:28:11Z qiang.xue $</div>