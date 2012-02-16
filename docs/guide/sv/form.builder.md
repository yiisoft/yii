Använda Form Builder
====================

När vi skapar HTML-formulär, finner vi ofta att vi skriver mängder av repetitiv vykod 
som är svår att återanvända i ett annat projekt. Till exempel, för varje inmatningsfält
behöver vi associera fältet med dess textetikett samt presentera eventuella valideringsfel.
För att göra sådan kod mer återanvändningsbar, kan vi använda formulärbyggaren.


Grundkoncept
------------

Yii:s formulärbyggare använder ett [CForm]-objekt till att representera de specifikationer som 
behövs för att beskriva ett HTML-formulär, inklusive de datamodeller som är associerade med formuläret,
vilka slags inmatningsfält formuläret består av, samt hur hela formuläret skall renderas. Det utvecklare 
huvudsakligen behöver göra är att skapa och konfigurera detta [CForm]-objekt, därefter anropa dess 
renderingsmetod för att presentera formuläret.

Specifikationer för formulärinmatning organiseras som en hierarki av formulärelement.
Hierarkins rot härbärgerar [CForm]-objektet. Rotformuläret upprätthåller sina 
underordnade objekt i två samlingsobjekt (collection): [CForm::buttons] och [CForm::elements]. 
Den förra innehåller knappelement (så som submit, reset), medan den senare innehåller 
inmatningselement, textsträngar och underformulär. Ett underformulär är ett [CForm]-objekt som
ingår i [CForm::elements]-samlingen i ett annat formulär. Det kan ha en egen datamodell samt
[CForm::buttons]- och [CForm::elements]-samlingar.

När användare skickar ett formulär, medför detta att all data skickas som matats in i inmatningsfält i 
hela formulärhierarkin, inklusive de inmatningsfält som hör till underformulären. [CForm] erbjuder 
ändamålsenliga metoder som automatiskt kan tilldela inmatat data till motsvarande modellattribut samt 
genomföra datavalidering.


Skapa ett enkelt formulär
-------------------------

I det följande visas hur formulärbyggaren kan användas till att skapa ett 
inloggningsformulär.

Först skriver vi kod för login-åtgärden:

~~~
[php]
public function actionLogin()
{
	$model = new LoginForm;
	$form = new CForm('application.views.site.loginForm', $model);
	if($form->submitted('login') && $form->validate())
		$this->redirect(array('site/index'));
	else
		$this->render('login', array('form'=>$form));
}
~~~

I ovanstående kod skapas ett [CForm]-objekt som använder specifikationerna adresserade 
av sökvägsalias `application.views.site.loginForm` (förklaras nedan). [CForm]-objektet 
är associerat till `LoginForm`-modellen så som beskrivs i [Skapa Model](/doc/guide/form.model).

Som koden är utformad kommer webbläsaren, efter att formuläret skickats och all inmatning 
validerats utan fel, att styras om till sidan `site/index`. I övriga fall renderas 
`login`-vyn med formulärets innehåll.

Sökvägsalias `application.views.site.loginForm` refererar närmare bestämt till PHP-filen 
`protected/views/site/loginForm.php`. Denna fil skall returnera en PHP-array som 
representerar den konfiguration [CForm] fordrar, så som visas nedan:

~~~
[php]
return array(
	'title'=>'Please provide your login credential',

    'elements'=>array(
        'username'=>array(
            'type'=>'text',
            'maxlength'=>32,
        ),
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),
        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),

    'buttons'=>array(
        'login'=>array(
            'type'=>'submit',
            'label'=>'Login',
        ),
    ),
);
~~~

Konfigurationen är en associativ array bestående av namn-värdepar som används för 
att initialisera motsvarande propertyn hos [CForm]. De viktigaste propertyna att 
konfigurera är som tidigare nämnts, [CForm::elements] och [CForm::buttons]. Var och en
av dem fordrar en array som specificerar en lista med formulärelement. Fler detaljer om 
hur formulärelement konfigureras följer i nästa underavsnitt.

Slutligen skriver vi ett vyskript `login`, som kan vara lika enkelt som följande,

~~~
[php]
<h1>Login</h1>

<div class="form">
<?php echo $form; ?>
</div>

~~~

> Tip|Tips: Ovanstående kod `echo $form;` är ekvivalent med `echo $form->render();`.
> Detta beror på att  [CForm] implementerar den magiska metoden `__toString` som 
> anropar `render()` och returnerar en strängrepresentation av formulärobjektet.


