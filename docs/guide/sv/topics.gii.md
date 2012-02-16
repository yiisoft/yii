Automatisk kodgenerering
========================

Med start fr o m version 1.1.2, är Yii försett med ett webbaserat kodgenereringsverktyg benämnt *Gii*. 
Det ersätter det tidigare `yiic shell`-verktyget, vilket körs från kommandorad. 
I denna sektion beskrivs hur Gii används samt hur Gii kan byggas ut för att åstadkomma ökad 
utvecklingsproduktivitet.

Använda Gii
-----------

Gii är implementerad i form av en modul och måste köras under en befintlig Yii-applikation. För att använda Gii, 
måste vi först komplettera applikationskonfigurationen som följer:

~~~
[php]
return array(
	......
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'pick up a password here',
			// 'ipFilters'=>array(...a list of IPs...),
			// 'newFileMode'=>0666,
			// 'newDirMode'=>0777,
		),
	),
);
~~~

Ovan deklareras en modul med namnet `gii`, vars klass är [GiiModule]. Vi anger även ett lösenord för 
modulen, vilket kommer att efterfrågas när vi vill ha tillgång till Gii.

Av säkerhetsskäl är Gii som standard konfigurerad till att endast kunna köras från localhost. 
Om vi vill göra den åtkomlig från andra betrodda datorer, kan detta konfigurera med propertyn [GiiModule::ipFilters] 
så som åskådliggörs i ovanstående kodexempel.

Eftersom Gii kommer att generera och spara nya kodfiler i den befintliga applikationen, 
måste vi se till att webbserverprocessen har erforderlig rättighet att göra det. 
Ovanstående propertyn [GiiModule::newFileMode] och [GiiModule::newDirMode] bestämmer hur de nya filkatalogerna och 
filerna kommer att genereras.

> Note|Märk: Gii tillhandahålls för användning som ett utvecklingsverktyg. Därför bör det bara installeras på en utvecklingsmaskin. 
Eftersom Gii kan generera nya PHP-skript i applikationen, bör tillräcklig uppmärksamhet ägnas åt dess säkerhet (t ex lösenord, IP-filter).

Nu kan Gii nås via URL:en `http://hostname/path/to/index.php?r=gii`. Här antas att `http://hostname/path/to/index.php` 
är URL:en som används för tillgång till den befintliga Yii-applikationen.

Om den befintliga Yii-applikationen använder URL:er enligt `path`-format (se [URL-hantering](/doc/guide/topics.url)), 
kan vi nå Gii via URL:en `http://hostname/path/to/index.php/gii`. Följande URL-regler kan behöva läggas till före redan 
befintliga URL-regler:

~~~
[php]
'components'=>array(
	......
	'urlManager'=>array(
		'urlFormat'=>'path',
		'rules'=>array(
			'gii'=>'gii',
			'gii/<controller:\w+>'=>'gii/<controller>',
			'gii/<controller:\w+>/<action:\w+>'=>'gii/<controller>/<action>',
			...existing rules...
		),
	),
)
~~~

Gii kommer med ett antal kodgeneratorer av allmänt slag. Varje kodgenerator har till 
uppgift att generera en specifik typ av kod. Till exempel genererar kontrollergeneratorn 
en kontrollerklass samt några action-vyskript; modellgeneratorn genererar en ActiveRecord-klass 
för den angivna databastabellen.

Följande är det grundläggande arbetsflödet när en generator används:

1. Gå in på generatorsidan;
2. Fyll i fälten som specificerar kodgenereringsparametrarna. Till exempel, för att använda 
modulgeneratorn till att generera en ny modul, behöver ett modul-ID anges;
3. Klicka på `Preview`-knappen för att förhandsgranska kod som kommer att genereras. 
En tabell presenteras, innehållande en förteckning över kodfiler som kommer att genereras. 
Klicka på någon av dessa för att förhandsgranska koden;
4. Klicka på knappen `Generate` för att generera kodfilerna;
5. Granska kodgenereringsloggen.


Bygga ut Gii
------------

Ävensom de standard kodgeneratorer som kommer med till Gii kan generera mycket kraftfull kod, 
vill vi ofta ha möjlighet att anpassa dem eller skapa nya efter smak och behov. 
Till exempel kan vi ha önskemålet att generera kod i vår favoritkodningsstil, 
eller att generera kod som innehåller flerspråksstöd. Allt detta kan med lätthet åstadkommas med Gii.

Gii kan byggas ut på två sätt: anpassa koden i befintliga kodgeneratorer, alternativt skriva nya kodgeneratorer.

###Struktur för en kodgenerator

En kodgenerator lagras i en katalog vars namn bildar generatorns namn. 
Katalogen har normalt följande innehåll:

~~~
model/                       the model generator root folder
   ModelCode.php             the code model used to generate code
   ModelGenerator.php        the code generation controller
   views/                    containing view scripts for the generator
      index.php              the default view script
   templates/                containing code template sets
      default/               the 'default' code template set
         model.php           the code template for generating model class code
~~~

###Sökväg för generator

