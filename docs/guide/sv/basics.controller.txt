Controller
==========

En `controller` är en instans av [CController] eller av en klass som 
utökar [CController]. Den skapas av applikationsobjektet till följd 
av en request från användare. När en kontroller körs utförs den begärda 
åtgärden (action), vilken vanligen laddar in erforderliga modeller (model) 
samt renderar en relevant vy (view). En åtgärd är, i sin enklaste form, 
en vanlig metod i kontrollerklassen vars namn börjar med `action`

En kontroller har en standardåtgärd. Denna körs om en request från 
användare inte specificerar vilken åtgärd som skall köras. Normalt har 
standardåtgärden namnet `index`. Detta kan ändras medelst den publikt 
tillgängliga instansvariablen [CController::defaultAction].

Följande kod definierar en `site`-controller, en `index`-åtgärd (standardåtgärden), 
samt en `contact`-åtgärd:

~~~
[php]
class SiteController extends CController
{
	public function actionIndex()
	{
		// ...
	}

	public function actionContact()
	{
		// ...
	}
}
~~~


Route
-----

Kontroller och åtgärd identifieras genom ID:n. Ett kontroller-ID har formatet 
`path/to/xyz` motsvarande kontrollerklassfilen 
`protected/controllers/path/to/XyzController.php`, där ledet `xyz` skall 
ersättas med aktuella namn (exempelvis motsvarar `post` 
`protected/controllers/PostController.php`). Action-ID är en åtgärds metodnamn 
minus dess `action`-prefix. Exempelvis, om en kontrollerklass innehåller en 
metod `actionEdit` så är ID för motsvarande åtgärd `edit`.

Användare begär körning av en viss kontroller och åtgärd genom att ange en 
route. En route består av controller-ID samt action-ID, separerat av ett 
snedstreck. Exempel: `post/edit` refererar till `PostController` och 
dess `edit`-åtgärd. Som standard refererar URL:en 
`http://hostname/index.php?r=post/edit` till post-kontrollern och dess edit-åtgärd.

> Note|Märk: Som standard är route skiftlägesberoende. Route kan göras okänslig 
för skiftläge genom att inställningen [CUrlManager::caseSensitive] sätts till false i 
applikationens konfiguration. I detta läge, se till att följa konventionen att 
kataloger som innehåller kontrollerklassfiler anges med gemener samt att både 
[controller map|CWebApplication::controllerMap] och [action map|CController::actions] 
använder gemener i sina nyckelvärden.

En applikation kan innehålla [moduler](/doc/guide/basics.module). 
En kontrolleråtgärd inuti en modul har en route på följande format 
`moduleID/controllerID/actionID`. För fler detaljer hänvisas till 
[avsnittet om moduler](/doc/guide/basics.module).

Instansiering av Controller
---------------------------

En kontrollerinstans skapas när [CWebApplication] behandlar en inkommande 
request. Givet ett kontroller-ID använder sig Application av följande regler för 
att bestämma kontrollerklass samt var dess klassfil är placerad.

   - Om [CWebApplication::catchAllRequest] har specificerats, skapas en kontroller baserat på denna property och användarspecificerat kontroller-ID ignoreras. Detta används i första hand till att sätta applikationen i underhållsläge och visa en statisk informationssida.

   - Om begärt ID kan hittas i [CWebApplication::controllerMap], används motsvarande kontrollerkonfiguration för att skapa kontrollerinstansen.

   - Om begärt ID har formatet `'path/to/xyz'`, förmodas kontrollerklassen vara `XyzController` och motsvarande klassfil `protected/controllers/path/to/XyzController.php`. Exempelvis kontroller-ID `admin/user` mappas till kontrollerklassen `UserController` med klassfilen `protected/controllers/admin/UserController.php`. Om klassfilen inte kan hittas ges en 404 [CHttpException].

Om [moduler](/doc/guide/basics.module) används, är ovanstående process aningen annorlunda. 
Mer detaljerat, applikationen kontrollerar om ID:t refererar till en kontroller 
inuti en modul och om så är fallet skapas modulen först, därefter kontrollerinstansen.


Action
------

Som tidigare nämnts kan en åtgärd definieras som en metod vars namn börjar med 
`action`. Ett mer avancerat sätt att definiera en åtgärd är att definiera en 
åtgärdsklass och be kontrollern instansiera denna på begäran. Detta ger bättre 
återanvändningsbarhet då åtgärder kan återanvändas.

Definiera en ny åtgärdsklass så här:

~~~
[php]
class UpdateAction extends CAction
{
	public function run()
	{
		// place the action logic here
	}
}
~~~

För att kontrollern skall bli varse denna åtgärd, åsidosätter vi den ärvda 
[actions()|CController::actions]-metoden i kontrollerklassen:

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

Ovan används sökvägsalias `application.controllers.post.UpdateAction` för att 
specificera att åtgärdsklassens fil är `protected/controllers/post/UpdateAction.php`.

Med hjälp av klassbaserade åtgärder kan en applikation organiseras modulärt. 
Till exempel kan följande katalogstruktur användas för att organisera kontrollrar och
åtgärdsklasser:

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

### Knyta parametrar till Action

Med start i version 1.1.4 har Yii försetts med stöd för automatisk koppling av 
parametrar till kontrolleråtgärder. Det innebär att en kontrollers åtgärdsmetod 
kan definiera namngivna parametrar vars värde automatiskt kommer att hämtas från 
`$_GET` av Yii.

