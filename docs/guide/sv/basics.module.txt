Moduler
=======

En modul är en komplett och oberoende mjukvaruenhet som består av [modeller](/doc/guide/basics.model), 
[vyer](/doc/guide/basics.view), [kontrollrar](/doc/guide/basics.controller) samt andra stödkomponenter. 
En modul motsvarar ur många aspekter en [applikation](/doc/guide/basics.application). Den huvudsakliga 
skillnaden är att en modul inte kan sättas ut på egen hand, den måste härbärgeras i en applikation. 
Användare kan komma åt en kontroller i en modul så som de gör med en vanlig kontroller på applikationsnivå.

Moduler är användbara i ett flertal scenarier. En storskalig applikation kan delas upp i flera moduler, 
som var och en utvecklas och underhålls separat. Vissa vanligen använda finesser, som administrering av 
användare eller kommentarhantering, kan utvecklas i form av moduler vilka utan svårighet kan återanvändas 
i kommande projekt.


Skapa modul
-----------

En modul organiseras i en katalog vars namn bildar dess unika [ID|CWebModule::id]. Modulkatalogens struktur 
liknar strukturen hos [applikationens rotkatalog](/doc/guide/basics.application#application-base-directory). 
Nedan visas den typiska katalogstrukturen för en modul benämnd `forum`:

~~~
forum/
   ForumModule.php            modulens klassfil
   components/                innehåller återanvändbara komponenter
      views/                  innehåller vyfiler för widget
   controllers/               innehåller kontrollerklassfiler
      DefaultController.php   standardkontrollerns klassfil
   extensions/                innehåller tredjepartstillägg
   models/                    innehåller modellklassfiler
   views/                     innehåller kontrollervyfiler och layoutfiler
      layouts/                innehåller layoutvyfiler
      default/                innehåller vyfiler för DefaultController
         index.php            fil som innehåller indexvy
~~~

En modul måste ha en modulklass som ärver från och utvidgar [CWebModule]. Klassnamnet bildas med hjälp av
uttrycket `ucfirst($id).'Module'`, där `$id` refererar till modulens ID (läs: modulens katalognamn). 
Modulklassen utgör den centrala platsen för lagring av information som skall delas av modulens kod. 
Vi kan till exempel använda [CWebModule::params] för lagring av modulparametrar, samt använda 
[CWebModule::components] för att dela [applikationskomponenter](/doc/guide/basics.application#application-component) 
på modulnivå.

> Tip|Tips: Modulgenereringen i Gii kan användas för att skapa det grundläggande skelettet till en ny modul.


Använda modul
-------------

För att använda en modul, placera först modulkatalogen under katalogen `modules` i 
[applikationens rotkatalog](/doc/guide/basics.application#application-base-directory). 
Deklarera sedan modulens ID i applikationens property [modules|CWebApplication::modules]. 
För att till exempel använda ovanstående `forum`-modul, kan följande 
[applikationskonfiguration](/doc/guide/basics.application#application-configuration) 
användas:

~~~
[php]
return array(
	......
	'modules'=>array('forum',...),
	......
);
~~~

En modul kan även konfigureras med initiala propertyvärden. Tillvägagångssättet är mycket snarlikt 
konfigurering av [applikationskomponenter](/doc/guide/basics.application#application-component). 
Exempel: `forum`-modulen kan i sin modulklass ha en property `postPerPage`, vilken kan konfigureras i 
[applikationskonfigurationen](/doc/guide/basics.application#application-configuration) på följande sätt:

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

Modulinstansen kan kommas åt via propertyn [module|CController::module] i den för tillfället aktiva kontrollern. 
Genom modulinstansen kan vi sedan nå information som delas på modulnivå. Exempelvis kan följande uttryck användas 
för att komma åt ovanstående `postPerPage`-information:

~~~
[php]
$postPerPage=Yii::app()->controller->module->postPerPage;
// or the following if $this refers to the controller instance
// $postPerPage=$this->module->postPerPage;
~~~

En kontrolleråtgärd i en modul kan nås via en [route](/doc/guide/basics.controller#route) på formatet
`moduleID/controllerID/actionID`. Exempel: om vi antar att ovanstående `forum`-modul har en 
kontroller `PostController`, kan vi som [route](/doc/guide/basics.controller#route) använda 
`forum/post/create` för att referera till åtgärden `create` i denna kontroller. Motsvarande 
URL för denna route blir då `http://www.example.com/index.php?r=forum/post/create`.

> Tip|Tips: Om en kontroller finns i en underkatalog till `controllers`, kan vi fortfarande använda 
ovanstående [route](/doc/guide/basics.controller#route) format. Till exempel om `PostController` 
är placerad under `forum/controllers/admin` kan vi referera till åtgärden `create` genom att 
använda `forum/admin/post/create`.


Nästlad modul
-------------

Moduler kan vara nästlade i ett obegränsat antal nivåer. Det vill säga, en modul kan 
innehålla en annan modul som i sin tur kan innehålla ännu en modul. 
Vi kallar den förra *föräldramodul* (parent module), den senare *barnmodul* (child module). 
En barnmodul måste deklareras i dess föräldramoduls property [modules|CWebModule::modules], 
vilket motsvarar deklaration av moduler i applikationskonfigurationen, åskådliggjort ovan.

För att komma åt en kontrolleråtgärd i en barnmodul, använder vi följande route 
`parentModuleID/childModuleID/controllerID/actionID`.


<div class="revision">$Id: basics.module.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>