Gii letar efter tillgängliga generatorer i en uppsättning kataloger som specificeras av 
propertyn [GiiModule::generatorPaths]. När anpassning behövs, kan denna property konfigureras 
i applikationskonfigurationen så som följer,

~~~
[php]
return array(
	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'generatorPaths'=>array(
				'application.gii',   // a path alias
			),
		),
	),
);
~~~

Ovanstående konfiguration instruerar Gii att leta efter generatorer i katalogen med alias `application.gii`, 
förutom att leta på standardplatsen `system.gii.generators`.

Det är möjligt att ha två generatorer med samma namn under olika sökvägar. 
I så fall kommer den generator som återfinns i sökvägen tidigare specificerad med [GiiModule::generatorPaths] 
att ges prioritet.


###Anpassning av kodmallar

Detta är det enklaste och vanligase sättet att bygga ut Gii. Ett exempel följer som förklarar hur man anpassar kodmallar. 
Antag att vi vill anpassa koden som genereras av modellgeneratorn.

Först skapar vi en katalog med namnet `protected/gii/model/templates/compact`.
Här innebär `model` att vi tänker *åsidosätta* den modellgenerator som används som standard, 
`templates/compact` innebär att vi kommer att lägga till en kodmalluppsättning med namnet `compact`.

Därefter modifierar vi vår applikationskonfiguration genom att tillfoga `application.gii` till 
[GiiModule::generatorPaths], så som visats i föregående delavsnitt.

Öppna nu sidan med modellgeneratorn. Klicka i fältet `Code Template`. En dropdown-lista innehållande vår 
nytillfogade kodmallskatalog `compact` presenteras. Dock, om vi skulle välja denna kodmall för att generera 
kod, skulle vi få se ett felmeddelande. Anledningen är att ännu återstår att lägga in faktiska kodmallsfiler 
i denna nya kodmallsuppsättning `compact`.

Kopiera filen `framework/gii/generators/model/templates/default/model.php` till `protected/gii/model/templates/compact`. 
Om vi nu åter försöker generera med mallen `compact`, kommer vi att lyckas.
Koden som genereras är dock identisk med den som genereras av `standard`-malluppsättningen.

Nu är det dags för det egentliga anpassningsarbetet. Öppna filen `protected/gii/model/templates/compact/model.php` 
för redigering. Kom ihåg att denna fil kommer att exekveras likt ett vyskript, vilket innebär att det kan innehålla 
PHP-uttryck och -satser. Låt oss modifiera mallen så att metoden `attributeLabels()` i den genererade koden använder 
`Yii::t()` för översättning av attributens etiketter:

~~~
[php]
public function attributeLabels()
{
	return array(
<?php foreach($labels as $name=>$label): ?>
			<?php echo "'$name' => Yii::t('application', '$label'),\n"; ?>
<?php endforeach; ?>
	);
}
~~~

I varje kodmall har vi tillgång till vissa fördefinierade variabler, så som `$labels` i ovanstående exempel. 
Dessa variabler tillhandahålls av den motsvarande kodgeneratorn. Olika kodgeneratorer kan tillgängliggöra skilda 
uppsättningar variabler i sina kodmallar. Vänligen läs noggrannt beskrivningarna i standardkodmallarna.


###Skapa nya kodgeneratorer

I detta delavsnitt visar vi hur man kan skapa en ny generator som kan generera en ny widget-klass.

Först skapar vi en katalog med namnet `protected/gii/widget`. I denna katalog kommer vi att lägga till följande filer:

* `WidgetGenerator.php`: innehåller kontrollerklassen `WidgetGenerator`. Detta är ingången till widgetgeneratorn.
* `WidgetCode.php`: innehåller modellklassen `WidgetCode`. Denna klass innehåller huvudsaklig logik för kodgenerering.
* `views/index.php`: vyskriptet som presenterar kodgeneratorns inmatningsformulär.
* `templates/default/widget.php`: standardkodmallen för generering av klassfil för en widget.


#### Skapa `WidgetGenerator.php`

Filen `WidgetGenerator.php` är extremt okomplicerad. Den innehåller endast följande kod:

~~~
[php]
class WidgetGenerator extends CCodeGenerator
{
	public $codeModel='application.gii.widget.WidgetCode';
}
~~~

I ovanstående kod specificerar vi att generatorn kommer att använda modellklassen vars sökvägsalias 
är `application.gii.widget.WidgetCode`. Klassen `WidgetGenerator` ärver från och utökar [CCodeGenerator] 
vilken implementerar en mängd funktionaliteter, inklusive kontrolleråtgärder (actions) som erfordras 
för koordinering av kodgenereringsprocessen.

#### Skapa `WidgetCode.php`

Filen `WidgetCode.php` innehåller modellklassen `WidgetCode` som har huvudlogiken för att generera en widgetklass 
baserat på användarens inmatning. I detta exempel antar vi att den enda erforderliga inmatningen är widgetens 
klassnamn. Vår `WidgetCode` kommer att likna följande:

~~~
[php]
class WidgetCode extends CCodeModel
{
	public $className;

