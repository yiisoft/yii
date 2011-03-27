Autentisering och auktorisering
===============================

Autentisering och auktorisering erfordras för en webbsida som skall vara 
tillgänglig endast för vissa användare. *Autentisering* handlar om att verifiera 
att någon är den han/hon uppger sig vara. Identifieringsförfarandet involverar 
vanligtvis ett användarnamn och ett lösenord, men kan också omfatta någon annan 
metod för att verifiera identitet, såsom smartcard, fingeravtryck etc. 
*Auktorisering* innebär att avgöra huruvida en person, när denne väl är 
identifierad (autentiserad), har tillåtelse att manipulera specifika resurser. 
Detta avgörs vanligtvis genom att undersöka om denna person ingår i en viss roll 
som har tillgång till resurserna.

Yii har ett inbyggt autentiserings-/auktoriseringsramverk (auth), vilket är 
lättanvänt och kan anpassas till speciella behov.

Den centrala delen i Yii:s auth-ramverk är den fördefinierade 
applikationskomponenten *user*, ett objekt som implementerar gränssnittet 
[IWebUser]. Komponenten user representerar icke-flyktig information om aktuell 
användares identitet. Den kan kommas åt från alla ställen i koden med hjälp av 
`Yii::app()->user`.

Med hjälp av user-komponenten kan vi kontrollera om en användare är inlogggad 
eller inte via [CWebUser::isGuest]; [logga in|CWebUser::login] och [logga 
ut|CWebUser::logout] en användare; undersöka om en användare kan utföra 
specifika operationer genom att anropa [CWebUser::checkAccess]; samt även 
erhålla [den unika identifieraren|CWebUser::name] och annan icke-flyktig 
identitetsinformation rörande användaren.


Definiera identitetsklass
-------------------------

Som nämnts ovan handlar autentisering om att validera användarens identitet. 
En typisk webbapplikations implementering av autentisering involverar vanligtvis 
en kombination av användarnamn och lösenord för att verifiera en användares identitet. 
Den kan dock omfatta andra metoder och skilda implementeringar kan erfordras. 
För att åstadkomma varierande autentiseringsmetoder introducerar Yii:s auth-ramverk 
begreppet identitetsklass.

Vi definierar en identitetsklass som innehåller den faktiska autentiseringslogiken. 
Identitetsklassen skall implementera gränssnittet [IUserIdentity]. Olika klasser 
kan implementeras för varierande autentiseringsmetoder (t.ex. OpenID, LDAP, 
Twitter OAuth, Facebook Connect). En bra start är att ärva och utvidga 
[CUserIdentity] som är basklass för autenticeringsmetoden som baseras på 
användarnamn och lösenord.

Det mesta arbetet med att definiera en identitetsklass är implementeringen av 
metoden [IUserIdentity::authenticate]. Detta är metoden som används för att kapsla in 
de huvudsakliga detaljerna för tillvägagångssättet vid autentisering. 
En identitetsklass kan även deklarera ytterligare identitetsinformation som behöver 
förbli icke-flyktig under pågående användarsession.

#### Ett exempel

åsidosätter även metoden `getId` så att den returnerar variabeln `_id`, vilken 
åsätts ett värde under autentiseringen (standardimplementeringen returnerar 
användarnamnet som ID). Under autentiseringen kan inhämtad `title`-information 
lagras i ett state med samma namn medelst anrop av [CBaseUserIdentity::setState].

I följande exempel, använder vi en identitetsklass för att demonstrera 
tillvägagångssättet att använda en databas för autentisering. Detta är 
mycket typiskt för de flesta webbapplikationer. En användare matar in sitt användarnamn 
och lösenord i ett inmatningsformulär, därefter validerar vi, med hjälp av [ActiveRecord](/doc/guide/database.ar), 
dessa inloggningsuppgifter mot en användartabell i databasen. 
Flera olika saker demonstreras i detta enstaka exempel:

1. Implementering av metoden `authenticate()` så att den använder databasen för validering 
av inloggningsuppgifter.
2. Åsidosättning av metoden `CUserIdentity::getId()` så att den returnerar propertyn `_id` 
eftersom standardimplementeringen returnerar användarnamnet som ID.
3. Användning av metoden `setState()` ([CBaseUserIdentity::setState]) för att visa 
hur man kan lagra ytterligare information som lätt kan återhämtas vid senare request.