Specificera formulärelement
---------------------------

När formulärbyggaren används skiftar huvuddelen av våra ansträngningar från att skriva kod för vyskript
till att specificera formulärelement. I detta underavsnitt beskrivs hur man specificerar propertyn 
[CForm::elements]. [CForm::buttons] kommer inte att beskrivas eftersom dess konfiguration är nästan
samma som för [CForm::elements].

Propertyn [CForm::elements] accepterar en array som sitt värde. Varje arrayelement specificerar ett enstaka
formulärelement som kan vara ett inmatningselement, en strängkonstant eller ett underformulär.

### Specificera inmatningselement

Ett inmatningselement består i huvudsak av en etikett, ett inmatningsfält, en ledtrådstext samt felpresentation.
Det måste associeras med ett modellattribut. Specifikationen för ett inmatningselement representeras som  
en instans av [CFormInputElement]. Följande kod i en [CForm::elements]-array specificerar ett enskilt
inmatningselement:

~~~
[php]
'username'=>array(
    'type'=>'text',
    'maxlength'=>32,
),
~~~

Det fastställer att modelattributet har namnet `username`, att inmatningsfältets typ är `text` och att 
attributet `maxlength` har värdet 32. 

Vilken som helst skrivbar propertry i [CFormInputElement] kan konfigureras på ovanstående sätt. 
Vi kan till exempel specificera alternativet [hint|CFormInputElement::hint] 
för att presentera en ledtrådstext, eller alternativet [items|CFormInputElement::items] om inmatningsfältet 
är en listbox, en dropdown-lista, en checkbox-lista eller en radiobutton-lista. Om ett alternativs namn 
inte är en property i [CFormInputElement], behandlas det som attributet tillhörande det motsvarande HTML 
inmatningselementet. Till exempel, eftersom `maxlength` i ovanstående inte är en property i [CFormInputElement], 
kommer den att renderas som attributet `maxlength` för HTML textinmatningsfältet.

Alternativet [type|CFormInputElement::type] förtjänar ytterligare uppmärksamhet. Det specificerar vilken typ av 
inmatningselement som skall renderas. Typen `text` innebär till exempel att ett normalt textinmatningsfält renderas;
`password` att ett inmatningsfält för lösen renderas. [CFormInputElement] känner till följande inbyggda elementtyper:

 - text
 - hidden
 - password
 - textarea
 - file
 - radio
 - checkbox
 - listbox
 - dropdownlist
 - checkboxlist
 - radiolist

Bland de ovanstående inbyggda typerna förtjänar "list"-typerna `dropdownlist`, `checkboxlist` 
och `radiolist`, en mer ingående beskrivning. Dessa typer kräver att man sätter propertyn 
[items|CFormInputElement::items] för det motsvarande inmatningselementet.
Detta kan göras på följande sätt:

~~~
[php]
'gender'=>array(
    'type'=>'dropdownlist',
    'items'=>User::model()->getGenderOptions(),
    'prompt'=>'Please select:',
),

...

class User extends CActiveRecord
{
	public function getGenderOptions()
	{
		return array(
			0 => 'Male',
			1 => 'Female',
		);
	}
}
~~~

Ovanstående kod genererar en drop-downlista vars prompt är "please select:". Listan med alternativ 
inkluderar "Male" och "Female", vilka returneras av metoden `getGenderOptions` i modellklassen `User` model.

Förutom dessa inbyggda typer, kan alternativet [type|CFormInputElement::type] också ges namnet på en widgetklass 
eller sökvägsalias till densamma. Widgetklassen måste ärva från [CInputWidget] eller [CJuiInputWidget]. 
När inmatningselementet renderas, kommer en instans av den specificerade widgetklassen att skapas och renderas. 
Widgeten kommer att konfigureras från specifikationen som givits för inmatningselementet.


### Specificera textkonstant

Många gånger innehåller ett formulär en del dekorativ HTML-kod förutom inmatningsfälten. Exempelvis kan en horisontell
linje behövsas för att separera olika delar av formuläret; en bild kan behöva läggas in på vissa ställen för att
förbättra den visuella framtoningen hos formuläret. Sådan HTML-kod kan specificeras i form av textkonstanter i samlingen 
[CForm::elements]. För att göra detta, specificera helt enkelt en textsträng som ett arrayelement på lämplig plats i
[CForm::elements]. Till exempel,