För att åskådliggöra denna finess, antag att vi behöver skriva en `create`-åtgärd 
för `PostController`. Åtgärden behöver två parametrar:

* `category`: ett heltal som indikerar det kategori-ID under vilket den nya postningen skall skapas
* `language`: en sträng som indikerar vilken språkkod den nya postningen skall höra till.

Vi kan komma fram till följande trista kod för ändamålet att hämta in de erforderliga 
parametervärdena från `$_GET`:

~~~
[php]
class PostController extends CController
{
	public function actionCreate()
	{
		if(isset($_GET['category']))
			$category=(int)$_GET['category'];
		else
			throw new CHttpException(404,'invalid request');

		if(isset($_GET['language']))
			$language=$_GET['language'];
		else
			$language='en';

		// ... fun code starts here ...
	}
}
~~~

Med användning av finessen åtgärdsparametrar kan vi lösa uppgiften mer bekvämt:

~~~
[php]
class PostController extends CController
{
	public function actionCreate($category, $language='en')
	{
		// ... fun code starts here ...
	}
}
~~~

Märk att vi lägger till två parametrar till åtgärdsmetoden `actionCreate`.
Namnen på dessa parametrar måste exakt stämma överens med de vi förväntar oss 
från `$_GET`. Parametern `$language` erhåller standardvärdet `en` i händelse av att 
användaren inte tillhandahåller en sådan parameter i sin request. Eftersom `$category` 
inte är försett med ett standardvärde, kommer en [CHttpException] att genereras om användaren inte 
tillhandahåller parametern `category` i `$_GET`.

Med start från version 1.1.5, kan Yii även detektera åtgärdsparametrar av arraytyp.
Detta sker med hjälp av PHP typledtrådar vars syntax ser ut som följer:

~~~
[php]
class PostController extends CController
{
	public function actionCreate(array $categories)
	{
		// Yii ser till att $categories är en array
	}
}
~~~

Det vill säga, vi inflikar nyckelordet `array` före `$categories` i metodens parameterlista. 
Genom detta kommer `$_GET['categories']`, om denna är en enkel sträng, 
att konverteras till en array med strängen som innehåll.

> Note|Märk: Om en parameter deklareras utan typledtråden `array`, innebär detta att parametern
> mäste vara skalär (dvs inte en array). Om, i detta fall, en arrayparameter förmedlas via
> `$_GET` leder det till en HTTP-exception.

Med start från version 1.1.7, fungerar automatisk koppling av parametrar även för klassbaserade 
åtgärder. När metoden `run()` i en åtgärdsklass deklareras med parametrar, kommer dessa att erhålla 
värden från motsvarande namngivna request-parametrar. Till exempel,

~~~
[php]
class UpdateAction extends CAction
{
	public function run($id)
	{
		// $id erhåller värde från $_GET['id']
	}
}
~~~


Filter
------

Filter är ett stycke kod som konfigureras att exekvera före och/eller efter 
exekvering av en kontrolleråtgärd. Exempel: ett filter för 
tillträdeskontroll kan köras för att säkerställa att användaren är autenticerad 
innan en begärd åtgärd utförs; ett prestandafilter kan användas för att 
mäta tidåtgång vid exekvering av en åtgärd.

En åtgärd kan ha flera filter. Filtren körs i den ordning de förekommer i 
filterdeklarationen. Ett filter kan förhindra exekvering av åtgärden såväl som 
återstående ej exekverade filter.

Ett filter kan definieras som en metod i kontrollerklassen. Metodens namn måste 
börja med `filter`. Exempelvis en metod `filterAccessControl` definierar 
ett filter med namnet `accessControl`. Filtermetoden måste ha korrekt signatur:

~~~
[php]
public function filterAccessControl($filterChain)
{
	// call $filterChain->run() to continue filtering and action execution
}
~~~

där `$filterChain` är en instans av [CFilterChain] vilken representerar begärd 
åtgärds filterlista. I en filtermetod kan vi anropa `$filterChain->run()` för att 
fortsätta med exekvering av övriga filter samt åtgärd.

Ett filter kan också vara en instans av [CFilter] eller en nedärvd klass. 
Följande kod definierar en ny filterklass:

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

Åtgärder förses med filter genom att den ärvda metoden `CController::filters()` 
omdefinieras . Metoden skall returnera en array av filterkonfigurationer. 
Exempel:

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

Ovanstående kod specificerar två filter: `postOnly` och `PerformanceFilter`. 
`postOnly`-filtret är metodbaserat (den motsvarande filtermetoden är redan 
definierad i [CController]); medan däremot `PerformanceFilter`-filtret är 
objektbaserat. Sökvägsalias `application.filters.PerformanceFilter` specificerar 
att filtrets klassfil är `protected/filters/PerformanceFilter`. En array används 
för att konfigurera `PerformanceFilter` så att propertyvärdena för 
filterobjektet kan initialiseras. I exemplet initialiseras propertyn `unit` i 
`PerformanceFilter` till `'second'`.

Genom användning av plus- och minusoperatorerna kan vi specificera vilka 
åtgärder filtret skall eller inte skall tillämpas på. I exemplet ovan tillämpas 
filtret `postOnly` på åtgärderna `edit` och `create`, medan filtret `PerformanceFilter` 
tillämpas på alla åtgärder UTOM `edit` och `create`. Om varken plus eller minus 
förekommer i filterkonfigurationen tillämpas filtret på alla åtgärder.

<div class="revision">$Id: basics.controller.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>