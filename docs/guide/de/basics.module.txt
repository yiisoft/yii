Modul
=====

Ein Modul ist eine in sich geschlossene Einheit, die aus
[Models](/doc/guide/basics.model), [Views](/doc/guide/basics.view),
[Controllern](/doc/guide/basics.controller) und evtl. weiteren Komponenten 
besteht. In vielerlei Hinsicht erinnert ein Modul an eine
[Applikation](/doc/guide/basics.application). Der wesentliche Unterschied
besteht darin, dass ein Modul nicht für sich allein betrieben werden kann und
in eine Applikation eingebettet werden muss. Anwender können auf die Controller
eines Moduls genauso wie auf gewöhnliche Controller einer Anwendung zugreifen.

Module sind in vielen Situationen nützlich. Eine große Anwendung könnte in mehrere
Module unterteilt werden, die alle einzeln entwickelt und gewartet werden
können. Häufig benötigte Features, wie Benutzer- oder
Kommentarverwaltung, könnten ebenfalls als Modul implementiert werden, um sie in späteren
Projekten einfach wiederverwenden zu können.

Erstellen eines Moduls
----------------------

Ein Modul wird in einem Verzeichnis mit der eindeutigen [Modul-ID|CWebModule::id]
als Namen untergebracht. Die Struktur eines solchen Modulverzeichnisses ähnelt dem
[Anwendungsverzeichnis](/doc/guide/basics.application#application-base-directory).
Hier die typische Struktur eines Moduls, in diesem Fall mit dem Namen 
`forum`:

~~~
forum/
   ForumModule.php            die Klassendatei des Moduls
   components/                enthält wiederverwendbare Benutzerkomponenten
      views/                  enthält Viewdateien für Widgets
   controllers/               enthält Klassendateien von Controllern
      DefaultController.php   Die Klassendatei des Standardcontrollers
   extensions/                enthält Erweiterungen von Dritten 
   models/                    enthält Klassendateien von Models
   views/                     enthält View- und Layoutdateien für Controller
      layouts/                enthält Layout-Viewdateien 
      default/                enthält Viewdateien für den Standardcontroller
         index.php            die Datei des Index-Views
~~~

Ein Modul muss eine Modulklasse beinhalten, die von [CWebModule] abgeleitet
wurde. Der Klassenname wird gemäß dem Ausdruck `ucfirst($id).'Module'` gebildet,
wobei `$id` sich auf die Modul-ID (bzw. den Verzeichnisnamen des Moduls)
bezieht. Die Modulklasse dient zum Ablegen von Informationen, die 
im gesamten Modul benötigt werden. Man kann z.B.
[CWebModule::params] oder [CWebModule::components] verwenden, um
Parameter bzw. [Komponenten](/doc/guide/basics.application#application-component)
auf Modulebene bereitzustellen.

> Tip|Tipp: Sie können den Modulgenerator in Gii verwenden, um das Grundgerüst
> eines Moduls zu erstellen.


Verwenden von Modulen
---------------------

Um eine Modul zu verwenden, legen Sie das Modulverzeichnis zunächst im 
`modules`-Ordner des
[Anwendungsverzeichnisses](/doc/guide/basics.application#application-base-directory)
ab. Geben Sie dann die Modul-ID im Konfigurationsparameter
[modules|CWebApplication::modules] an. Um zum Beispiel das obige
`forum`-Modul zu verwenden, ändert man die 
[Konfiguration](/doc/guide/basics.application#application-configuration)
folgendermaßen:

~~~
[php]
return array(
	......
	'modules'=>array('forum',...),
	......
);
~~~

Auch für ein Modul können die Startparameter wie üblich eingestellt werden,
ähnlich zur Konfiguration von
[Anwendungskomponenten](/doc/guide/basics.application#application-component).
Das `forum`-Modul könnte zum Beispiel einen Parameter `postPerPage` (Beiträge
pro Seite) in seiner Modulklasse kennen, der dann folgendermaßen in der
[Konfiguration](/doc/guide/basics.application#application-configuration)
angegeben wird:

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

Über die [module|CController::module]-Eigenschaft
des aktiven Controllers kann dann auf die Modulinstanz zugegriffen werden, um
solche Parameter auszulesen Um zum
Beispiel die obige `postPerPage`-Information kann z.B. so erhalten werden:

~~~
[php]
$postPerPage=Yii::app()->controller->module->postPerPage;
// oder wenn $this sich auf die Controllerinstanz bezieht:
// $postPerPage=$this->module->postPerPage;
~~~

Eine Controller-Action in einem Modul kann über die
[Route](/doc/guide/basics.controller#route) `modulID/controllerID/actionID`
aufgerufen werden. Würde das `forum`-Modul z.B. einen `PostController`
enthalten, würde die [Route](/doc/guide/basics.controller#route) 
`forum/post/create` die Action `create` in diesem Controller aufrufen.
Die entsprechende URL für diese Route wäre dann
`http://www.example.com/index.php?r=forum/post/create`.


> Tip|Tipp: Liegt ein (Modul-)Controller in einem Unterverzeichnis von `controllers`
> kann man trotzdem das beschriebene 
> [Routen](/doc/guide/basics.controller#route)-Format verwenden. Liegt
> `PostController` zum Beispiel in `forum/controllers/admin`, lautet die
> Route zu dessen `create`-Action `forum/admin/post/create`.


Verschachtelte Module
---------------------

Module können in unbegrenzter Tiefe ineinander verschachtelt werden. Das bedeutet, 
dass ein Modul ein weiteres Modul enthalten kann, welches wiederum ein anderes
Modul beherbergt. Wir nennen ersteres *Elternmodul*, letzeres
*Kindmodul*. Kindmodule müssen in der
[modules|CWebModule::modules]-Eigenschaft des jeweiligen Elternmoduls
angebeben werden und zwar genauso wie in der Anwendungskonfiguration
(siehe oben).
Um eine Controller-Action in einem Kindmodul aufzurufen,
wird die Route `elternModulID/kindModulID/controllerID/actionID`
verwendet.

<div class="revision">$Id: basics.module.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>
