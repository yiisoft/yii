Skapa modell
============

Innan HTML-koden som ett formulär erfordrar skrivs, behöver vi avgöra vilken 
slags data vi förväntar oss att slutanvändare skriver in samt vilka regler dessa 
data behöver följa. En modellklass kan användas till att registrera denna 
information. En modell är, enligt definition  i underavsnittet 
[Model](/doc/guide/basics.model), den centrala platsen för att hålla 
användarinmatad data samt för validering av denna.

Beroende på hur vi använder oss av inmatade data, kan två olika typer av 
modeller skapas. Om inmatningen hämtas in och används för att sedan slängas, 
skapas lämpligen en [formulärmodell](/doc/guide/basics.model); om 
användarinmatningen hämtas in för att sparas i en databas, används lämpligen en 
[active record](/doc/guide/database.ar)-modell i stället. Båda typerna av modell 
härstammar från samma basklass, [CModel], vilken definierar det gemensamma 
gränssnittet ett formulär behöver.

> Note|Märk: I exemplen i detta avsnitt används huvudsakligen formulärmodeller. 
Samma förfarande kan emellertid även appliceras på [active record](/doc/guide/database.ar)-modeller.

Definiera en modellklass
------------------------

Nedan skapas en modellklass, `LoginForm`, som används för att samla in 
användarinmatning på en loginsida. Eftersom logininformationen bara används till 
att autentisera användaren och inte behöver sparas , skapas `LoginForm` som en 
formulärmodell.

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;
}
~~~

Tre attribut deklareras i `LoginForm`: `$username`, `$password` samt 
`$rememberMe`. De används för att hålla inmatat användarnamn och lösen, samt det 
frivilliga alternativet huruvida logininformationen skall kommas ihåg. Eftersom 
`$rememberMe` har ett standardvärde, `false`, kommer det motsvarande 
alternativet, när det initialt visas i loginformuläret, att vara omarkerat .

> Info: I stället för att kalla dessa medlemsvariabler för propertyn, används 
här termen *attribut* för att särskilja dem från vanliga propertyn. Ett attribut 
är en property som huvudsakligen används till att lagra data som härrör från 
användarinmatning eller från en databas.

Deklarera valideringsregler
---------------------------

När en användare väl postar sina inmatningar och modellen tilldelats dessa, 
behöver vi säkerställa att inmatningarna är giltiga innan de används. Detta sker 
genom validering av inmatningarna mot en uppsättning regler. Valideringsregler 
specificeras i metoden `rules()`, vilken skall returnera en array bestående av 
regelkonfigurationer.

~~~
[php]
class LoginForm extends CFormModel
{
	public $username;
	public $password;
	public $rememberMe=false;

	private $_identity;

	public function rules()
	{
		return array(
			array('username, password', 'required'),
			array('rememberMe', 'boolean'),
			array('password', 'authenticate'),
		);
	}

	public function authenticate($attribute,$params)
	{
		$this->_identity=new UserIdentity($this->username,$this->password);
		if(!$this->_identity->authenticate())
			$this->addError('password','Incorrect username or password.');
	}
}
~~~

Ovanstående kod specificerar att både `username` och `password` är obligatorisk 
inmatning, att `password` skall autentiseras, samt att `rememberMe` skall vara 
ett boolskt värde.

Varje regel som returneras från `rules()` måste vara på följande format:

~~~
[php]
array('AttributeList', 'Validator', 'on'=>'ScenarioList', ...additional options)
~~~

där `AttributeList` är en sträng bestående av kommaseparerade attributnamn vilka 
behöver valideras enligt regeln; `Validator` specificerar vilket slags 
validering som skall utföras; `on`-parametern är frivillig och specificerar en 
lista med scenarier där regeln skall appliceras; additional options är namn-
värdepar som används till att initialisera motsvarande validators propertyvärden.

`Validator` kan specificeras på tre sätt i en valideringsregel. För det första 
kan `Validator` vara namnet på en metod i modellklassen, som `authenticate` i 
ovanstående exempel. Valideringsmetoden måste ha följande signatur:

~~~
[php]
/**
 * @param string $attribute namn på attribut som skall valideras
 * @param array $params alternativ som specificerats i valideringsregeln
 */
public function ValidatorName($attribute,$params) { ... }
~~~

För det andra kan `Validator` vara namnet på en validerarklass. När regeln 
appliceras, kommer en instans av validerarklassen att skapas för att utföra den 
aktuella valideringen. De ytterligare alternativen (additional options) i regeln 
används till att initialisera instansens attributvärden. En validerarklass måste 
ärva från och utvidga [CValidator].