	public function rules()
	{
		return array_merge(parent::rules(), array(
			array('className', 'required'),
			array('className', 'match', 'pattern'=>'/^\w+$/'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'className'=>'Widget Class Name',
		));
	}

	public function prepare()
	{
		$path=Yii::getPathOfAlias('application.components.' . $this->className) . '.php';
		$code=$this->render($this->templatepath.'/widget.php');

		$this->files[]=new CCodeFile($path, $code);
	}
}
~~~

Klassen `WidgetCode` ärver från och utökar [CCodeModel]. Liksom för en normal modellklass, 
kan vi i denna klass deklarera `rules()` och `attributeLabels()`, för uppgiften att validera användarinmatning 
respektive att tillhandahålla etiketter för attributen. Märk att eftersom modellklassen [CCodeModel] 
redan definierar vissa regler och attributetiketter, måste vi här sammanfoga dessa med våra nya regler och etiketter.

Metoden `prepare()` förbereder för koden som kommer att genereras. Dess huvudsyfte är att sammanställa en lista med 
[CCodeFile]-objekt, vart och ett representerande en kodfil att generera. I vårt exempel behöver vi bara skapa ett enda 
[CCodeFile]-objekt som representerar klassfilen för widgeten som skall genereras. Den nya widgetklassen kommer att 
genereras i katalogen `protected/components`. Vi anropar metoden [CCodeFile::render] vilken utför den faktiska 
kodgenereringen. Denna metod inkluderar kodmallen som ett PHP-skript och returnerar utmatning från detta som 
genererad kod.


#### Skapa `views/index.php`

Med tillgång till kontrollern (`WidgetGenerator`) och modellen (`WidgetCode`), är det nu dags att skapa vyn `views/index.php`.

~~~
[php]
<h1>Widget Generator</h1>

<?php $form=$this->beginWidget('CCodeForm', array('model'=>$model)); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'className'); ?>
		<?php echo $form->textField($model,'className',array('size'=>65)); ?>
		<div class="tooltip">
			Widget class name must only contain word characters.
		</div>
		<?php echo $form->error($model,'className'); ?>
	</div>

<?php $this->endWidget(); ?>
~~~

I ovanstående kod, presenterar vi ett formulär med hjälp av widgeten [CCodeForm]. I detta formulär, 
presenterar vi fältet för inhämtning av attributet `className` i `WidgetCode`.

När vi skapar formuläret kan vi dra nytta av två användbara finesser som widgeten [CCodeForm] erbjuder. 
Den ena är fältspecifika tips (tooltips). Den andra handlar om vidhäftad (sticky) inmatning.

Den som provat ut standardkodgeneratorn, har noterat att när man sätter fokus till ett inmatningsfält, 
kommer ett behändigt kortfattat tips att dyka upp i anslutning till fältet. Detta kan enkelt åstadkommas 
genom att lägga in ett `div`-element med CSS-klass `tooltip` i anslutning till inmatningsfältet.

För vissa inmatningsfält vill vi komma ihåg deras senaste giltiga innehåll, så att användare besparas 
besväret att återigen mata in värden varje gång de använder generatorn för kodgenerering. Ett exempel 
är inmatningsfältet som hämtar in kontrollerns basklassnamn i standardkontrollergeneratorn. 
Dessa vidhäftande fält presenteras initialt i form av markerad statisk text. Om vi klickar på dessa, 
ändras de till inmatningsfält.

För att deklarera ett inmatningsfält som vidhäftande, behöver vi göra två saker.

Först behöver vi deklarera en `sticky` valideringsregel för det motsvande modellattributet. 
Till exempel, standardkontrollergeneratorn har följande regel som deklarerar att attributen 
`baseClass` och `actions` skall vara vidhäftande:

~~~
[php]
public function rules()
{
	return array_merge(parent::rules(), array(
		......
		array('baseClass, actions', 'sticky'),
	));
}
~~~

För det andra, behöver vi lägga till en CSS-klass men namnet `sticky` till container-elementet `div`, 
tillhörande inmatningsfältet i vyn:

~~~
[php]
<div class="row sticky">
	...input field here...
</div>
~~~

#### Skapa `templates/default/widget.php`

Till sist skapar vi kodmallen `templates/default/widget.php`. Som tidigare beskrivits används denna som 
ett vyskript innehållande PHP-uttryck och -satser. I en kodmall är alltid `$this`-variabeln tillgänglig 
för referens till kodens modellobjekt. I vårt exempel refererar `$this` till objektet `WidgetModel`. 
Sålunda kan vi nå det användarinmatade widgetklassnamnet via `$this->className`.

~~~
[php]
<?php echo '<?php'; ?>

class <?php echo $this->className; ?> extends CWidget
{
	public function run()
	{

	}
}
~~~

Därmed är vår nyskapade kodgenerator komplett. Den kan användas omedelbart via URL:en `http://hostname/path/to/index.php?r=gii/widget`.

<div class="revision">$Id: topics.gii.txt 3223 2011-05-17 23:02:50Z alexander.makarow $</div>