~~~
[php]
return array(
    'elements'=>array(
		......
        'password'=>array(
            'type'=>'password',
            'maxlength'=>32,
        ),

        '<hr />',

        'rememberMe'=>array(
            'type'=>'checkbox',
        )
    ),
	......
);
~~~

Ovan, sätts en horisontell linje in mellan inmatningsfälten `password` och `rememberMe`t.

Textkonstanter kommer bäst till användning vid oregelbundet textinnehåll och position. Om varje inmatningselement 
i ett formulär behöver dekoreras likformigt, kan vi stället anpassa sättet på vilket ett formulär renderas, 
vilket kommer att förklaras längre fram i detta avsnitt.


### Specificera underformulär

Underformulär används för att dela upp ett omfattande formulär i flera logiskt sammanhängande stycken. 
Vi kan till exempel dela upp ett formulär för användarregistrering i två underformulär: inloggningsinformation 
och profilinformation. Varje underformulär kan vara, men behöver inte vara, associerat med en datamodell. 
I exemplet med formulär för användarregistrering, om inloggningsinformation respektive profilinformation lagras 
i två separata databastabeller (och därmed i två datamodeller), kommer vardera underformuläret att vara  knutet 
till en motsvarande datamodell. Om vi lagrar allting i en enda databastabell kommer inget av underformulären att ha 
en datamodelleftersom de delar samma modell som rotformuläret.

Även ett underformulär representeras av ett [CForm] object. För att specificera ett underformulär konfigurerar vi 
propertyn [CForm::elements] med ett element vars typ är `form`:

~~~
[php]
return array(
    'elements'=>array(
		......
        'user'=>array(
            'type'=>'form',
            'title'=>'Login Credential',
            'elements'=>array(
            	'username'=>array(
            		'type'=>'text',
            	),
            	'password'=>array(
            		'type'=>'password',
            	),
            	'email'=>array(
            		'type'=>'text',
            	),
            ),
        ),

        'profile'=>array(
        	'type'=>'form',
        	......
        ),
        ......
    ),
	......
);
~~~

Liksom vid konfigurering av ett rotformulär behöver vi för ett underformulär i huvudsak konfigurera propertyn 
[CForm::elements]. Om ett underformulär behöver associeras med en datamodell kan vi dessutom konfigurera dess
property [CForm::model].

Ibland kan vi behöva representera ett formulär med en annan klass än den underförstådda [CForm]. Till exempel,
vilket kommer att framgå senare i detta avsnitt, kan vi ärva och utöka [CForm] för att anpassa logiken för rendering.
Genom att specificera inmatningselementets typ till `form` kommer ett underformulär automatiskt att representeras av 
ett objekt av samma klass som dess förälder. Om vi specificerar inmatningselementets typ till något i stil med 
`XyzForm` (en sträng avslutad med `Form`), kommer underformuläret att representeras av ett `XyzForm`-objekt.


Tillgång till formulärelement
-----------------------------

Access till formulärelement är lika enkelt som till arrayelement. Propertyn [CForm::elements] returnerar 
ett [CFormElementCollection]-objekt, vilket ärver från [CMap] och därmed tillåter access till dess element som 
för en vanlig array. Till exempel, för att accessa elementet `username` i exemplet med inloggningsformuläret, 
kan följande kod användas:

~~~
[php]
$username = $form->elements['username'];
~~~

Och för att accessa elementet `email` i exemplet formulär för användarregistrering, kan vi använda

~~~
[php]
$email = $form->elements['user']->elements['email'];
~~~

Eftersom [CForm] implementerar arrayaccess till dess property [CForm::elements], kan ovanstående kod 
förenklas ytterligare till:

~~~
[php]
$username = $form['username'];
$email = $form['user']['email'];
~~~


Skapa ett nästlat formulär
--------------------------

Underformulär har redan beskrivits. Vi kallar ett formulär med underformulär för ett nästlat formulär. 
I detta avsnitt kommer formuläret för användarregistrering att användas som exempel för att visa hur man 
kan skapa ett nästlat formulär associerat med flera datamodeller . Antag att användarens inloggningskoder 
lagras som en modell `User`, medan användarens profilinformation lagras som en modell `Profile`.

Först skapar vi `register`-åtgärden som följer:

