Folosirea extensiilor
=====================

Folosirea unei extensii implica urmatorii trei pasi:

  1. Descarcarea extensiei din [depozitul de extensii](http://www.yiiframework.com/extensions/) de pe site-ul Yii.
  2. Dezarhivarea extensiei in subdirectorul `extensions/xyz` din
     [directorul de baza al aplicatiei](/doc/guide/basics.application#application-base-directory),
     unde `xyz` este numele extensiei.
  3. Importarea, configurarea si folosirea extensiei.

Fiecare extensie are un nume care o identifica unic fata de celelalte extensii. 
Daca extensia are numele `xyz`, putem folosi oricand alias-ul de cale
`application.extensions.xyz` pentru a localiza directorul de baza care contine toate
fisierele extensiei `xyz`.

Fiecare extensie are cerinte specifice in ce priveste importarea,
configurarea si folosirea. In cele ce urmeaza, facem un sumar al scenariilor obisnuite
de folosire, urmand categorisirea descrisa in sectiunea [Generalitati despre extensii](/doc/guide/extension.overview).

Componenta aplicatie
--------------------

Pentru a folosi o [componenta de aplicatie](/doc/guide/basics.application#application-component),
trebuie sa modificam fisierul de [configurare a aplicatiei](/doc/guide/basics.application#application-configuration)
prin adaugarea unei noi intrari la proprietatea `components`, in felul urmator:

~~~
[php]
return array(
    // 'preload'=>array('xyz',...),
    'components'=>array(
        'xyz'=>array(
            'class'=>'application.extensions.xyz.XyzClass',
            'property1'=>'value1',
            'property2'=>'value2',
        ),
        // alte configuratii de componente
    ),
);
~~~

Apoi, putem accesa componenta oriunde in cod, folosind `Yii::app()->xyz`. Componenta
la fi creata prin abordarea lazy (adica, ea va fi creata atunci cand este accesata prima data).
Putem specifica incarcarea ei in proprietatea `preload`, pentru a fi creata automat o data cu aplicatia.


Widget
------

[Widget-urile](/doc/guide/basics.view#widget) sunt cel mai folosite in [view-uri](/doc/guide/basics.view).
Daca avem o clasa widget `XyzClass` (care apartine extensiei `xyz`), putem s-o folosim
intr-un view in felul urmator:

~~~
[php]
// widget care nu are nevoie de continut body
<?php $this->widget('application.extensions.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

// widget care poate contine un body
<?php $this->beginWidget('application.extensions.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

...continutul body al widget-ului...

<?php $this->endWidget(); ?>
~~~

Action
------

[Action-urile](/doc/guide/basics.controller#action) sunt folosite de un [controller](/doc/guide/basics.controller)
pentru a raspunde diverselor cereri din partea utilizatorilor web. Daca avem o clasa action `XyzClass`
(care apartine extensiei `xyz`), putem s-o folosim prin suprascrierea metodei [CController::actions]
din clasa controller-ului nostru:

~~~
[php]
class TestController extends CController
{
	public function actions()
	{
		return array(
			'xyz'=>array(
				'class'=>'application.extensions.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// alte action-uri
		);
	}
}
~~~

Apoi, action-ul poate fi accesat prin [routa](/doc/guide/basics.controller#route) `test/xyz`.

Filtru
------
[Filtrele](/doc/guide/basics.controller#filter) sunt de asemenea folosite de catre un [controller](/doc/guide/basics.controller).
In principal asigura posibilitatea de a executa un cod inainte si dupa procesarea unei cereri din
partea utilizatorului web atunci cand este tratata de un [action](/doc/guide/basics.controller#action).
Daca avem o clasa filtru `XyzClass`(care apartine extensiei `xyz`), putem s-o folosim
prin suprascrierea metodei [CController::filters] din clasa controller-ului nostru:

~~~
[php]
class TestController extends CController
{
	public function filters()
	{
		return array(
			array(
				'application.extensions.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// alte filtre
		);
	}
}
~~~

In codul de mai sus, putem sa folosim operatorii `+` si `-` in primul element al array-ului
pentru a aplica filtrul doar unor anumite action-uri. Pentru mai multe detalii, putem vedea
sectiunea despre [CController] din documentatie.

Controller
----------
Un [controller](/doc/guide/basics.controller) asigura un set de action-uri care pot fi
cerute de catre utilizatorii web. Pentru afolosi un controller al unei extensii, trebuie sa configuram
proprietatea [CWebApplication::controllerMap] din fisierul care contine
[configurarea aplicatiei](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	'controllerMap'=>array(
		'xyz'=>array(
			'class'=>'application.extensions.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// alte controller-e
	),
);
~~~

Apoi, un action `a` din controller poate fi accesat prin
[ruta](/doc/guide/basics.controller#route) `xyz/a`.

Validator
---------
Un validator este folosit in special intr-o clasa [model](/doc/guide/basics.model)
(una care este derivata fie din [CFormModel] fie din [CActiveRecord]).
Daca avem o clasa validator `XyzClass` (care apartine extensiei `xyz`), putem s-o folosim
prin suprascrierea metodei [CModel::rules] din clasa modelului nostru:

~~~
[php]
class MyModel extends CActiveRecord // sau CFormModel
{
	public function rules()
	{
		return array(
			array(
				'attr1, attr2',
				'application.extensions.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// alte reguli de validare
		);
	}
}
~~~

Comanda de consola
---------------
O [comanda de consola](/doc/guide/topics.console) a unei extensii de obicei imbunatateste
unealta `yiic` prin adaugarea unei noi comenzi. Daca avem o clasa cu o comanda de consola
`XyzClass` (care apartine unei extensii `xyz`), putem s-o folosim prin modificarea fisierului
de configurare al aplicatiei de consola:

~~~
[php]
return array(
	'commandMap'=>array(
		'xyz'=>array(
			'class'=>'application.extensions.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// alte comenzi
	),
);
~~~

Apoi, putem folosi unealta `yiic` care va avea incorporata noua comanda `xyz`.

> Note|Nota: O aplicatie de consola de obicei foloseste un fisier de configurare
care este diferit decat cel folosit de o aplicatie Web. Daca o aplicatie este creata
folosind comanda `yiic webapp`, atunci fisierul de configurare
pentru aplicatia de consola `protected/yiic` este `protected/config/console.php`,
in timp ce fisierul de configurare pentru aplicatia Web este `protected/config/main.php`.

Module
------
Un modul de obicei este format din mai multe fisiere cu clase si este un mix
format din tipurile de extensii de mai sus. De aceea, ar trebui sa urmam instructiunile
corespunzatoare pentru a folosi un modul.

Componenta generica
-------------------
Pentru a folosi o [componenta](/doc/guide/basics.component) generica, mai intai trebuie
sa includem fisierul care contine clasa sa:

~~~
Yii::import('application.extensions.xyz.XyzClass');
~~~

Apoi, putem crea o instanta a clasei, putem sa ii configuram proprietatile
si sa ii apelam metodele. De asemenea, putem sa o derivam pentru a crea noi clase derivate.

<div class="revision">$Id: extension.use.txt 749 2009-02-26 02:11:31Z qiang.xue $</div>