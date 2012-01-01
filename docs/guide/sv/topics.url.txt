URL-hantering
=============

Komplett URL-hantering för en webbapplikation involverar två aspekter. För det 
första, när en användar-request kommer in i form av en URL, behöver 
applikationen avkoda den till begripliga parametrar. För det andra behöver 
applikationen erbjuda ett sätt att skapa URL:er på ett sådant vis att de är 
begripliga för applikationen. En Yii-applikation åstadkommer detta med hjälp av 
[CUrlManager].

Skapa URL:er
------------

Även om URL:er kan hårdkodas i kontrollervyer, är det ofta mer flexibelt att 
skapa dem dynamiskt:

~~~
[php]
$url=$this->createUrl($route,$params);
~~~

där `$this` refererar till kontrollerinstansen; `$route` anger 
[route](/doc/guide/basics.controller#route) för önskad request; `$params` är en 
lista med `GET`-parametrar som skall läggas till URL:en.

Som standard är URL:er som skapats med hjälp av 
[createUrl|CController::createUrl] av det så kallade `get`-formatet. Till 
exempel, givet `$route='post/read'` och `$params=array('id'=>100)`, skulle vi 
erhålla följande URL:

~~~
/index.php?r=post/read&id=100
~~~

där parametrarna återfinns i frågesträngen sammanslagna till en lista av 
`Namn=Värde`-par separerade av och-tecken (&), och där `r`-parametern 
specificerar request-[route](/doc/guide/basics.controller#route). Detta 
URL-format är inte speciellt användarvänligt eftersom det erfordrar ett antal 
ordfrämmande tecken.

Ovanstående URL kan fås att se renare ut samt mer självförklarande genom 
användning av det så kallade `path`-formatet, vilket eliminerar frågesträngen 
och dessutom placerar GET-parametrarna i den del av URL:en som innehåller path-info:

~~~
/index.php/post/read/id/100
~~~

För att byta URL:format konfigurerar vi applikationskomponenten 
[urlManager|CWebApplication::urlManager] så att 
[createUrl|CController::createUrl] automatiskt kan byta till det nya formatet 
och så att applikationen korrekt kan begripa de nya URL:erna:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'urlManager'=>array(
			'urlFormat'=>'path',
		),
	),
);
~~~

Lägg märke till att vi inte behöver klass för komponenten 
[urlManager|CWebApplication::urlManager], den är fördeklarerad som [CUrlManager] 
i [CWebApplication].

> Tip|Tips: URL:en som genereras av metoden [createUrl|CController::createUrl] 
är en relativ sådan. För att få en absolut URL kan vi infoga ett prefix med 
hjälp av `Yii::app()->request->hostInfo`, alternativt anropa 
[createAbsoluteUrl|CController::createAbsoluteUrl].

Användarvänliga URL:er
----------------------

När `path` används dom URL-format kan vi specificera vissa URL-regler för att 
göra våra URL:er ännu mer användarvänliga. Vi kan till exempel generera en så 
kort URL som `/post/100`, i stället för det betydligt längre 
`/index.php/post/read/id/100`. URL-regler används av [CUrlManager] både för att 
skapa URL:er och för URL-parsning.

För att ange URL-regler behöver vi konfigurera propertyn 
[rules|CUrlManager::rules]  i applikationskomponenten 
[urlManager|CWebApplication::urlManager]:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'pattern1'=>'route1',
				'pattern2'=>'route2',
				'pattern3'=>'route3',
			),
		),
	),
);
~~~