~~~
[php]
class UserIdentity extends CUserIdentity
{
	private $_id;
	public function authenticate()
	{
		$record=User::model()->findByAttributes(array('username'=>$this->username));
		if($record===null)
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		else if($record->password!==md5($this->password))
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->_id=$record->id;
			$this->setState('title', $record->title);
			$this->errorCode=self::ERROR_NONE;
		}
		return !$this->errorCode;
    }

	public function getId()
	{
		return $this->_id;
	}
}
~~~

När vi i nästa avsnitt går igenom inloggning och utloggning, kommer det att framgå att vi 
bifogar denna identitetsklass vid anrop inloggningsmetoden för en användare.
Information som sparas i ett state (genom anrop av [CBaseUserIdentity::setState]) 
förmedlas till [CWebUser] vilken lagrar den i icke-flyktigt minne, så som session.
Denna information kan sedan kommas åt i form av propertyn i [CWebUser]. 
I vårt exempel lagrade vi information om användarens titel via anropet 
`$this->setState('title', $record->title);`. När väl inloggningsprocesen är genomförd, 
går det att erhålla `title`-information för aktuell användare via  `Yii::app()->user->title`.

> Info: Som standard använder [CWebUser] sessionen för icke-flyktig lagring av 
information om användaridentitet. Om cookie-baserad inloggning gjorts möjlig 
(genom att sätta [CWebUser::allowAutoLogin] till true), kan informationen om 
användarens identitet även lagras i en cookie. Se till att känslig information 
(t.ex. lösenord) ej deklareras som icke-flyktig.


In- och utloggning
------------------

Genom användning av identitetsklassen och user-komponenten kan in-och 
utloggningsåtgärder enkelt implementeras.
När vi nu sett ett exempel på hur en användaridentitet kan skapas, använder vi 
detta som hjälp att underlätta implementeringen av erforderliga inloggnings- och 
utloggningsåtgärder. Följande kod visar hur detta kan åstadkommas:

~~~
[php]
// Login a user with the provided username and password.
$identity=new UserIdentity($username,$password);
if($identity->authenticate())
	Yii::app()->user->login($identity);
else
	echo $identity->errorMessage;
......
// Logout the current user
Yii::app()->user->logout();
~~~

Här skapar vi ett nytt UserIdentity-objekt och lämnar med autentiseringsuppgifterna 
(d.v.s `$username`- och `$password`-värdena användaren matat in) till dess constructor. 
Därefter anropar vi helt enkelt metoden `authenticate()`. Vid lyckad autentisering  
lämnar vi vidare identitetsinformationen till metoden [CWebUser::login], som kommer  
att spara identitetsinformationen till icke-flyktigt minne (som standard PHP:s session), 
för senare återhämtning vid kommande request. Om autentiseringen misslyckas kan vi 
erhålla mer information om anledningen från propertyn `errorMessage`.

Huruvida en användare blivit autentiserad kan lätt kontrolleras från valfri plats i 
applikationen med hjälp av `Yii::app()->user->isGuest`. Om icke-flyktigt minne 
(som standard session) och/eller en cookie (se nedan) används till att lagra 
identitetsinformationen, kan användaren förbli inloggad inför efterkommande request. 
I detta fall behöver inte klassen UserIdentity och hela inloggningsprocessen användas 
vid varje request. I stället kommer CWebUser att automatiskt ombesörja laddning av 
identitetsinformationen från icke-flyktigt minne och använda den för att avgöra 
huruvida `Yii::app()->user->isGuest` returnerar true eller false.

Cookie-baserad inloggning
-------------------------

