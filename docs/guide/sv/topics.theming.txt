Teman
=====

Användning av teman är ett systematiskt sätt att anpassa utseendet hos sidor i 
en webbapplikation. Genom att applicera ett annat tema, kan man omedelbart 
dramatiskt förändra det övergripande utseendet hos en webbapplikation.

I Yii representeras varje tema av en katalog innehållande vyfiler, layoutfiler 
samt relevanta resursfiler så som bilder, CSS-filer, JavaScript-filer etc. 
Namnet på ett tema är namnet på dess katalog. Alla teman huserar under samma 
katalog, `WebRoot/themes`. Vid varje tillfälle kan endast ett av dessa vara 
aktivt.

> Tip|Tips: Standardrotkatalogen för teman, `WebRoot/themes`, kan konfigureras 
till en anan katalog. Konfigurera bara applikationskomponenten 
[themeManager|CWebApplication::themeManager]:s propertyn 
[basePath|CThemeManager::basePath] och [baseUrl|CThemeManager::baseUrl] efter 
önskemål.


Using a Theme
-------------

För att aktivera ett tema, sätt webbapplikationens property 
[theme|CWebApplication::theme] till namnet på det önskade temat. 
Detta kan antingen göras i 
[applikationskonfigurationen](/doc/guide/basics.application#application-configuration) 
eller vid körning, i en kontrolleråtgärd.

> Note|Märk: Temanamn är skiftlägeskänsliga (case-sensitive). Om man försöker 
att aktivera ett tema som inte kan hittas returnerar `Yii::app()->theme` `null`.


Creating a Theme
----------------

Innehåll under temats katalog skall organiseras på samma sätt som det under 
[applikationens rotkatalog](/doc/guide/basics.application#application-base-directory). 
Till exempel, alla vyfiler måste placeras under `views`, 
layoutvyfiler under `views/layouts` och systemvyfiler under `views/system`. 
Om vi till exempel vill ersätta vyn `create` tillhörande `PostController` 
med en vy ur temat `classic`, skall den nya vyfilen sparas som 
`WebRoot/themes/classic/views/post/create.php`.

För vyer som tillhör kontrollrar i en [modul](/doc/guide/basics.module), skall även 
motsvarande vyfiler som tillhör teman placeras under katalogen `views`.
Till exempel, om tidigare nämnda `PostController` återfinns i modulen `forum`, 
skall vyfilen `create` sparas som `WebRoot/themes/classic/views/forum/post/create.php`. 
Om modulen `forum` är nästlad i en annan modul, `support`, skall vyfilen istället sparas 
som `WebRoot/themes/classic/views/support/forum/post/create.php`.

> Note|Märk: Eftersom katalogen `views` kan innehålla ur säkerhetssynpunkt känslig data, 
skall den konfigureras för att förhindra webbanvändare tillgång.

Vid anrop till [render|CController::render] eller 
[renderPartial|CController::renderPartial] för att presentera en vy, kommer 
motsvarande vyfil samt layoutfil att eftersökas i det för tillfället aktiva 
temat. Om de hittas används de för renderingen. I annat fall används 
standardplatserna för vyer och layouter specificerade i 
[viewPath|CController::viewPath] respektive 
[layoutPath|CWebApplication::layoutPath].

> Tip|Tips: Inuti en temavy, behöver ofta länkning ske till andra 
temaresursfiler. Till exempel, kan vi vilja visa en bildfil under temats katalog 
`images`. Genom att använda det för tillfället aktiva temats property 
[baseUrl|CTheme::baseUrl], kan en URL för bilden genereras på följande sätt, 

> ~~~ 
> [php] 
> Yii::app()->theme->baseUrl . '/images/FileName.gif' 
> ~~~ 

Nedan följer ett exempel på katalogstruktur för en applikation med två teman, `basic` och `fancy`.

~~~
WebRoot/
	assets
	protected/
		.htaccess
		components/
		controllers/
		models/
		views/
			layouts/
				main.php
			site/
				index.php
	themes/
		basic/
			views/
				.htaccess
				layouts/
					main.php
				site/
					index.php
		fancy/
			views/
				.htaccess
				layouts/
					main.php
				site/
					index.php
~~~

Om vi lägger in följande i applikationskonfigurationen

~~~
[php]
return array(
	'theme'=>'basic',
	......
);
~~~

kommer temat `basic` att gälla, vilket innebär att applikationen hämtar sin layout från 
katalogen `themes/basic/views/layouts`, samt sin indexvy från `themes/basic/views/site`. 
I händelse av att vyfilen inte hittas i temat, används filen från katalogen `protected/views`.


Theming Widgets
---------------

Med start från version 1.1.5 kan teman användas även för vyer som används av widgetar. 
Närmare bestämt kommer Yii att, när vi anropar [CWidget::render()] för att rendera en widgetvy, 
att leta såväl i temakatalogen som i widgetens vykatalog, efter den önskade vyfilen.

För att temasätta vyn `xyz` tillhörande en widget vars klassnamn är `Foo`, skapar vi först en katalog 
med namnet `Foo` (dvs samma som widgetens klassnamn) i det aktiva temats vykatalog. Om widgetklassen 
 tillhör ett namespace (tillgängligt i PHP 5.3.0 eller senare), så som `\app\widgets\Foo`, skall en 
 katalog med namnet `app_widgets_Foo` skapas. Vi byter alltså ut namespace-separerare mot understreck.

Därefter skapar vi en vyfil benämnd `xyz.php` i den just skapade katalogen. Vi har nu en fil 
`themes/basic/views/Foo/xyz.php` som, givet att det för tillfället aktiva temat är `basic`, 
kommer att användas av widgeten istället för dess originalvy.


Anpassa widgetar globalt
------------------------

> Note|Märk: denna finess har varit tillgänglig fr o m version 1.1.3.

När en widget från tredjepart eller Yii används, behöver vi ofta anpassa den
för specifika behov. Till exempel kan vi vilja ändra värdet för
[CLinkPager::maxButtonCount] från 10 (standard) till 5. Detta kan åstadkommas 
genom att initialvärden lämnas med när [CBaseController::widget]
anropas för att skapa en widget. Detta kan dock bli bekymmersamt då det måste 
upprepas på varje ställe där [CLinkPager] används.

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
    'maxButtonCount'=>5,
    'cssFile'=>false,
));
~~~

