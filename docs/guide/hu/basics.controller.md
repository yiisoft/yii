Controller
==========

Egy `controller` a [CController] vagy annak egy leszármazott osztályának a
példánya. A felhasználói kéréstől függően az alkalmazás hozza létre. A controller,
futásakor végrehajtja a kért tevékenységet (action), ami általában beolvassa
a szükséges modellt (model) és megjeleníti a megfelelő nézetet (view). Egy
`action` a legegyszerűbb formájában nem más, mint a controller osztály egy
metódusa, aminek a neve `action`-nel kezdődik.

Minden controller-nek van egy alap action-je (tevékenysége). Amikor a
felhasználói kérés nem határozza meg, hogy melyik action-t kell végrehajtani,
akkor ez az alap action kerül végrehajtásra. Alapbeállításként ez az `index`
nevű action. Ez megváltoztatható a [CController::defaultAction] beállításával.

Alább látható egy controller osztály meghatározásához való minimális kód. Lévén
ez a controller nem ad meg egyetlen action-t sem, erre való hivatkozáskor
kivétel (exception) képződik.

~~~
[php]
class SiteController extends CController
{
}
~~~


Útvonal
-------

A controller-ek és action-ök ID-k által azonosíthatóak. Egy controller ID a
következő formátumú: `path/to/xyz`, ami a következő controller osztály-fájlra
vonatkozik `protected/controllers/path/to/XyzController.php`, ahol is az
`xyz` rész behelyettesítendő valódi nevekkel (pl.: a `post`, a
`protected/controllers/PostController.php`-re vonatkozik). Egy action ID pedig
az adott action metódus neve az `action` előtag nélkül. Például, ha egy
controller osztály tartalmaz egy `actionEdit` nevű metódust, a vonatkozó action
ID-je `edit` lesz.

> Note|Figyelem: Az 1.0.3-as verzió előtt a controller ID-k formátuma
>`path.to.xyz` volt `path/to/xyz` helyett.

A felhasználók útvonalak segítségével hajtanak végre kérést egy adott controller
és action felé. Az útvonal a controller ID és az action ID összefűzéséből jön
létre, perjellel elválasztva. Például, a `post/edit` útvonal a `PostController`
controller-re és annak az `edit` action-jére vonatkozik. Alapbeállításként
a `http://hostname/index.php?r=post/edit` URL intéz kérést ehhez a controllerhez
és action-höz.

>Note|Figyelem: alapértelmezésként az útvonalak kis-/nagybetű érzékenyek. Az
>1.0.1-es verzió óta lehetőség van az útvonalak kis-/nagybetű függetlenné
>tételére a [CUrlManager::caseSensitive] false-ra állításával az alkalmazás
>konfigurációban. Kis-/nagybetű független üzemmódban azonban figyelnünk kell
>arra, hogy pontosan kövessük a konvenciót, miszerint a controller osztályfájlokat
>tartalmazó könyvtáraknak kisbetűsnek kell lenniük, és mind a
>[controller map|CWebApplication::controllerMap] és az [action map|CController::actions]
>kisbetűs kulcsokat használjon.

Az 1.0.3-as verzió óta lehetőségünk van alkalmazásunkban [modulokat](/doc/guide/basics.module)
használni. Egy modulban lévő controller action-höz vezető útvonal a
`moduleID/controllerID/actionID` formában adható meg. További részletek a
[modulokkal foglalkozó részben](/doc/guide/basics.module).


Controller példányosítás
------------------------

Egy controller példány kerül létrehozásra, amikor a [CWebApplication] egy
bejövő kérést kezel. Ha megvan a controller ID-je, az alkalmazás a következő
szabályok szerint határozza meg a controller osztályát valamint az adott
osztályfájl helyét:

   - Ha a [CWebApplication::catchAllRequest] meg van határozva, akkor a controller
e tulajdonság szerint lesz létrehozva, figyelmen kívül hagyva a felhasználó által
megadott controller ID-t. Ez elsősorban akkor használatos, ha az alkalmazást
karbantartási üzemmódba akarjuk állítani, és egy statikus üzenetet megjeleníteni
a látogatóknak.

   - Ha az ID megtalálható a [CWebApplication::controllerMap]-ban, akkor a
megfelelő controller beállítás használatával jön létre a controller példány.

   - Ha az ID formátuma `'path/to/xyz'` szerint van megadva, a controller osztály
`XyzController`-ként, a vonatkozó osztályfájl pedig
`protected/controllers/path/to/XyzController.php`-ként lesz meghatározva. Például,
az `admin/user` controller ID a `UserController` controller osztályra és a
`protected/controllers/admin/UserController.php` osztályfájlra lesz feloldva.
Ha az osztályfájl nem létezik, 404-es [CHttpException] kivétel képződik.