Som standard loggas en användare ut efter en viss tid utan aktivitet, beroende på 
[sessionskonfigurationen](http://www.php.net/manual/en/session.configuration.php). 
Detta beteende kan man ändra genom att propertyn 
[allowAutoLogin|CWebUser::allowAutoLogin] i user-komponenten sätts till true 
samt genom att lämna en varaktighetsparameter till metoden [CWebUser::login]. 
Användaren kommer då att förbli inloggad hela den specificerade varaktigheten, 
även om webbläsarens fönster dessförinnan stängs. Lägg märke till att denna 
finess kräver att användarens webbläsare accepterar cookies.

~~~
[php]
// Låt användaren förbli inloggad i 7 dagar.
// Kontrollera att allowAutoLogin är konfigurerad till true i komponenten user.
Yii::app()->user->login($identity,3600*24*7);
~~~

Som tidigare nämnts kommer, om cookie-baserad inloggning är aktiverad, även de 
tillstånd som lagrats via [CBaseUserIdentity::setState] att sparas i cookie.
Nästa gång användaren loggas in, kommer dessa tillstånd att läsas från cookie 
och göras tillgängliga via `Yii::app()->user`.

Även om Yii kan vidta åtgärder för att skydda en tillstånds-cookie från att bli ändrad 
på klientsidan, rekommenderar vi bestämt att ur säkerhetssynpunkt känslig information 
ej lagras som tillstånd (states). Istället bör sådan information återskapas på serversidan 
genom att den läses från något icke-flyktigt lagringsmedium (t.ex. databas).

Vidare, i fråga om seriösa webbapplikationer, rekommenderar vi starkt användning av följande 
strategi för ökad säkerhet vid cookie-baserad inloggning.

* När en användare genomför en lyckad inloggning genom att fylla i ett inloggningsformulär, 
lagrar vi en slumpgenererad nyckel såväl i cookielagrad tillståndinformation som i 
icke-flyktigt medium på serversidan (t.ex. databas).

* När, vid en senare kommande request, autentisering av användaren sker via cookielagrad information, 
jämför vi de två exemplaren av den slumpgenererade nyckeln och förvissar oss om att de överensstämmer 
innan användaren loggas in.

* Om inloggningsformuläret återigen används för inloggning, behöver nyckeln genereras på nytt.

Genom användning av ovanstående strategi eliminerar vi möjligheten att en användare återanvänder 
en gammal cookie, som kan innehålla inaktuell tillståndsinformation.

För att implementera ovanstående strategi, behöver vi åsidosätta följande två metoder:

* [CUserIdentity::authenticate()]: här utförs den egentliga autentiseringen.
Om användaren är korrekt autentiserad, genererar vi en ny slumpgenererad nyckel och lagrar den 
i såväl databasen som i identitetstillståndet, via [CBaseUserIdentity::setState].

* [CWebUser::beforeLogin()]: anropas när användaren faktiskt loggas in.
Här kontrollerar vi att nyckeln som erhålls från tillstånds-cookie överensstämmer med 
den som hämtas från databasen.


Filter för åtkomstkontroll
--------------------------

Filter för åtkomstkontroll (access control filter) är ett preliminärt 
auktoriseringsschema som bestämmer huruvida den aktuella användaren får utföra 
begärd kontrolleråtgärd. Auktoriseringen baseras på användarnamnet, klientens 
ip-adress samt typ av request. Den tillhandahålls som ett filter benämnt 
["accessControl"|CController::filterAccessControl].

> Tip|Tips: Ett filter för åtkomstkontroll är tillräckligt för enklare scenarier. 
För mer komplex åtkomstkontroll kan rollbaserad åtkomsthantering (RBAC) användas, vilket 
beskrivs i nästa avsnitt.

För att hantera åtkomst till åtgärder i en kontroller, installerar man ett 
filter för åtkomstkontroll genom att åsidosätta [CController::filters] (se 
[Filter](/doc/guide/basics.controller#filter) för fler detaljer angående 
installation av filter).

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'accessControl',
		);
	}
}
~~~

I ovanstående exempel specificeras att filtret [access 
control|CController::filterAccessControl] skall appliceras på varje åtgärd i 
`PostController`. De detaljerade auktoriseringsreglerna som filtret använder 
specificeras genom att man åsidosätter [CController::accessRules] i 
kontrollerklassen.

~~~
[php]
class PostController extends CController
{
	......
	public function accessRules()
	{
		return array(
			array('deny',
				'actions'=>array('create', 'edit'),
				'users'=>array('?'),
			),
			array('allow',
				'actions'=>array('delete'),
				'roles'=>array('admin'),
			),
			array('deny',
				'actions'=>array('delete'),
				'users'=>array('*'),
			),
		);
	}
}
~~~