Reglerna specificeras i form av en array av mönster-routepar, vart och ett 
motsvarande en enstaka regel. Mönstret i en regel utgörs av en sträng som 
används till att matcha pathinfo-delen av URL:erna. Routedelen av en regel 
skall referera till en giltig [kontrollerroute](/doc/guide/basics.controller#route).

Utöver ovanstående mönster-routeformat, kan en regel även specificeras med 
anpassade alternativ, enligt följande:

~~~
[php]
'pattern1'=>array('route1', 'urlSuffix'=>'.xml', 'caseSensitive'=>false)
~~~

Med start från version 1.1.7, kan även följande format användas (dvs mönstret specificeras som 
ett element i en vektor), vilket medger att man specificerar ett flertal regler med hjälp av 
samma mönster:

~~~
[php]
array('route1', 'pattern'=>'pattern1', 'urlSuffix'=>'.xml', 'caseSensitive'=>false)
~~~

I ovanstående exempel innehåller vektorn en lista med extra alternativ till regeln. Möjliga 
alternativ är:

   - [pattern|CUrlRule::pattern]: mönstret som skall användaas för att matcha och skapa URL:er. 
   Detta alternativ har varit tillgängligt sedan version 1.1.7.

   - [urlSuffix|CUrlRule::urlSuffix]: URL suffix som skall användas specifikt för denna regel.
   Standardvärde är null, vilket innebär värdet av [CUrlManager::urlSuffix].

   - [caseSensitive|CUrlRule::caseSensitive]: huruvida denna regel är skiftlägeskänslig.
   Standardvärde är null, vilket innebär värdet av [CUrlManager::caseSensitive].

   - [defaultParams|CUrlRule::defaultParams]: underförstådda GET-parametrar (namn=>värde) som 
   denna regel tillhandahåller. När denna regel används för att behandla en inkommen request, 
   läggs värdena som deklareras i denna property till i $_GET.

   - [matchValue|CUrlRule::matchValue]: huruvida GET-parametervärdena skall matcha motsvarande 
   delmönster i regeln när en URL skapas. Standardvärde är null, vilket innebär värdet
   av [CUrlManager::matchValue]. Om denna property är false, innebär det att en regel kommer att
   användas när en URL skapas, om dess route och parameternamn matchar de som givits. Om denna property
   är satt till true, måste de givna parametervärdena även matcha motsvarande (parameter-)delmönster.
   Lägg märke till att prestanda påverkas negativt om detta alternativ är satt till true.

   - [verb|CUrlRule::verb]: HTTP-verbet (t.ex. `GET`, `POST`, `DELETE`) som denna regel måste matcha 
   för att kunna användas vid analys av aktuell request. Standardvärde är null, med innebörd att regeln 
   kan matcha varje HTTP-verb. Om en regel kan matcha multipla verb, måste de separeras med kommatecken. 
   När en regel inte matchar (de) specificerade verb(en), utelämnas den vid analys av request. Det här 
   alternativet används enbart vid analys av request. Det tillhandahålls huvudsakligen för att stödja 
   RESTful URL:er. Detta alternativ har varit tillgängligt sedan  1.1.7.

   - [parsingOnly|CUrlRule::parsingOnly]: huruvida regeln används enbart för analys av request. 
   Standardvärde är false, med innebörden att en regel används både vid analys respektive skapande av URL. 
   Detta alternativ har varit tillgängligt sedan  1.1.7.


Användning av namngivna parametrar
----------------------------------

En regel kan associeras med några GET-parametrar. Dessa GET-parametrar uppträder 
i regelns mönster som speciella symboler på följande format:

~~~
<ParamName:ParamPattern>
~~~

där `ParamName` specificerar namnet på en GET-parameter och det frivilliga 
`ParamPattern` specificerar det reguljära uttrycket som skall användas för att 
matcha GET-parameterns värde. Om `ParamPattern` utelämnas, innebär detta att 
parametern skall matcha alla tecken med undantag för snedstreck `/`.
När en URL skapas kommer dessa parametersymboler att ersättas med motsvarande 
parametervärden; vid parsning av en URL, kommer de motsvarande GET-parametrarna 
att erhålla värden från resultatet av parsningen.

Några exempel följer nu för att belysa hur URL-regler fungerar. 
Regeluppsättningen antas innehålla tre regler:

~~~
[php]
array(
	'posts'=>'post/list',
	'post/<id:\d+>'=>'post/read',
	'post/<year:\d{4}>/<title>'=>'post/read',
)
~~~

   - Anropet `$this->createUrl('post/list')` genererar `/index.php/posts`. Den 
   första regeln appliceras.

   - Anropet `$this->createUrl('post/read',array('id'=>100))` genererar 
   `/index.php/post/100`. Den andra regeln appliceras.

   - Anropet `$this->createUrl('post/read',array('year'=>2008,'title'=>'a sample 
   post'))` genererar `/index.php/post/2008/a%20sample%20post`. Den tredje 
   regeln appliceras.

   - Anropet `$this->createUrl('post/read')` genererar `/index.php/post/read`. 
   Ingen av reglerna appliceras.

Sammanfattningsvis, när [createUrl|CController::createUrl] används för att 
generera en URL, används route- och GET-parametrarnas som lämnas till funktionen 
till att avgöra vilken URL-regel som skall appliceras. Om varje parameter 
associerad med en regel kan hittas bland GET-parametrarna som lämnats till 
[createUrl|CController::createUrl], och om regelns route också matchar 
routeparametern, kommer regeln att användas för att generera URL:en.

Om GET-parametrarna som lämnas till [createUrl|CController::createUrl] är fler 
än de som behövs i en regel, hamnar de överflödiga parametrarna i en query-
sträng. Till exempel, ur anropet `$this->createUrl('post/read',array('id'=>100,'year'=>2008))`, 
erhålls `/index.php/post/100?year=2008`. För att få dessa tillkommande parametrar att 
hamna i pathinfo-delen, kompletterar vi regeln med `/*`. Med regeln 
`post/<id:\d+>/*` erhåller vi URL:en `/index.php/post/100/year/2008`.

Som nämnts är det andra ändamålet med URL-regler parsning av request-URL:er. 
Detta är naturligtvis den omvända processen mot URL-generering. Till exempel, 
när en användare skickar en request `/index.php/post/100`, kommer den andra 
regeln i ovanstående exempel att appliceras, vilket löses upp till en route 
`post/read` och GET-parametern `array('id'=>100)` (kan nås  via `$_GET`).

> Note|Märk: Användning av URL-regler försämrar applikationen prestanda. Detta 
beror på att vid parsning av request-URL:en, kommer [CUrlManager] att försöka 
matcha den mot varje regel tills någon kan appliceras. Ju fler regler, desto 
större prestandaförlust. Av denna anledning skall en webbapplikation med 
intensiv trafik minimera sin användning av URL-regler.


Parametrisering av route
------------------------

Vi kan referera till namngivna parametrar i routedelen av en regel. Detta medger 
att regeln appliceras på multipla route baserat på matchningskriterium. Det kan 
även hjälpa till att reducera antalet regler i en applikation och därmed förbättra 
totalprestanda.

Följande exempelregler illustrerar hur route parametriseras med hjälp av 
namngivna parametrar:

~~~
[php]
array(
	'<_c:(post|comment)>/<id:\d+>/<_a:(create|update|delete)>' => '<_c>/<_a>',
	'<_c:(post|comment)>/<id:\d+>' => '<_c>/read',
	'<_c:(post|comment)>s' => '<_c>/list',
)
~~~

Ovan används två namngivna parametrar i routedeln av reglerna:
`_c` och `_a`. Den förra matchar ett kontroller-ID till att vara antingen 
`post` eller `comment`, medan den senare matchar ett  åtgärds-ID till att vara 
`create`, `update` eller `delete`. Parametrarna kan namnges annorlunda så länge 
de inte kommer i konflikt med GET-parametrar som kan finnas i URL:er.

Med användning av ovanstående regler kommer URL:en `/index.php/post/123/create` 
att tolkas som routen `post/create` med GET-parametern `id=123`.
Och givet routen `comment/list` och GET-parametern `page=2`, kan vi generera 
URL:en `/index.php/comments?page=2`.


Parametrisering av värdnamn
---------------------------

Det är möjligt att inkludera värdnamn i regler för tolkning och generering av URL:er. 
Man kan extrahera delar av ett värdnamn till att bilda en GET-parameter. Till exempel 
URL:en `http://admin.example.com/en/profile` kan tolkas om till GET-parametrarna 
`user=admin` och `lang=en`. Å andra sidan kan regler med väednamn även användas till att
bilda URL:er med parametriserade värdnamn.

För att använda parametriserade värdnamn, deklarera kort och gott URL-regler med värdinformation, 
till exempel:

~~~
[php]
array(
	'http://<user:\w+>.example.com/<lang:\w+>/profile' => 'user/profile',
)
~~~

Ovanstående exempel innebär att det första segmentet i värdnamnet skall bilda `user`-parametern 
medan det första segmentet i path-delen skall bilda `lang`-parametern. Regeln motsvarar routen 
`user/profile`.

Märk att [CUrlManager::showScriptName] ignoreras när en en URL skapas med användning av en 
regel innehållande parametriserat värdnamn.

Lägg även märke till att regeln med parametriserat värdnamn INTE skall innehålla underkatalogen
om applikationen är placerad i en underkatalog till webbrotkatalogen. Till exempel, om applikationen 
befinner sig under `http://www.example.com/sandbox/blog`, skall fortfarande samma URL-regel som 
beskrivs ovan användas, utan underkatalogen `sandbox/blog`.


Gömma `index.php`
----------------

Det finns en sak till att göra för att ytterligare rensa upp URL:er, att 
eliminera startskriptet `index.php` från URL:en. Detta kräver att vi 
konfigurerar webbservern såväl som applikationskomponenten 
[urlManager|CWebApplication::urlManager].

Webbservern behöver konfigureras så att URL:en utan startskriptdelen fortfarande 
kommer att hanteras av startskriptet. För [Apache HTTP-server](http://httpd.apache.org/), 
kan detta åstadkommas genom att slå på URL rewriting engine och 
specificera några rewriting-regler. Vi t ex kan skapa filen 
`/wwwroot/blog/.htaccess` med nedanstående innehåll.
Märk att samma innehåll även kan placeras i Apaches konfigurationsfil
inuti elementet `Directory` för `/wwwroot/blog`.

~~~
RewriteEngine on

# if a directory or a file exists, use it directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# otherwise forward it to index.php
RewriteRule . index.php
~~~

Därefter konfigurerar vi propertyn [showScriptName|CUrlManager::showScriptName] 
i komponenten [urlManager|CWebApplication::urlManager] till `false`.

Med anropet `$this->createUrl('post/read',array('id'=>100))`, erhålls nu URL:en 
`/post/100`. Och nog så viktigt, denna URL kan kännas igen korrekt av 
webbapplikationen.

Låtsat URL-suffix
-----------------

Vi kan även lägga till något suffix till URL:er. Till exempel, kan vi erhålla 
`/post/100.html` i stället för `/post/100`. Detta liknar mer en URL till en 
statisk webbsida. För att göra så, konfigurera komponenten 
[urlManager|CWebApplication::urlManager] genom att sätta dess property 
[urlSuffix|CUrlManager::urlSuffix] till önskad suffixsträng.


Anpassade URL-regelklasser
--------------------------

> Note|Märk: Användning av anpassade URL-regelklasser har stöd sedan version 1.1.8.

Som standard representeras varje URL-regel deklarerad med [CUrlManager] som ett 
[CUrlRule]-objekt som genomför uppgiften att analysera request och skapa URL:er 
baserat på den specificerade regeln. Även om [CUrlRule] är tillräckligt flexibel 
att hantera de flesta URL-format, kan det ibland vara önskvärt att utöka den med 
speciella finesser.

Till exempel, för en bilhandlares webbplats, kan önskemål uppstå om stöd för 
URL-format som `/Manufacturer/Model`, där `Manufacturer` och `Model` båda måste 
överensstämma med data i en databastabell. Klassen [CUrlRule] kommer inte att fungera 
eftersom den till största delen förlitar sig på statiskt deklarerade reguljära uttryck, 
vilka saknar kunskap om databaser.

En ny URL-regelklass kan skapas genom utökning av [CBaseUrlRule] för användning 
i en eller flera URL-regler. Med ovanstående bilhandlares webbplats som exempel, 
kan följande URL-regler deklareras,

~~~
[php]
array(
	// en standardregel som mappar '/' till 'site/index'
	'' => 'site/index',

	// en standardregel som mappar '/login' till 'site/login', osv
	'<action:(login|logout|about)>' => 'site/<action>',

	// en anpassad regel för hantering av '/Manufacturer/Model'
	array(
	    'class' => 'application.components.CarUrlRule',
	    'connectionID' => 'db',
	),

	// en standardregel för hantering av 'post/update' osv
	'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
),
~~~

Ovan används den anpassade URL-regelklassen `CarUrlRule` för hantering 
av URL-formatet `/Manufacturer/Model`. Klassen kan skrivas som följer:

~~~
[php]
class CarUrlRule extends CBaseUrlRule
{
	public $connectionID = 'db';

	public function createUrl($manager,$route,$params,$ampersand)
	{
		if ($route==='car/index')
		{
			if (isset($params['manufacturer'], $params['model']))
				return $params['manufacturer'] . '/' . $params['model'];
			else if (isset($params['manufacturer']))
				return $params['manufacturer'];
		}
		return false;  // denna regel tillämpas inte
	}

	public function parseUrl($manager,$request,$pathInfo,$rawPathInfo)
	{
		if (preg_match('%^(\w+)(/(\w+))?$%', $pathInfo, $matches))
		{
			// check $matches[1] and $matches[3] to see
			// if they match a manufacturer and a model in the database
			// If so, set $_GET['manufacturer'] and/or $_GET['model']
			// and return 'car/index'
		}
		return false;  // denna regel tillämpas inte
	}
}
~~~

Den anpassade URL-klassen måste implementera de två abstrakta metoderna 
deklarerade i [CBaseUrlRule]:

* [createUrl()|CBaseUrlRule::createUrl()]
* [parseUrl()|CBaseUrlRule::parseUrl()]

Utöver ovanstående typiska användningssätt kan anpassade URL-regelklasser även 
implementeras för många andra ändamål. Till exempel kan vi skriva en regelklass 
som loggar anropen för URL-analys och -konstruktion. Detta kan vara användbart 
i utvecklingsfasen. Vi kan även skriva en regelklass som presenterar en speciell 
404-felsida i händelse av att alla andra URL-regler misslyckad med att tolka 
det aktuella anropet. Märk att i sådant fall måste regeln för denna specialklass 
deklareras som sista regel.

<div class="revision">$Id: topics.url.txt 3329 2011-06-28 08:31:35Z mdomba $</div>