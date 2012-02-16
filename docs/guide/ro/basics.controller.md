Controller
==========

Un `controller` este o instanta a clasei [CController] sau a unei clase derivate. Este
creat de application atunci cand o cerere client are nevoie. Atunci cand ruleaza,
un controller executa un action cerut de client. De obicei, action apeleaza
modelele necesare si va genera un view corespunzator (rezultatul vazut de client). 
Un `action`, in cea mai simpla forma, este doar o metoda a clasei controllerului
al carei nume incepe cu `action`.

Orice controller are un default action (actiune implicita). Atunci cand cererea client
nu specifica ce action va fi apelat, default action va fi apelat. Implicit,
default action are numele `index`. Dar poate fi schimbat prin setarea [CController::defaultAction].

Mai jos este codul minim necesar pentru un controller. Din moment ce acest controller
nu defineste nici un action, orice cerere pentru acest controller va genera o exceptie.

~~~
[php]
class SiteController extends CController
{
}
~~~


Rute
----

Controller-ele si action-urile sunt identificate prin ID-uri. ID-ul controller are formatul
`cale/catre/xyz` care corespunde fisierului fizic al controller-ului
`protected/controllers/cale/catre/XyzController.php`, where the token `xyz`
should be replaced by actual names (ex. `post` corespunde cu
`protected/controllers/PostController.php`). ID-ul action este numele metodei action
fara prefixul `action`. De exemplu, daca o clasa controller contine o metoda cu numele
`actionEdit`, atunci ID-ul este `edit`.

> Note|Nota: Inainte de versiunea 1.0.3, ID-ul controller-ului era in formatul `cale.catre.xyz`
in loc de `cale/catre/xyz`.

Utilizatorii cer un anumit controller si un anumit action prin intermediul unui route (ruta).
Route este format prin concatenarea unui ID controller si al unui ID action separate prin slash (/).
De exemplu, ruta `post/edit` se refera la controllerul `PostController` si la action-ul `edit`.
Si implicit, URL-ul `http://hostname/index.php?r=post/edit` va cere acest controller si acest action.

>Note|Nota: Implicit, rutele sunt case-sensitive. De la versiunea 1.0.1, este posibila crearea de rute
>case-insensitive prin setarea [CUrlManager::caseSensitive] cu valoarea false in configuratia aplicatiei.
>In modul case-insensitive, trebuie sa ne asiguram ca urmam conventia ca directoarele care contin
>fisierele cu clasele controller-ului sunt in lower case, si ca atat [controller map|CWebApplication::controllerMap]
>cat si [action map|CController::actions] folosesc key-uri in lower case.

Incepand cu versiunea 1.0.3, o aplicatie poate contine [module](/doc/guide/basics.module). Ruta pentru un action
dintr-un controller din interiorul unui modul are formatul `moduleID/controllerID/actionID`. Pentru mai multe detalii,
trebuie vazuta [sectiunea despre module](/doc/guide/basics.module).

Instantierea Controller-ului
----------------------------

Cand [CWebApplication] analizeaza o cerere de la client, atunci este creata o instanta a unui controller.
Se primeste, prin ruta, ID-ul controller-ului, iar aplicatia va folosi urmatoarele reguli pentru a
determina ce clasa controller este si unde este localizat fisierul fizic al clasei.

   - Daca este specificat [CWebApplication::catchAllRequest], va fi creat un controller pe baza acestei
proprietati, iar ID-ul de controller primit de la client va fi ignorat. Aceasta este situatia cand dorim sa
aducem aplicatia in modul de mentenanta, offline sau invizibila in Web in spatele unei pagini statice. 

   - Daca ID-ul este gasit in [CWebApplication::controllerMap], configuratia controller-ului care este precizata
