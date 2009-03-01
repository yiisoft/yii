Module
======

> Note|Nota: Suportul pentru module este disponibil incepand cu versiunea 1.0.3.

Un modul este o unitate software de sine statatoare care este formata din [modele](/doc/guide/basics.model), 
[view-uri](/doc/guide/basics.view), [controllere](/doc/guide/basics.controller) si alte
componente suportate. In multe privinte, un modul seamana cu o [aplicatie](/doc/guide/basics.application).
Principala diferenta este ca un modul nu poate fi decat in interiorul unei aplicatii. 
Utilizatorii pot accesa controller-ele dintr-un modul la fel cum acceseaza controller-ele normale din aplicatie.

Modulele sunt utile in cateva scenarii. Pentru o aplicatie complexa, putem alege sa o impartim
in mai multe module, fiecare fiind dezvoltat si intretinut separat. Unele feature-uri, folosite in mod obisnuit,
precum gestiunea utilizatorilor, gestiunea comentariilor, pot fi dezvoltate ca module astfel incat ele vor fi
reutilizate usor in proiectele viitoare.


Creare modul
------------

Un modul este organizat ca un director al carui nume este [ID-ul|CWebModule::id] sau unic. 
Strunctura directorului modulului este similara cu cea a [directorului de baza al aplicatiei](/doc/guide/basics.application#application-base-directory).
Structura de directoare tipica a unui modul cu numele `forum` ar arata in felul urmator:

~~~
forum/
   ForumModule.php            fisierul clasei modulului
   components/                contine componente utilizator reutilizabile
      views/                  contine fisierele view ale widget-urilor
   controllers/               contine fisierele claselor controller-elor
      DefaultController.php   fisierul clasei controller-ului implicit
   extensions/                contine extensii third-party
   models/                    contine fisierele cu clasele modelelor
   views/                     contine fisierele de layout si view-urile controller-elor
      layouts/                contine fisierele layout pt view-uri
      default/                contine fisierele view pentru DefaultController
         index.php            view-ul index 
~~~

Un modul trebuie sa aiba o clasa de modul care sa fie derivata din [CWebModule].
Numele clasei este determinat folosind expresia `ucfirst($id).'Module'`,
unde `$id` se refera la ID-ul modulului (acelasi cu numele directorului modulului).
Clasa modulului serveste ca loc central pentru stocarea informatiilor vizibile peste tot
in codul modulului. De exemplu, putem folosi [CWebModule::params] pentru a stoca parametrii
modulului si sa folosim [CWebModule::components] pentru a shera
[componentele aplicatiei](/doc/guide/basics.application#application-component) la nivelul modulului.

> Tip|Sfat: Putem folosi unealta `yiic` pentru a crea un schelet simplu al unui nou modul.
> De exemplu, pentru a crea modulul `forum` de mai sus, putem executa urmatoarele comenzi
intr-o linie de comanda:
>
> ~~~
> % cd WebRoot/testdrive
> % protected/yiic shell
> Yii Interactive Tool v1.0
> Please type 'help' for help. Type 'exit' to quit.
> >> module forum
> ~~~


Folosirea modulului
-------------------

Pentru a folosi un modul, trebuie sa punem directorul modulului in directorul 
`modules` din [directorul de baza al aplicatiei](/doc/guide/basics.application#application-base-directory).
Apoi sa declaram ID-ul modulului in proprietatea [modules|CWebApplication::modules] a aplicatiei.
De exemplu, pentru a folosi modulul `forum` de mai sus, putem folosi urmatoarea 
[configuratie de aplicatie](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	......
	'modules'=>array('forum',...),
	......
);
~~~

Un modul poate sa fie de asemenea configurat cu valori initiale pentru proprietatile sale.
Folosirea este foarte similara cu configurarea [componentelor aplicatiei](/doc/guide/basics.application#application-component).
De exemplu, modulul `forum` poate avea o proprietate cu numele `postPerPage` (in clasa sa) care poate fi configurata
in [configuratia aplicatiei](/doc/guide/basics.application#application-configuration) in felul urmator:

~~~
[php]
return array(
	......
	'modules'=>array(
	    'forum'=>array(
	        'postPerPage'=>20,
	    ),
	),
	......
);
~~~

Instanta modulului poate fi accesata prin proprietatea [module|CController::module] a controller-ului activ in acest moment.
Prin instanta modulului, putem accesa apoi informatiile care sunt sherate la nivel de modul.
De exemplu, pentru a accesa informatia `postPerPage` de mai sus, putem folosi urmatoarea expresie:

~~~
[php]
$postPerPage=Yii::app()->controller->module->postPerPage;
// sau urmatoarea daca $this se refera la instanta controller-ului
// $postPerPage=$this->module->postPerPage;
~~~

Un action de controller din interiorul unui modul poate fi accesat prin
[ruta](/doc/guide/basics.controller#route) `moduleID/controllerID/actionID`.
De exemplu, presupunand ca modulul `forum` de mai sus are un controller cu numele `PostController`,
putem folosi [ruta](/doc/guide/basics.controller#route) `forum/post/create` pentru a ne referi la action-ul `create` din acest controller.
URL-ul corespunzator pentru aceasta ruta va fi `http://www.example.com/index.php?r=forum/post/create`.

> Tip|Sfat: Daca un controller este intr-un subdirector din `controllers`, inca
> putem folosi formatul [rutei](/doc/guide/basics.controller#route) de mai sus. De exemplu,
> presupunand ca `PostController` este sub `forum/controllers/admin`, putem sa apelam 
> action-ul `create` folosind ruta `forum/admin/post/create`.


Module imbricate
----------------

Modulele pot fi imbricate. Adica, un modul poate contine alt modul. Le puntem denumi
*modulul parinte* si *modulul copil*. Modulele copil trebuie sa fie pus in directorul `modules`
al modulului parinte. Pentru a accesa un action al unui controller dintr-un modul copil,
trebuie sa folosim ruta `parentModuleID/childModuleID/controllerID/actionID`.

<div class="revision">$Id: basics.module.txt 745 2009-02-25 21:45:42Z qiang.xue $</div>