För det tredje kan `Validator` vara ett fördefinierat aliasnamn för en 
validerarklass. I ovanstående exempel är namnet `required` ett alias för 
[CRequiredValidator], vilken säkerställer att attributvärdet som valideras inte 
är tomt. Nedan följer den kompletta förteckningen över validerarklassers 
aliasnamn:

   - `boolean`: alias för [CBooleanValidator], garanterar att attributet 
   har ett värde som är antingen [CBooleanValidator::trueValue] eller 
   [CBooleanValidator::falseValue].

   - `captcha`: alias för [CCaptchaValidator], garanterar att attributet 
   överensstämmer med verifieringskoden som visades i 
   [CAPTCHA](http://en.wikipedia.org/wiki/Captcha).

   - `compare`: alias för [CCompareValidator], garanterar att attributet har 
   samma värde som ett annat attribut eller en konstant.

   - `email`: alias för [CEmailValidator], garanterar att attributet är en 
   giltig email-adress.

   - `date`: alias för [CDateValidator], garanterar att attributet representerar 
   ett giltigt datum-, tid-, eller datetime-värde.

   - `default`: alias för [CDefaultValueValidator], tilldelar specificerade 
   attribut ett standardvärde.

   - `exist`: alias för [CExistValidator], säkerställer att attributvärdet kan 
   återfinnas i den specificerade tabellkolumnen.

   - `file`: alias för [CFileValidator], garanterar att attributet innehåller 
   namnet på en uppladdad fil.

   - `filter`: alias för [CFilterValidator], transformerar attributet med hjälp 
   av ett filter.

   - `in`: alias för [CRangeValidator], garanterar att data håller sig inom ett 
   fördefinierat intervall (lista) av värden.

   - `length`: alias för [CStringValidator], garanterar att längden av data 
   faller inom ett angivet intervall.

   - `match`: alias för [CRegularExpressionValidator], garanterar att data 
   matchar ett reguljärt uttryck (regexp).

   - `numerical`: alias för [CNumberValidator], garanterar att data är ett 
   giltigt tal.

   - `required`: alias för [CRequiredValidator], garanterar att attributet ej är 
   tomt.

   - `type`: alias för [CTypeValidator], garanterar att attributet är av en 
   specifik datatyp.

   - `unique`: alias för [CUniqueValidator], garanterar att data är unikt för en 
   kolumn i en databastabell.

   - `url`: alias för [CUrlValidator], garanterar att data är en giltig URL.

Nedan listas några exemepl på användning av de fördefinierade aliasnamnen för 
validerarklasser:

~~~
[php]
// username är obligatoriskt
array('username', 'required'),
// username måste vara mellan 3 och 12 tecken långt
array('username', 'length', 'min'=>3, 'max'=>12),
// i register-scenariet endast, måste password matcha password2
array('password', 'compare', 'compareAttribute'=>'password2', 'on'=>'register'),
// i login-scenariet endast, måste password vara autentiserat
array('password', 'authenticate', 'on'=>'login'),
~~~


Säkra upp attributtilldelningar
-------------------------------

När en modellinstans har skapats, behöver dess attribut ofta tilldelas värden 
inskickade av slutanvändare. Detta kan lämpligen utföras med hjälp av följande 
massiva tilldelning:

~~~
[php]
$model=new LoginForm;
if(isset($_POST['LoginForm']))
	$model->attributes=$_POST['LoginForm'];
~~~

Den sista programraden innebär en massiv tilldelning av varje fält i 
`$_POST['LoginForm']` till motsvarande modellattribut. 
Detta är ekvivalent med följande tilldelning:

~~~
[php]
foreach($_POST['LoginForm'] as $name=>$value)
{
	if($name is a safe attribute)
		$model->$name=$value;
}
~~~

Det är kritiskt att avgöra vilka attribut som är säkra. Om vi till exempel 
exponerar primärnyckeln till en tabell som säker, kan en angripare få
chansen att modifiera primärnyckeln för en given databaspost och därmed
göra ändringar i data han saknar auktorisation att ändra.


###Deklarera säkra attribut

Ett attribut betraktas som säkert om det förekommer i en valideringsregel 
som är applicerbar på det givna scenariet. Till exempel,

~~~
[php]
array('username, password', 'required', 'on'=>'login, register'),
array('email', 'required', 'on'=>'register'),
~~~

I ovanstående exempel är attributen `username` och `password` obligatoriska i 
`login`-scenariet, medan attributen `username`, `password` och `email` är obligatoriska
i `register`-scenariet. Resultatet blir att, om vi utför en massiv tilldelning i 
`login`-scenariet, kommer endast `username` och `password` att bli massivt tilldelade
eftersom de är de enda attribut som förekommer bland valideringsreglerna för `login`.
Å andra sidan kan alla tre attributen tilldelas massivt om scenariet är `register`.

~~~
[php]
// in login scenario
$model=new User('login');
if(isset($_POST['User']))
	$model->attributes=$_POST['User'];

// in register scenario
$model=new User('register');
if(isset($_POST['User']))
	$model->attributes=$_POST['User'];
~~~

Så varför använder vi en sådan policy för att avgöra om ett attribut är säkert eller inte?
Logiken bakom detta är att om ett attribut redan har en eller flera validateringsregler för
kontroll av dess giltighet, på vilket ytterligare sätt behöver vi bekymra oss för det?

Det är viktigt att komma ihåg att valideringsregler används för att kontrollera användarinmatad
data snarare än data som vi genererar med  kod (t.ex. tidstämpel, automatiskt genererad primärnyckel).
Av denna anledning, lägg INTE till valideringsregler för de attribut som inte förväntas erhålla
indata från slutanvändare.

I vissa fall vill vi kunna deklarera ett attribut som säkert även om vi egentligen inte har några
specifika regler för det. Ett exempel är en artikels innehållsattribut som kan ta emot godtycklig 
inmatning. Detta kan åstadkommas med hjälp av den speciella `safe`-regeln:

~~~
[php]
array('content', 'safe')
~~~

För helhetens skull finns det även en `unsafe`-regel, som används för att uttryckligen deklarera 
ett attribut som ej säkert:

~~~
[php]
array('permission', 'unsafe')
~~~

`unsafe`-regeln används sällan och den utgör ett undantag från den tidigare definitionen av 
säkra attribut.


I fråga om inmatningar som inte är säkra, behöver vi tilldela dem till 
motsvarande attribut med hjälp av individuella tilldelningssatser, som i 
följande exempel:

~~~
[php]
$model->permission='admin';
$model->id=1;
~~~


Sätta igång validering
----------------------

När väl en modell försetts med användarinmatad data, kan metoden 
[CModel::validate()] anropas för att sätta igång datavalideringsprocessen. 
Metoden returnerar ett värde som indikerar huruvida valideringen lyckades eller 
inte. För [CActiveRecord]-modeller, kan validering även sättas igång automatiskt 
till följd av att dess metod [CActiveRecord::save()] anropas.

Ett scenario kan sättas med propertyn [scenario|CModel::scenario], vilket 
därmed indikerar vilken uppsättning valideringsregler som skall åsättas.


Validering utförs scenariobaserat. Propertyn [scenario|CModel::scenario]
specificerar i vilket scenario modellen för tillfället används samt vilken 
uppsättning valideringsregler som skall användas. Till exempel i 
`login`-scenariet, vill vi endast validera `username`- och `password`-inmatningarna 
för user-modellen, medan vi i `register`-scenariet behöver validera fler inmatningar, 
så som `email`, `address`, etc. Följande exempel visar hur man genomför validering i 
scenariet `register` vid registrering av en användare:

~~~
[php]
// creates a User model in register scenario. It is equivalent to:
// $model=new User;
// $model->scenario='register';
$model=new User('register');

// populates the input values into the model
$model->attributes=$_POST['User'];

// performs the validation
if($model->validate())   // if the inputs are valid
    ...
else
    ...
~~~

De scenarier som en regel skall associera till kan specificeras med hjälp av 
regelns `on`-alternativ. Om `on`-alternativet inte anges innebär detta att 
regeln kommer att användas i alla scenarier. Till exempel,

~~~
[php]
public function rules()
{
	return array(
		array('username, password', 'required'),
		array('password_repeat', 'required', 'on'=>'register'),
		array('password', 'compare', 'on'=>'register'),
	);
}
~~~

Resultatet blir att den första regeln appliceras i alla scenarier, medan de 
övriga två reglerna endast kommer att appliceras i scenariet `register`.


Åtkomst till valideringsmeddelanden
-----------------------------------

När väl validering har utförts finns eventuella felmeddelanden lagrade i 
objektet model. Metoderna [CModel::getErrors()] och [CModel::getError()] 
kan användas för att erhålla felmeddelanden. Skillnaden mellan dessa två 
metoder är att den första returnerar *alla* felmeddelanden för specificerat 
modellattribut, medan den andra endast returnerar det *första* felmeddelandet.

Attributs ledtexter
-------------------

Vid utformning av ett formulär behöver vi ofta presentera en ledtext för varje 
inmatningsfält. Ledtexten informerar användaren om vad slags information denne 
förväntas mata in i fältet. Även om en ledtext kan hårdkodas i vyn, erbjuds 
större flexibilitet och praktisk användbarhet om den kan specificeras i 
tillhörande modell.

Som standard lämnar [CModel] helt enkelt namnet på ett attribut som dess 
ledtext. Detta kan anpassas genom att åsidosätta metoden 
[attributeLabels()|CModel::attributeLabels]. Som kommer att framgå i nästa 
avsnitt, tillåter specificering av ledtexter i modellen, oss att skapa formulär 
snabbare och mer kraftfullt.

<div class="revision">$Id: form.model.txt 3482 2011-12-13 09:41:36Z mdomba $</div>