~~~
[php]
public function actionRegister()
{
	$form = new CForm('application.views.user.registerForm');
	$form['user']->model = new User;
	$form['profile']->model = new Profile;
	if($form->submitted('register') && $form->validate())
	{
		$user = $form['user']->model;
		$profile = $form['profile']->model;
		if($user->save(false))
		{
			$profile->userID = $user->id;
			$profile->save(false);
			$this->redirect(array('site/index'));
		}
	}

	$this->render('register', array('form'=>$form));
}
~~~

I det ovanstående skapar vi formuläret med hjälp av konfigurationen specificerad i `application.views.user.registerForm`.
När formuläret skickats och validerats utan fel försöker vi spara modellerna user och profile.
Vi hämtar modellerna user och profile genom att accessa propertyn `model` i det motsvarande underformulärobjektet.
Efetrsom validering av inmatningen redan skett anropar vi `$user->save(false)` för att hoppa över valideringen. 
Förfarandet upprepas för modellen profile.

Därefter skriver vi formulärets konfigureringsfil `protected/views/user/registerForm.php`:

~~~
[php]
return array(
	'elements'=>array(
		'user'=>array(
			'type'=>'form',
			'title'=>'Login information',
			'elements'=>array(
		        'username'=>array(
		            'type'=>'text',
		        ),
		        'password'=>array(
		            'type'=>'password',
		        ),
		        'email'=>array(
		            'type'=>'text',
		        )
			),
		),

		'profile'=>array(
			'type'=>'form',
			'title'=>'Profile information',
			'elements'=>array(
		        'firstName'=>array(
		            'type'=>'text',
		        ),
		        'lastName'=>array(
		            'type'=>'text',
		        ),
			),
		),
	),

    'buttons'=>array(
        'register'=>array(
            'type'=>'submit',
            'label'=>'Register',
        ),
    ),
);
~~~

När varje underformulär specificras ovan, specificerar vi också dess property [CForm::title].
Den underförstådda logiken för rendering av formulär kommer att omfatta varje underformulär med ett 
field-set som använder denna property som sin titel.

Till slut skriver vi det enkla vyskriptet `register`:

~~~
[php]
<h1>Register</h1>

<div class="form">
<?php echo $form; ?>
</div>
~~~


Anpassa presentation av formulär
--------------------------------

Den främsta fördelen med att använda formulärbyggaren är separationen av logik (dvs formulärkonfigurationen 
lagrad i en separat fil) och presentation (metoden [CForm::render]). Det får till resultat att vi kan anpassa 
presentationen av formulär genom att antingen ärva från och utöka [CForm::render] eller tillhandahålla en partiell 
vy för rendering av formuläret. Båda tillvägagångssätten kan behålla formulärkonfigurationen oförändrad och kan lätt 
återanvändas.

Vid utökning av [CForm::render], behöver man huvudsakligen gå igenom samlingarna [CForm::elements] och [CForm::buttons] 
och anropa varje formulärelements metod [CFormElement::render]. Till exempel,

~~~
[php]
class MyForm extends CForm
{
	public function render()
	{
		$output = $this->renderBegin();

		foreach($this->getElements() as $element)
			$output .= $element->render();

		$output .= $this->renderEnd();

		return $output;
	}
}
~~~

Vi kan även skriva ett vyskript `_form` som renderar formuläret:

~~~
[php]
<?php
echo $form->renderBegin();

foreach($form->getElements() as $element)
	echo $element->render();

echo $form->renderEnd();
~~~

För att använda detta vyskript kan vi göra följande metodanrop:

~~~
[php]
<div class="form">
$this->renderPartial('_form', array('form'=>$form));
</div>
~~~

Om generisk formulärrendering inte räcker för ett specifikt formulär (till exempel om formuläret 
behöver några oregelbundna dekorationer av vissa element), kan vi göra som följer i ett vyskript:

~~~
[php]
some complex UI elements here

<?php echo $form['username']; ?>

some complex UI elements here

<?php echo $form['password']; ?>

some complex UI elements here
~~~

Med det senare tillvägagångssättet verkar inte formulärbyggaren tillföra någon speciell nytta, 
eftersom vi fortfarande behöver skriva liknande kvantiteter av formulärkod. Det är fortfarande till 
nytta, dock, att formuläret specificeras genom användning av en separat konfigurationsfil, då det 
hjälper utvecklare att bättre fokusera på logiken.


<div class="revision">$Id: form.builder.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>