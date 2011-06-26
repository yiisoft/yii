View
====

En vy (view) är ett PHP-skript huvudsakligen bestående av element som hör till 
användargränssnittet. Vyn kan innehålla PHP-satser, men det rekommenderas att 
dessa satser inte ändrar datamodeller samt att de förblir relativt enkla. I 
andan av separation mellan programlogik och presentation, bör stora block av 
programlogik placeras i kontroller eller modeller hellre än i vyer.

En vy har ett namn avsett att identifiera vyns skriptfil vid rendering. Vyn och 
dess skriptfil har samma namn. Till exempel, vynamnet `edit` refererar till en 
vyskriptfil med namnet `edit.php`. En vy kan renderas genom anrop till 
[CController::render()] med vyns namn bifogat. Metoden kommer att söka efter 
motsvarande vyfil i katalogen `protected/views/ControllerID`.

I vyns skriptfil kan kontrollerinstansen adresseras med hjälp av `$this`. Vi kan 
därför hämta in (`pull`) varje egenskap (property) från kontrollern genom att utvärdera 
`$this->propertyName` i vyn.

Vi kan också trycka ut (`push`) data till vyn:

~~~
[php]
$this->render('edit', array(
	'var1'=>$value1,
	'var2'=>$value2,
));
~~~

I ovanstående exempel kommer metoden [render()|CController::render] att 
extrahera arrayen i parameter 2 till variabler. Resultatet blir att vi i 
vyskriptet kan använda de lokala variablerna `$var1` och `$var2`.

Layout
------

Layout är en speciell vy som används för att dekorera vyer. Den innehåller 
vanligtvis delar av användargränssnittet vilka är gemensamma för många vyer. 
Till exempel kan en layout innehålla sidhuvud och sidfot samt på 
följande sätt bädda in vyn däremellan:

~~~
[php]
......header here......
<?php echo $content; ?>
......footer here......
~~~

där `$content` innehåller renderingsresultatet för vyn.

Layout blir underförstått applicerad vid anrop till 
[render()|CController::render]. Som standard används vyskriptet 
`protected/views/layouts/main.php` som layout. Detta kan anpassas genom att 
ändra antingen [CWebApplication::layout] eller [CController::layout]. Använd 
[renderPartial()|CController::renderPartial] för rendering av en vy utan att 
applicera någon layout.

Widget
------

En widget är en instans av [CWidget] eller av en nedärvd klass. 
Den är en komponent huvudsakligen avsedd för presentation. 
Widget bäddas vanligen in i vyskript för att generera något 
komplext, men ändå komplett och oberoende, användargränssnitt. 
Till exempel kan en kalenderwidget användas till att rendera 
ett avancerat kalendergränssnitt. Widget möjliggör bättre 
återanvändningsbarhet av användargränssnittets kod.

För att använda en widget, gör så här i ett vyskript:

~~~
[php]
<?php $this->beginWidget('path.to.WidgetClass'); ?>
...body content that may be captured by the widget...
<?php $this->endWidget(); ?>
~~~

eller

~~~
[php]
<?php $this->widget('path.to.WidgetClass'); ?>
~~~

Det senare används när widget:en inte behöver omfatta innehåll (body content).

Widgetar kan konfigureras för anpassning av deras beteende. Detta sker genom att 
man sätter dess initiala propertyvärden vid anrop till 
[CBaseController::beginWidget] eller [CBaseController::widget]. Till exempel, 
vid användning av en [CMaskedTextField] widget, kan vi vilja specificera 
vilken mask som skall användas. Detta kan göras genom att på följande sätt 
lämna med en vektor (array) innehållande initialvärden för widget-egenskaper 
(property), där vektorns nycklar är egenskapsnamn och vektorns värden är 
initialvärden för widgetens egenskaper:

~~~
[php]
<?php
$this->widget('CMaskedTextField',array(
	'mask'=>'99/99/9999'
));
?>
~~~

För att definiera en ny widget, ärv en underklass från [CWidget] och åsidosätt metoderna
[init()|CWidget::init] och [run()|CWidget::run] med egna:

~~~
[php]
class MyWidget extends CWidget
{
	public function init()
	{
		// this method is called by CController::beginWidget()
	}

	public function run()
	{
		// this method is called by CController::endWidget()
	}
}
~~~

En widget kan. liksom en kontroller, ha sin egen vy. Som standard har en widget 
sina vyfiler placerade i underkatalogen `views` till katalogen som innehåller 
widgetens klassfil. Dessa vyer kan renderas genom anrop till 
[CWidget::render()], ungefär som med en kontroller. Den enda skillnaden är att 
ingen layout appliceras på en widgetvy. `$this` i vyn refererar till widgetens 
instans i stället för till kontrollerinstansen.

Systemvy
--------

Systemvyer refererar till de vyer som Yii använder för att presentera 
felmeddelanden och loggningsinformation. Till exempel, när en användare skickar 
en begäran gällande en icke-existerande kontroller eller åtgärd, kommer Yii 
att signalera en exception som förklarar felet. Yii presenterar en exception med 
hjälp av en specifik systemvy.

Namn på systemvyer följer vissa regler. Namn i stil med `errorXXX` avser vyer 
för presentation, [CHttpException] med felkod `XXX`. Till exempel, om 
[CHttpException] signaleras med felkod 404, så kommer vyn `error404` att visas.

Yii erbjuder en standarduppsättning systemvyer placerade under 
`framework/views`. Dessa kan anpassas genom att vyfiler med samma namn placeras 
under `protected/views/system`.

<div class="revision">$Id: basics.view.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>