Genom användning av finessen med global anpassning av widgetar, behöver vi bara 
specificera dessa initialvärden på ett enda ställe, nämligen  i applikationskonfigurationen. 
Detta gör anpassningen av widgetar mer hanterbar.

För att använda finessen med global anpassning av widgetar, behöver vi konfigurera 
[widgetFactory|CWebApplication::widgetFactory] på följande sätt:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'widgets'=>array(
                'CLinkPager'=>array(
                    'maxButtonCount'=>5,
                    'cssFile'=>false,
                ),
                'CJuiDatePicker'=>array(
                    'language'=>'ru',
                ),
            ),
        ),
    ),
);
~~~

I ovanstående exempel, specificeras den globala widgetkonfigurationen för både 
[CLinkPager] och [CJuiDatePicker] genom konfigurering av propertyn [CWidgetFactory::widgets]. 
Märk att den globala konfigurationen för respektive widget representeras som ett 
nyckel-värdepar i arrayen, där nyckeln refererar till widgetens klassnamn 
medan värdet specificerar en array med initialvärden för propertyn.

Nu kommer, närhelst vi skapar en [CLinkPager]-widget i en vy, ovanstående propertyvärden 
att tilldelas widgeten, vi behöver alltså bara skriva följande kod i vyn för att skapa 
widgeten:

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
));
~~~

Det är fortfarande möjligt att åsidosätta initialvärden för propertyn om så erfordras. 
Till exempel, om vi i någon vy vill sätta `maxButtonCount` till 2, kan detta göras som följer:

~~~
[php]
$this->widget('CLinkPager', array(
	'pages'=>$pagination,
	'maxButtonCount'=>2,
));
~~~


Skin
----