Ovanstående kod specificerar tre regler, var och en representerad av en array. 
Det första elementet i arrayen är antingen `'allow'` eller `'deny'`, övriga 
består av namn-värdepar som specificerar reglerna genom mönsterparametrar. 
Exemplets regler skall utläsas: åtgärderna `create` och `edit` kan inte utföras 
av ej autentiserade användare (anonymous); åtgärden `delete` kan utföras av användare med 
rollen `admin`; åtgärden `delete` kan inte utföras av någon.

Åtkomstreglerna utvärderas en och en i den ordning de specificerats. Den 
första regeln som matchar aktuellt mönster (t.ex. användarnamn, roller, 
klientens ip-adress) bestämmer resultatet av auktoriseringen. Om denna regel är 
en `allow`-regel, kan åtgärden exekveras; om den är en `deny`-regel, kan 
åtgärden inte köras; om ingen av reglerna är tillämplig i kontextet, kommer 
åtgärden fortfarande att kunna köras.

> Tip|Tips: För att säkerställa att en viss åtgärd inte körs i vissa kontext,
> är det fördelaktigt att i slutet av regeluppsättningen alltid specificera en 
> `deny`-regel som matchar allt , som i det följande:
> ~~~
> [php]
> return array(
>     // ... andra regler...
>     // följande regel förhindrar 'delete'-åtgärden att köras i alla kontext
>     array('deny',
>         'actions'=>array('delete'),
>     ),
> );
> ~~~
> Anledningen till ovanstående regel är att om ingen regel alls matchar ett kontext,
> kommer en åtgärd att fortsätta exekveras.

