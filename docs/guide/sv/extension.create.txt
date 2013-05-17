Skapa utvidgning
================

Då en utvidgning är tänkt att användas av tredjepartsutvecklare, krävs lite 
extra ansträngning när den skapas. Följande är några generella riktlinjer:

* En utvidgning skall vara komplett och oberoende. Det innebär att dess externa 
beroenden måste vara minimala. Det skulle vara en pina för dess användare om en 
utvidgning skulle kräva installation av ytterligare programpaket, klasser eller 
resursfiler. 

* Filer som tillhör en utvidgning bör organiseras under en katalog med samma namn 
som utvidgningen. 

* Klasser i en utvidgning bör ges ett (alfabetiskt) namnprefix för undvikande av 
namnkonflikt med klasser i andra utvidgningar. 

* En utvidgning bör åtföljas av detaljerad installations- och API-dokumentation. 
Detta håller nere den tid och möda som krävs av andra utvecklare när de använder 
sig av utvidgningen. 

* En utvidgning bör använda sig av en relevant licensmodell. Om utvidgningen är 
tänkt att kunna användas i projekt med öppen källkod såväl som sådana med 
proprietär kod, bör licenser som BSD, MIT etc. men inte GPL, övervägas, då den 
senare kräver att även resulterande arbeten där den ingår måste göras tillgänglig 
som öppen källkod.

I det följande beskrivs hur en ny utvidgning skapas, enligt respektive 
kategorier, beskrivna i  [översikt](/doc/guide/extension.overview). Dessa 
beskrivningar är även applicerbara på komponenter som skapas huvudsakligen för 
användning i egna projekt.

Applikationskomponent
---------------------