Medan användning av teman snabbt kan få vyer att ändra skepnad, kan skin användas 
för att systematiskt anpassa utseendet hos [widgetar](/doc/guide/basics.view#widget) som används i vyer.

En skin består av en array av namn-värdepar som kan användas för att initialisera propertyn i en widget. 
En skin hör till en widgetklass och en widgetklass kan ha flera skin vilka identifieras av dess namn. 
Vi kan till exempel ha en skin benämnd `classic`, tillhörande widgeten [CLinkPager].

För att skin-finessen skall kunna användas måste först applikationskonfigurationen modifieras genom  
att propertyn [CWidgetFactory::enableSkin] för applikationskomponenten `widgetFactory` sätts till true:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'enableSkin'=>true,
        ),
    ),
);
~~~

Vänligen lägg märke till att i versioner före 1.1.3, behöver följande konfiguration 
användas för att aktivera användning av widget skin:

~~~
[php]
return array(
    'components'=>array(
        'widgetFactory'=>array(
            'class'=>'CWidgetFactory',
        ),
    ),
);
~~~

Därefter kan önskade skin skapas. Skin som tillhör samma widgetklass lagras i ett gemensamt 
PHP-skript med samma namn som widgetklassen. Samtliga dessa skinfiler lagras som standard 
i katalogen `protected/views/skins`. Detta kan ändras till en annan katalog genom konfigurering 
av propertyn `skinPath` i komponenten `widgetFactory`. Exempelvis en fil `CLinkPager.php`, 
med nedanstående innehåll, skapas i katalogen `protected/views/skins`,

~~~
[php]
<?php
return array(
    'default'=>array(
        'nextPageLabel'=>'&gt;&gt;',
        'prevPageLabel'=>'&lt;&lt;',
    ),
    'classic'=>array(
        'header'=>'',
        'maxButtonCount'=>5,
    ),
);
~~~

I ovanstående exempel skapas två skin för widgeten [CLinkPager]: `default` och `classic`. 
Den förra är den skin som kommer att åsättas varje [CLinkPager] widget där `skin`-propertyn
inte uttryckligen specificerats. Den senare är den skin som åsätts [CLinkPager]-widgetar 
där `skin`-propertyn specificerats som `classic`. I följande vykod kommer den första 
pager-instansen att använda `default`-skin, medan den andra använder `classic`-skin:

~~~
[php]
<?php $this->widget('CLinkPager'); ?>

<?php $this->widget('CLinkPager', array('skin'=>'classic')); ?>
~~~

Om vi skapar en widget med en uppsättning propertyn med givna initialvärden, kommer dessa 
att få prioritet och läggas till varje relevant skin. Till exempel kommer följande vykod 
att skapa en pager vars initialvärden kommer att vara `array('header'=>'', 'maxButtonCount'=>6, 'cssFile'=>false)`,
ett resultat av sammanslagna initialvärden specificerade i vyn respektive i `classic`-skin.

~~~
[php]
<?php $this->widget('CLinkPager', array(
    'skin'=>'classic',
    'maxButtonCount'=>6,
    'cssFile'=>false,
)); ?>
~~~

Lägg märke till att skin-finessen INTE kräver att teman används. Skulle dock ett tema vara aktivt 
kommer Yii även att söka efter skin i underkatalogen `skins` till temats vykatalog 
(t.ex. `WebRoot/themes/classic/views/skins`). I händelse av att en skin med samma namn existerar 
i både temats och huvudapplikationens vykataloger, prioriteras temats skin.

Om en widget använder en skin som inte existerar kommer Yii fortfarande att skapa widgeten som vanligt, 
utan felmeddelanden.

> Info: Användning av skin kan sätta ned prestanda eftersom Yii behöver leta efter skin-filen första gången en widget skapas.

Skin är mycket snarlikt finessen global anpassning av widget. De huvudsakliga skillnaderna 
är följande.

   - Skin är mer relaterat till anpassning av propertyvärden för presentation;
   - En widget kan ha flera skin;
   - Skin kan åsättas via teman;
   - Användning av skin är mer resurskrävande än global anpassning av widget.

<div class="revision">$Id: topics.theming.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>