En åtkomstregel kan matcha följande kontextparametrar:

   - [actions|CAccessRule::actions]: specificerar vilka åtgärder denna regel 
   matchar. Detta skall vara en array av åtgärds-ID:n. Jämförelsen sker 
   skiftlägesoberoende (case-insensitive).

   - [controllers|CAccessRule::controllers]: specificerar vilka kontroller denna regel 
   matchar. Detta skall vara en array av kontroller-ID:n. Jämförelsen sker 
   skiftlägesoberoende.

   - [users|CAccessRule::users]: specificerar vilka användare denna regel 
   matchar. Aktuellt [användarnamn|CWebUser::name] används för matchning. Jämförelsen 
   sker skiftlägesoberoende. Tre specialtecken kan användas här:

	   - `*`: varje användare, inkluderande både anonyma och autentiserade användare.
	   - `?`: anonyma användare.
	   - `@`: autentiserade användare.

   - [roles|CAccessRule::roles]: specificerar vilka roller denna regel matchar. 
   Till detta används finessen [rollbaserad åtkomstkontroll](/doc/guide/topics.auth#role-based-access-control) 
   som kommer att beskrivas i nästa underavsnitt. Mer 
   detaljerat, regeln appliceras om [CWebUser::checkAccess] returnerar true för 
   någon av rollerna. Lägg märke till att roller huvudsakligen bör användas i en 
   `allow`-regel eftersom, per definition, representerar en roll tillåtelse att 
   göra något.  Märk också att, även om termen `roles` används här, kan dess 
   värde utgöras av varje auth-element, inklusive roller, uppgifter och operationer.

   - [ips|CAccessRule::ips]: specificerar vilka (klient) ip-adresser denna regel 
   matchar.

   - [verbs|CAccessRule::verbs]: specificerar vilka typer av request (t.ex. 
   `GET`, `POST`) denna regel, matchar. Jämförelsen sker skiftlägesoberoende.

   - [expression|CAccessRule::expression]: specificerar ett PHP-uttryck vars värde 
   indikerar huruvida denna regel matchar. I uttrycket kan man använda variabeln 
   `$user`, vilken refererar till `Yii::app()->user`.


Hantera resultat av auktorisering
---------------------------------

När en auktorisering misslyckas, dvs användaren tillåts inte utföra den 
tilltänkta åtgärden, kan ett av följande två scenarier utspela sig:

   - Om användaren inte är inloggad samt user-komponentens property 
   [loginUrl|CWebUser::loginUrl] konfigurerats att vara inloggningssidans URL, kommer 
   webbläsaren att styras om till den sidan. Lägg märke till att som standard 
   pekar [loginUrl|CWebUser::loginUrl] till sidan `site/login`.

   - I annat fall kommer en HTTP-exception att presenteras, med felkod 403.

När [loginUrl|CWebUser::loginUrl]-propertyn konfigureras kan man ange en relativ 
eller absolut URL. Man kan även ange en array som då kommer att användas vid 
generering av en URL genom anrop till [CWebApplication::createUrl]. Det första 
arrayelementet skall specificera en [route](/doc/guide/basics.controller#route) 
till inloggningskontrollerns åtgärd, resterande namn-värdepar är GET-parametrar. 
Till exempel,

~~~
[php]
array(
	......
	'components'=>array(
		'user'=>array(
			// this is actually the default value
			'loginUrl'=>array('site/login'),
		),
	),
)
~~~

Om webbläsaren har styrts om till inloggningssidan och inloggningen lyckas, vill 
vi antagligen styra webbläsaren tillbaka till sidan som fann auktoriseringen 
otillräcklig. Hur kan vi veta URL:en för denna sida? Den informationen går att 
erhålla från user-komponentens property [returnUrl|CWebUser::returnUrl]. Sålunda 
kan omstyrningen åstadkommas på följande sätt:

~~~
[php]
Yii::app()->request->redirect(Yii::app()->user->returnUrl);
~~~


Rollbaserad åtkomsthantering
----------------------------

Rollbaserad åtkomsthantering (RBAC) tillhandahåller enkel men ändå kraftfull 
centraliserad åtkomstkontroll. Vänligen läs [wiki-
artikeln](http://en.wikipedia.org/wiki/Role-based_access_control) för fler 
detaljer kring RBAC i jämförelse med andra mer traditionella scheman för 
åtkomstkontroll.

Yii implementerar ett hierarkiskt RBAC-schema genom sin applikationskomponent 
[authManager|CWebApplication::authManager]. I det följande introduceras 
huvudkoncepten i detta schema; därefter beskrivs hur man definierar 
auktoriseringsdata; till sist visas hur auktoriseringsdata kommer till 
användning vid genomförande av åtkomstkontroll.

### Översikt

Ett fundamentalt koncept i Yii:s RBAC är *auktoriseringsartikel* (authorization 
item). En auktoriseeringsartikel är en rättighet att göra någonting (t.ex. skapa 
nya bloggpostningar, hantera användare). Alltefter dess finkornighet samt 
tilltänkta publik kan auktoriseringsartiklar klassificeras som *operationer*, 
*uppgifter* och *roller*. En roll består av uppgifter, en uppgift består av 
operationer, en operation är en atomär rättighet. Till exempel kan vi ha ett 
system med rollen `administratör` vilken består av uppgifterna `hantera 
postningar` och `hantera användare`. Uppgiften `hantera användare` kan i sin tur 
bestå av operationerna `skapa användare`, `uppdatera användare` samt `tag bort 
användare`. För större flexibilitet tillåter Yii också att en roll består av 
andra roller eller operationer, en uppgift av andra uppgifter, och en operation 
av andra operationer.

En auktoriseringsartikel identifieras unikt av dess namn.

En auktoriseringsartikel kan associeras med en *affärsregel* (business rule). En 
affärsregel är ett stycke PHP-kod som kommer att exekveras när åtkomstkontroll 
skall utföras enligt auktoriseringsartikeln. Endast om exekveringen returnerar 
true, kommer användaren att anses ha rättigheten som representeras av 
auktoriseringsartikeln. Till exempel, när operationen `updatePost` definieras, 
vill vi antagligen lägga till en affärsregel som kollar om användar-ID är samma 
som postningens författares ID, så att endast författaren själv kan ha rättighet 
att uppdatera en postning.

Genom användning av auktoriseringsartiklar kan vi bygga upp en 
*auktoriseringshierarki*. En artikel `A` är förälder till en annan artikel `B` i 
hierarkin om `A` består av `B` (eller säg `A` ärver rättigheter(na) som `B` 
representerar). En artikel kan ha flera barnartiklar (child items), och den 
kan också ha flera föräldraartiklar. Av denna anledning är en 
auktoriseringshierarki en partiellt ordnad graf snarare än en trädstruktur. I 
denna hierarki placerar sig rollartiklar på översta nivån, operationer på de 
lägsta nivåerna, med uppgiftsartiklar däremellan.

När vi väl har en auktoriseringshierarki, kan vi tilldela applikationsanvändare 
roller i denna hierarki. En användare har, när denne tilldelats en roll, de 
rättigheter som rollen representerar. Till exempel, om vi tilldelar en användare 
rollen `administratör`, kommer denne att ha administratörsrättigheterna vilka 
inkluderar `hantera postningar` and `hantera användare` (och motsvarande 
operationer så som `skapa användare`).

Nu startar det roliga. I en kontrolleråtgärd, vill man kolla om den aktuella 
användaren kan ta bort den specificerade postningen. Med användning av RBAC-
hierarki och -tilldelning kan detta enkelt låta sig göras på följande sätt:

~~~
[php]
if(Yii::app()->user->checkAccess('deletePost'))
{
	// delete the post
}
~~~

Konfigurera auktoriseringshanteraren
------------------------------------

Innan vi sätter igång med att definiera en auktoriseringshierarki och genomföra 
åtkomstkontroll, behöver vi konfigurera applikationskomponenten 
[authManager|CWebApplication::authManager]. Yii tillhandahåller två typer av 
auktoriseringshanterare: [CPhpAuthManager] och [CDbAuthManager]. Den förra 
använder PHP-skriptfiler till att lagra auktoriseringsdata, medan den senare 
lagrar auktoriseringsdata i en databas. När vi konfigurerar 
applikationskomponenten [authManager|CWebApplication::authManager], behöver vi 
specificera vilken komponentklass som skall användas samt vilka initiala 
propertyvärden som skall gälla för komponenten. Till exempel,

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'sqlite:path/to/file.db',
		),
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'connectionID'=>'db',
		),
	),
);
~~~

