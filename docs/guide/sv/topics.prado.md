Alternativ mallsyntax
=====================

Yii tillåter urvecklare att använda sin egen favoritmallsyntax (t.ex. Prado, 
Smarty) för att skriva kontroller- eller widgetvyer. Detta uppnås genom att 
skriva och installera en applikationskomponent, 
[viewRenderer|CWebApplication::viewRenderer]. Vyrenderaren fångar upp 
invokeringar av [CBaseController::renderFile], kompilerar vyfilen med anpassad 
mallsyntax, och renderar de kompilerade resultaten.

> Info: Det rekommenderas att anpassad mallsyntax bara används till att skriva 
vyer som med liten sannolikhet kommer att återanvändas. I annat fall kommer 
personer som återanvänder vyerna att tvingas använda samma anpassade mallsyntax i 
sina applikationer.

I det följande, introduceras hur man använder [CPradoViewRenderer], en 
vyrenderare som tillåter utvecklare att använda en mallsyntax snarlik den i 
[Prado-ramverket](http://www.pradosoft.com/). För personer som vill utveckla 
sina egna vyrenderare, är [CPradoViewRenderer] en bra referens.

Använda `CPradoViewRenderer`
----------------------------

För att använda [CPradoViewRenderer], behöver man konfigurera applikationen som följer:

~~~
[php]
return array(
	'components'=>array(
		......,
		'viewRenderer'=>array(
			'class'=>'CPradoViewRenderer',
		),
	),
);
~~~

Som standard, kommer [CPradoViewRenderer] att kompilera vyfilernas källkod och 
spara de resulterande PHP-filerna i 
[runtime](/doc/guide/basics.convention#directory)-katalogen. bara när vyfilernas 
källkod har ändrats, kommer PHP-filerna att genereras på nytt. Därför åsamkar 
[CPradoViewRenderer] mycket liten prestandaförlust.

> Tip|Tips: Medan [CPradoViewRenderer] huvudsakligen introducerar ett antal nya 
mall-taggar för att göra det lättare och snabbare att skriva vyer, kan man 
fortfarande skriva PHP-kod precis som vanligt i vyernas källkod.

I det följande introduceras de mall-taggar som stöds av [CPradoViewRenderer].

### Korta PHP-taggar

Korta PHP-taggar är kortformer för att skriva PHP-uttryck och -satser i en vy. 
Uttrycks-taggen `<%= expression %>` översätts till `<?php echo expression ?>`; 
medan sats-taggen `<% statement %>` översätts till `<?php statement ?>`. Till 
exempel,

~~~
[php]
<%= CHtml::textField($name,'value'); %>
<% foreach($models as $model): %>
~~~

översätts till

~~~
[php]
<?php echo CHtml::textField($name,'value'); ?>
<?php foreach($models as $model): ?>
~~~

### Komponent-taggar

Komponent-taggar används för att infoga en 
[widget](/doc/guide/basics.view#widget) i en vy. De använder följande syntax:

~~~
[php]
<com:WidgetClass property1=value1 property2=value2 ...>
	// body content for the widget
</com:WidgetClass>

// a widget without body content
<com:WidgetClass property1=value1 property2=value2 .../>
~~~

där `WidgetClass` specificerar widgetklassens namn eller 
[sökvägsalias](/doc/guide/basics.namespace), och där initialvärden för propertyn 
kan bestå av antingen strängar mellan citationstecken eller PHP-uttryck omgivna 
av ett par krumparenteser. Till exempel,

~~~
[php]
<com:CCaptcha captchaAction="captcha" showRefreshButton={false} />
~~~

översätts till

~~~
[php]
<?php $this->widget('CCaptcha', array(
	'captchaAction'=>'captcha',
	'showRefreshButton'=>false)); ?>
~~~

> Note|Märk: Värdet på `showRefreshButton` specificeras till `{false}` i stället 
för `"false"` eftersom det senare innebär en sträng i stället för ett boolskt 
värde.

### Cache-taggar

Cache-taggar är kortformer för att använda 
[fragmentcachning](/doc/guide/caching.fragment). Deras syntax är som följer,

~~~
[php]
<cache:fragmentID property1=value1 property2=value2 ...>
	// content being cached
</cache:fragmentID >
~~~

där `fragmentID` skall vara en identifierare som oförväxelbart identifierar 
innehållet som cachas, och där property-värdeparen används till att konfigurera 
fragmentcachen. Till exempel,

~~~
[php]
<cache:profile duration={3600}>
	// user profile information here
</cache:profile >
~~~

översätts till

~~~
[php]
<?php if($this->beginCache('profile', array('duration'=>3600))): ?>
	// user profile information here
<?php $this->endCache(); endif; ?>
~~~

### Urklipps-taggar

Snarlikt cache-taggar är urklipps-taggar kortformer för anrop till 
[CBaseController::beginClip] och [CBaseController::endClip] i en vy. Syntaxen är 
som följer,

~~~
[php]
<clip:clipID>
	// content for this clip
</clip:clipID >
~~~

Där `clipID` är en identifierare som oförväxelbart identifierar klippinnehållet.
Urklipps-taggarna översätts till

~~~
[php]
<?php $this->beginClip('clipID'); ?>
	// content for this clip
<?php $this->endClip(); ?>
~~~

### Kommentars-taggar

Kommentars-taggar används för att skriva vykommentarer som endast skall vara 
synliga för utvecklare. Kommentar-taggar kommer att vara borttagna när vyerna 
presenteras för slutanvändare. Syntaxen för kommentars-taggar är som följer,

~~~
[php]
<!---
view comments that will be stripped off
--->
~~~

Blandning av mallformat
-----------------------

Med start från version 1.1.2 är det möjligt att blanda användning av någon alternativ 
mallsyntax med vanlig PHP-syntax. För att göra så måste propertyn [CViewRenderer::fileExtension]
i den installerade vyrenderaren konfigureras med ett värde skilt från `.php`. 
Till exempel om propertyn är satt till `.tpl` kommer varje vyfil med filtillägget `.tpl` 
att renderas med hjälp av den installerade vyrenderaren, medan alla andra vyfiler med filtillägget 
`.php` kommer att behandlas som vanliga PHP-vyskript.


<div class="revision">$Id: topics.prado.txt 3226 2011-05-18 10:37:47Z mdomba $</div>