acolo va fi folosita pentru a crea instanta controller-ului. 

   - Daca ID-ul este in formatul 'cale/catre/xyz'`, numele clasei controller-ului se presupune ca este
`XyzController` si fisierul fizic al clasei este `protected/controllers/cale/catre/XyzController.php`. De exemplu,
un ID de controller `admin.user` ar conduce la clasa `UserController` si la fisierul fizic
`protected/controllers/admin/UserController.php`. Daca fisierul fizic al clasei nu exista, atunci se genereaza [CHttpException] 404.

In cazul in care [modulele](/doc/guide/basics.module) sunt folosite 
(posibil incepand cu versiunea 1.0.3), procesul de mai sus este un pic
diferit. In particular, aplicatia va verifica daca ID-ul se refera la
un controller din interiorul unui modul, si daca da, atunci va fi creata intai
instanta modulului respectiv, iar apoi va fi creata instanta controller-ului.

Action
------

Dupa cum am mentionat anterior, un action poate fi definit ca metoda al carei nume incepe cu cuvantul `action`. O modalitate mai
avansata de a defini o clasa action este prin a cere controller-ului sa instantieze clasa action atunci cand este ceruta.
Aceasta permite reutilizarea usoara in alte proiecte a clasei action, clasa action fiind independenta in felul acesta
de aplicatia curenta.

Pentru a defini o noua clasa action, facem urmatoarele:

~~~
[php]
class UpdateAction extends CAction
{
	public function run()
	{
		// aici intra logica action
	}
}
~~~

Pentru ca acest action sa fie vizibil de catre controller, suprascriem
metoda [actions()|CController::actions] din clasa controller-ului:

~~~
[php]
class PostController extends CController
{
	public function actions()
	{
		return array(
			'edit'=>'application.controllers.post.UpdateAction',
		);
	}
}
~~~

Mai sus, am folosit alias-ul `application.controllers.post.UpdateAction` pentru a specifica
faptul ca fisierul fizic al clasei action este `protected/controllers/post/UpdateAction.php`.

Daca concepem action-urile fiind clase, putem sa organizam aplicatia modular.
De exemplu, urmatoarea structura de directoare poate fi folosita pentru a organiza codul pentru controllere:

~~~
protected/
    controllers/
        PostController.php
        UserController.php
        post/
            CreateAction.php
            ReadAction.php
            UpdateAction.php
        user/
            CreateAction.php
            ListAction.php
            ProfileAction.php
            UpdateAction.php
~~~

Filter
------

Filter (filtru) este o bucata de cod care poate fi executata inainte si/sau
dupa ce un action al unui controller a fost executat. De exemplu, un filtru de control de acces
poate fi executat, pentru a se asigura ca utilizatorul este autentificat inainte de a executa
action-ul cerut; sau un filtru de performanta poate fi folosit inainte si dupa executia unui action, pentru
a masura timpul de executie al action-ului.

Un action poate avea mai multe filtre. Filtrele sunt executate in ordinea in care apar in lista de filtre.
Un filtru poate sa interzica executia celorlalte filtre ramase si a action-ului. 

Un filtru poate fi definit ca metoda in clasa controller-ului. Numele metodei trebuie sa
inceapa cu `filter`. De exemplu, daca exista metoda `filterAccessControl` atunci se defineste un filtru
cu numele `accessControl`. Metoda filtru trebuie sa fie in forma urmatoare: 

~~~
[php]
public function filterAccessControl($filterChain)
{
	...
	// se apeleaza $filterChain->run() pentru a continua filtrarea si executia action-ului
}
~~~

`$filterChain` este o instanta a clasei [CFilterChain] si reprezinta o lista de filtre asociate
cu action-ul cerut. In interiorul filtrului, putem apela `$filterChain->run()` pentru a continua
filtrarea si executia action-ului.

Dar un filtru poate sa fie si o instanta a clasei [CFilter] sau a unei clase derivate.
Urmatorul cod defineste o clasa noua de filtru:

~~~
[php]
class PerformanceFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// cod executat inainte de executia action-ului
		return true; // daca action-ul nu trebuie executat trebuie returnat false
	}

	protected function postFilter($filterChain)
	{
		// cod de executat dupa ce action-ul este executat
	}
}
~~~

Pentru a aplica filtre action-urilor, trebuie sa suprascriem metoda `CController::filters()`.
Metoda ar trebui sa returneze un array cu configuratia filtrului. De exemplu:

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'postOnly + edit, create',
			array(
				'application.filters.PerformanceFilter - edit, create',
				'unit'=>'microsecunde',
			),
		);
	}
}
~~~

Codul de mai sus specifica doua filtre: `postOnly` si `PerformanceFilter`.
Filtrul `postOnly` este definit ca metoda (metoda filtru corspunzatoare este
definita deja in [CController]); filtrul `PerformanceFilter` este definit printr-o clasa.
Aliasul `application.filters.PerformanceFilter` ne spune calea unde gasim fisierul fizic al clasei:
`protected/filters/PerformanceFilter`. Filtrul `PerformanceFilter` are nevoie de un array pentru a isi
initializa valorile proprietatilor. Aici, proprietatea `unit` din filtrul `PerformanceFilter`
va fi initializata cu `'microsecunde'`.

Folosind operatorii plus si minus, putem specifica la ce action-uri ar trebui aplicat (sau nu) un filtru.
In codul de mai sus, filtrul `postOnly` va fi aplicat action-urilor 
`edit` si `create`, in timp ce filtrul `PerformanceFilter` ar trebui aplicat la toate action-urile
CU EXCEPTIA action-urilor `edit` si `create`. Daca nu apare nici plus nici minus in configuratia filtrului,
atunci filtrul va fi aplicat tuturor action-urilor.
<div class="revision">$Id: basics.controller.txt 745 2009-02-25 21:45:42Z qiang.xue $</div>