Ha [modulokat](/doc/guide/basics.module) is használunk (elérhetőek az 1.0.3-as verzió óta),
a fenti folyamat egy kicsit másképp zajlik. Lényegében, az alkalmazás ellenőrzi,
hogy az ID egy modulon belül található controller-re vonatkozik-e, és ha igen,
először a modul példány, azt követően pedig a controller példány kerül létrehozásra.


Action
------

Mint azt említettük, lehetőség van metódusként definiált action-ök létrehozására;
ezeknek a neve az `action` szóval kell kezdődjön. Egy kifinomultabb megoldás,
ha egy action osztályt hozunk létre, és a controller-re bízzuk, hogy szükség esetén
példányosítsa. Ezáltal action-jeink könnyedén újrahasznosíthatóak.

Egy új action osztály definiálásához tegyük a következőket:

~~~
[php]
class UpdateAction extends CAction
{
	public function run()
	{
		// az action megvalósításának helye
	}
}
~~~

Ahhoz, hogy a controller tudjon erről az action-ről, felül kell írnunk a controller
osztályunk [actions()|CController::actions] metódusát:

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

A fentiekben az `application.controllers.post.UpdateAction` útvonal álnevet
használjuk, hogy meghatározzuk, az action osztály a
`protected/controllers/post/UpdateAction.php` fájlban található.

Osztály alapú action-öket írva modulárisan szervezhetjük alkalmazásunkat.
Például a következő könyvtárszerkezet is alkalmazható controller-eink kódjának
rendszerezésére:

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

A filter egy kód, ami a controller action végrehajtása előtt és/vagy után
futtatandó. Például egy hozzáférés szabályozó (access control) filter használható,
hogy megbizonyosodjunk arról, a felhasználó jogosult végrehajtani az adott action-t
mielőtt az valójában lefutna; egy teljesítmény filtert pedig használhatunk arra,
hogy lemérjük, mennyi időbe telik egy adott action végrhajtása.

Egy action-nek több filtere is lehet. A filterek abban a sorrendben hajtódnak
végre, ahogyan a filter listában szerepelnek. egy filter megakadályozhatja az action
és a további filterek futtatását.

Egy filtert meghatározhatunk controller osztály metódusként. A metódus neve
`filter`-rel kell kezdődjön. Például a `filterAccessControl` metódus meghatároz
egy `accessControl` nevű filtert. Egy filter metódusnak az alábbi vázzal kell
rendelkeznie:

~~~
[php]
public function filterAccessControl($filterChain)
{
	// hívjuk meg a $filterChain->run() -t a szűrés folytatásához,
	// és/vagy az action végrehajtásához
}
~~~

ahol is `$filterChain` a [CFilterChain] egy példánya, ami a végrhajtandó action
filter listáját testesíti meg. A filterben meghívva a `$filterChain->run()`
metódust tovább léphetünk a többi filter, vagy az action végrehajtására.

Egy filter lehet a [CFilter] osztály vagy annak egy leszármazottjának példánya is.
A következő kód egy új filter osztályt határoz meg:

~~~
[php]
class PerformanceFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// logic being applied before the action is executed
		return true; // false if the action should not be executed
	}

	protected function postFilter($filterChain)
	{
		// logic being applied after the action is executed
	}
}
~~~

Hogy action-jeinken alkalmazzuk a filtereket, felül kell írjuk a
`CController::filters()` metódust. A metúdusnak filter konfigurációs tömbbel
kell visszatérnie. Például:

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
				'unit'=>'second',
			),
		);
	}
}
~~~

A fenti kód két filtert határoz meg: `postOnly` és `PerformaceFilter`.
A `postOnly` filter metódus alapú (a vonatkozó filter metódus a [CController]
osztályban van meghatározva), míg a `PerformanceFilter` filter objektum alapú.
Az `application.filters.PerformanceFilter` útvonal álnév meghatározza, hogy a
vonatkozó filter osztály fájl `protected/filters/PerformanceFilter`. Egy tömböt
használunk a `PerformanceFilter` konfigurálásához, hogy meg tudjuk határozni a
filter objektum tulajdonságainak értékét. Itt a `PerformanceFilter` `unit`
tulajdonságát `'second'`-ként adjuk meg.

A plusz és minusz operátorokat használva meghatározhatjuk, hogy mely action-ökre
vonatkoznak a megadott filter-ek, és melyekre nem. A fenti példában a `postOnly`
filter az `edit` és a `create` action-ökhöz lett hozzárendelve, míg a
`PerformanceFilter` minden action-re alkalmazandó, KIVÉVE az `edit` és a `create`
action-t. Ha sem a plusz, sem a minusz operátor nem szerepel a filter konfigurációban,
akkor az adott filter minden action-re vonatkozik.

<div class="revision">$Id: basics.controller.txt 745 2009-02-25 21:45:42Z qiang.xue $</div>