Därefter kan [authManager|CWebApplication::authManager] kommas åt via 
`Yii::app()->authManager`.

Definiera auktoriseringshierarki
--------------------------------

Att definiera en auktoriseringshierarki involverar tre steg: definiera 
auktoriseringsartiklar, upprätta samband mellan auktoriseringsartiklar samt 
tilldela applikationsanvändare roller. Applikationskomponenten 
[authManager|CWebApplication::authManager] tillgängliggör en hel uppsättning 
API:er för detta ändamål.

För att definiera en auktoriseringsartikel, anropa en av följande metoder,
beroende på typ av artikel:

   - [CAuthManager::createRole]
   - [CAuthManager::createTask]
   - [CAuthManager::createOperation]

När vi väl har en uppsättning auktoriseringsartiklar, kan följande metoder 
anropas för att etablera samband mellan auktoriseringsartiklar:

   - [CAuthManager::addItemChild]
   - [CAuthManager::removeItemChild]
   - [CAuthItem::addChild]
   - [CAuthItem::removeChild]

Och slutligen, anropar vi följande metoder för att tilldela individuella 
användare rollartiklar:

   - [CAuthManager::assign]
   - [CAuthManager::revoke]

Nedan visas i ett exempel hur man bygger en auktoriseringshierarki med hjälp av 
de tillgängliga API:erna:

~~~
[php]
$auth=Yii::app()->authManager;

$auth->createOperation('createPost','create a post');
$auth->createOperation('readPost','read a post');
$auth->createOperation('updatePost','update a post');
$auth->createOperation('deletePost','delete a post');

$bizRule='return Yii::app()->user->id==$params["post"]->authID;';
$task=$auth->createTask('updateOwnPost','update a post by author himself',$bizRule);
$task->addChild('updatePost');

$role=$auth->createRole('reader');
$role->addChild('readPost');

$role=$auth->createRole('author');
$role->addChild('reader');
$role->addChild('createPost');
$role->addChild('updateOwnPost');

$role=$auth->createRole('editor');
$role->addChild('reader');
$role->addChild('updatePost');

$role=$auth->createRole('admin');
$role->addChild('editor');
$role->addChild('author');
$role->addChild('deletePost');

$auth->assign('reader','readerA');
$auth->assign('author','authorB');
$auth->assign('editor','editorC');
$auth->assign('admin','adminD');
~~~