En [applikationskomponent](/doc/guide/basics.application#application-component) 
skall implementera gränssnittet [IApplicationComponent] eller ärva från och 
utvidga [CApplicationComponent]. Den viktigaste metoden att implementera är 
[IApplicationComponent::init] i vilken komponenten utför sina initialiseringar. 
Denna metod anropas när komponenten skapats och initiala propertyvärden 
(specificerade i 
[applikationskonfiguration](/doc/guide/basics.application#application-configuration)) 
har applicerats.

Som standard skapas och initialiseras en applikationskomponent endast när den 
anropas för första gången i samband med hantering av användarens request. Om en 
applikationskomponent behöver skapas direkt efter instansiering av 
applikationen, bör den ställa kravet på användaren att dess ID listas i 
propertyn [CApplication::preload].


Behavior
--------

För att skapa en behavior, måste man implementera ett [IBehavior] interface.
För bekvämlighets skull innehåller Yii en basklass [CBehavior] som redan 
implementerar detta interface och dessutom tillhandahåller fler passande 
metoder. Ärvda klasser behöver i huvudsak implementera de extra metoder som 
de avser att göra tillgängliga för komponenterna de skall kopplas till.

Vid utveckling av behavior för [CModel] och [CActiveRecord] kan man även utöka 
[CModelBehavior] respektive [CActiveRecordBehavior]. Dessa basklasser erbjuder 
ytterligare finesser speciellt ämnade för [CModel] och [CActiveRecord].
Till exempel, klassen [CActiveRecordBehavior] implementerar en uppsättning metoder 
som svarar på händelser som ett ActiveRecord-objekt signalerar under sin livscykel. 
En ärvd klass kan därmed åsidosätta dessa metoder och tillföra anpassad kod 
som kommer att utgöra en del av AR-objektets livscykel.

Följande kod visar ett exempel på en ActiveRecord behavior. När denna behavior har 
kopplats till ett AR-objekt och AR-objektet sparas genom anrop till `save()`, 
förser den automatiskt `create_time`- och `update_time`-attributen med en aktuell 
tidstämpel.

~~~
[php]
class TimestampBehavior extends CActiveRecordBehavior
{
	public function beforeSave($event)
	{
		if($this->owner->isNewRecord)
			$this->owner->create_time=time();
		else
			$this->owner->update_time=time();
	}
}
~~~



Widget
------

En [widget](/doc/guide/basics.view#widget) skall ärva och utvidga [CWidget] 
eller nedärvd klass.

Det enklaste sättet att skapa en ny widget är genom arv och utvidgning av en 
existerande widget med åsidosättande av dess metoder eller ändring av dess 
standardpropertyvärden. Till exempel, om en mer estetiskt tilltalande CSS-style 
önskas för [CTabView], kan dess [CTabView::cssFile]-property konfigureras när 
denna widget används. Det går även att ärva från och utvidga [CTabView] enligt 
följande så att propertyn inte behöver konfigureras.

~~~
[php]
class MyTabView extends CTabView
{
	public function init()
	{
		if($this->cssFile===null)
		{
			$file=dirname(__FILE__).DIRECTORY_SEPARATOR.'tabview.css';
			$this->cssFile=Yii::app()->getAssetManager()->publish($file);
		}
		parent::init();
	}
}
~~~

I ovanstående, ärvs och utvidgas metoden [CWidget::init] så att den tilldelar 
[CTabView::cssFile] URL:en till vår nya standard CSS-style, förutsatt att 
propertyn inte getts ett värde. Den nya filen som innehåller CSS-style placeras 
under samma katalog som innehåller klassfilen för `MyTabView`, så att de kan 
paketeras som en utvidgning. Då filen med CSS-style inte är tillgänglig för 
webbanvändare behöver den publiceras som en asset.

För att skapa en ny widget från grunden, behöver i huvudsak två metoder 
implementeras: [CWidget::init] och [CWidget::run]. Den första metoden anropas 
när `$this->beginWidget` används för att sätta in en widget i en vy, den andra 
metoden anropas som en följd av anrop till `$this->endWidget`. Om det 
presentationsinnehåll som genereras mellan anropen till dessa två metoder 
behöver fångas upp, kan [buffring av 
utmatning](http://us3.php.net/manual/en/book.outcontrol.php) startas i 
[CWidget::init] och resultatet hämtas tillbaka i [CWidget::run] för fortsatt 
bearbetning.

En widget involverar ofta CSS, JavaScript eller andra resursfiler i sidan som 
använder nämnda widget. Dessa filer kallas *assets* eftersom de förblir 
placerade tillsammans med widgetens klassfil och vanligtvis inte är tillgängliga 
för webbanvändare. För att dessa filer skall bli tillgängliga från webben 
behöver de publiceras med hjälp av [CWebApplication::assetManager], så som 
framgår av ovanstående kodsnutt. Vidare, om en CSS- eller JavaScriptfil behöver 
inkluderas i den aktuella sidan, behöver den registreras med hjälp av [CClientScript]:

~~~
[php]
class MyWidget extends CWidget
{
	protected function registerClientScript()
	{
		// ...publish CSS or JavaScript file here...
		$cs=Yii::app()->clientScript;
		$cs->registerCssFile($cssFile);
		$cs->registerScriptFile($jsFile);
	}
}
~~~

En widget kan även ha sina egna vyfiler. Om så är fallet, skapa en katalog 
`views` under katalogen som innehåller widgetens klassfil och placera alla 
vyfiler där. För att rendera en widgetvy, använd `$this->render('ViewName')`, 
snarlikt hur det görs i en kontroller.

Åtgärd
------

En [åtgärd](/doc/guide/basics.controller#action) skall ärva från och utvidga 
[CAction] eller nedärvd klass. Huvudsaklig metod att implementera för en åtgärd 
är [IAction::run].

Filter
------ 
 
Ett [filter](/doc/guide/basics.controller#filter) skall ärva från och 
utvidga [CFilter] eller nedärvd klass. Huvudsakliga metoder som behöver 
implementeras för ett filter är [CFilter::preFilter] och [CFilter::postFilter]. 
Den förra anropas innan åtgärden körs, den senare efter.

~~~
[php]
class MyFilter extends CFilter
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

Parametern `$filterChain` är av typen [CFilterChain] vilken innehåller 
information om den åtgärd som är föremål för pågående filtrering.


Kontroller
----------

En [kontroller](/doc/guide/basics.controller) distribuerad i form av en 
utvidgning skall ärva från och utvidga [CExtController] istället för 
[CController]. Det huvudsakliga skälet är att [CController] antar att 
kontrollerns vyfiler är placerade under `application.views.ControllerID`, medan 
[CExtController] antar att vyfilerna är placerade i katalogen `views`, en 
underkatalog till katalogen innehållande kontrollerns klassfil. På det sättet 
blir det lättare att vidaredistribuera kontrollern eftersom dess vyfiler förblir 
placerade i anslutning till kontrollerns klassfil.


Validator
---------

En validator skall ärva från och utvidga [CValidator] och 
implementera dess metod [CValidator::validateAttribute].

~~~
[php]
class MyValidator extends CValidator
{
	protected function validateAttribute($model,$attribute)
	{
		$value=$model->$attribute;
		if($value has error)
			$model->addError($attribute,$errorMessage);
	}
}
~~~

Konsolkommando
--------------

Ett [konsolkommando](/doc/guide/topics.console) skall ärva från och utvidga 
[CConsoleCommand] och implementera dess metod [CConsoleCommand::run]. Som 
frivillig komplettering kan [CConsoleCommand::getHelp] åsidosättas för att 
tillhandahålla användbar hjälpinformation om kommandot.

~~~
[php]
class MyCommand extends CConsoleCommand
{
	public function run($args)
	{
		// $args gives an array of the command-line arguments for this command
	}

	public function getHelp()
	{
		return 'Usage: how to use this command';
	}
}
~~~

Modul
-----
Se avsnittet om [moduler](/doc/guide/basics.module#creating-module) för 
information om hur man skapar en modul.

En generell riktlinje för utveckling av en modul är att den skall vara komplett 
i sig, samt oberoende. Resursfiler (så som CSS, JavaScript, bilder) som modulen 
använder sig av, skall distribueras tillsammans med modulen. Modulen skall även 
publicera dessa så att de blir åtkomliga för webbanvändare.

Generell komponent
------------------

Utveckling av en generell utvidgningskomponent är som att skriva en klass. 
Återigen, komponenten skall även vara komplett och oberoende så att den med 
lätthet kan användas av andra utvecklare.


<div class="revision">$Id: extension.create.txt 1423 2009-09-28 01:54:38Z qiang.xue $</div>