När vi väl har etablerat denna hierarki, kommer komponenten [authManager|CWebApplication::authManager] 
(t. ex. [CPhpAuthManager], [CDbAuthManager]) att ladda auktoriseringsartiklarna 
automatiskt när de skapas. Därför behöver vi bara exekvera  ovanstående kod en gång 
och INTE vid varje request.

> Info: Även om ovanstående exempel framstår som omfattande och långrandigt, 
> återfinns det här som demonstration. Utvecklare behöver vanligtvis utveckla 
> någon form av administrativt användargränssnitt som slutanvändare sedan kan 
> använda för att, på ett mer intuitivt sätt, åstadkomma en auktoriseringshierarki.


Använda affärsregler
--------------------

När vi definierar en auktoriseringshierarki kan vi associera en roll, en uppgift 
eller en operation med en så kallad *affärsregel*. Vi kan även associera en affärsregel 
när vi tilldelar en roll till en användare. En affärsregel är ett stycke PHP-kod 
som exekveras vid åtkomstkontroll. Returvärdet från denna kod används för att avgöra 
huruvida rollen eller tilldelningen avser aktuell användare. I ovanstående exempel 
associerades en affärsregel till uppgiften `updateOwnPost`. I affärsregeln undersöker 
vi helt enkelt huruvida aktuell användares ID är identiskt med den specificerade 
postningens författar-ID. Information om postningen, i arrayen `$params`, levereras 
av utvecklare när åtkomstkontroll utförs.


### Åtkomstkontroll

För att genomföra åtkomstkontroll, behöver man först veta namnet på 
auktoriseringsartikeln. Till exempel, för att kontrollera om den aktuella 
användaren kan skapa en postning, skulle vi kontrollera om denne har rättigheten 
representerad av operationen `createPost`. Sedan anropas [CWebUser::checkAccess] 
för att genomföra åtkomstkontrollen:

~~~
[php]
if(Yii::app()->user->checkAccess('createPost'))
{
	// create post
}
~~~

Om auktoriseringsregeln är associerad med en affärsregel som erfordrar 
ytterligare parametrar, kan vi även lämna med dem. Till exempel, för att 
undersöka huruvida en användare tillåts uppdatera en postning, lämnar vi med 
postningsdata i argumentet `$params`:

~~~
[php]
$params=array('post'=>$post);
if(Yii::app()->user->checkAccess('updateOwnPost',$params))
{
	// update post
}
~~~


### Använda standardroller

Många webbapplikationer behöver ett antal mycket specialiserade roller som 
tilldelas varje eller åtminstone de flesta systemanvändarna. Vi kanske vill 
tilldela alla autentiserade användare vissa rättigheter. Det kan skapa en 
hel del underhållsproblem om vi väljer att uttryckligt specificera och lagra 
dessa rolltilldelningar. Vi kan dra nytta av *standardroller* för att lösa 
detta problem.

En standardroll är en roll som underförstått tilldelas varje användare, inklusive
både autentiserade och gäster. Vi behöver inte uttryckligen tilldela denna till en 
användare. När [CWebUser::checkAccess] körs kommer standardroller att kontrolleras 
först, som om de hade tilldelats användaren.

Standardroller måste deklareras i propertyn [CAuthManager::defaultRoles].
Exempelvis följande konfiguration deklarerar två roller som standardroller, 
nämligen `authenticated` och `guest`.

~~~
[php]
return array(
	'components'=>array(
		'authManager'=>array(
			'class'=>'CDbAuthManager',
			'defaultRoles'=>array('authenticated', 'guest'),
		),
	),
);
~~~

Eftersom en standardroll tilldelas alla användare, behöver den i regel associeras 
med en affärsregel som avgör om rollen verkligen skall tillämpas på användaren. 
Exempel: följande kod definierar två roller, `authenticated` och `guest`, 
vilka i praktiken tillämpas på autentiserade användare respektive gästanvändare.

~~~
[php]
$bizRule='return !Yii::app()->user->isGuest;';
$auth->createRole('authenticated', 'authenticated user', $bizRule);

$bizRule='return Yii::app()->user->isGuest;';
$auth->createRole('guest', 'guest user', $bizRule);
~~~

<div class="revision">$Id: topics.auth.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>