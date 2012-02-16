Använda Utvidgning
==================

Användning av en utvidgning involverar vanligtvis följande tre steg:

  1. Ladda ned utvidgningen från Yii:s 
  [förråd av utvidgningar](http://www.yiiframework.com/extensions/).
     
  2. Packa upp utvidgningen i underkatalogen `extensions/xyz` till
     [applikationens rotkatalog](/doc/guide/basics.application#application-base-directory),
     där `xyz` står för utvidgningens namn.
     
  3. Importera, konfigurera och använd utvidgningen.

Varje utvidgning har ett namn som unikt identifierar den bland alla 
utvidgningar. Givet en utvidgning med namnet `xyz`, kan alltid aliassökvägen 
`ext.xyz` användas för att lokalisera dess rotkatalog, vilken innehåller 
alla filer som tillhör `xyz`.

Olika utvidgningar har varierande krav kring import av konfiguration samt 
användning. I det följande sammanfattas vanliga användningsfall för 
utvidgningar, enligt respektive kategorier, beskrivna i 
[översikt](/doc/guide/extension.overview).

Zii-utvidgningar
----------------

Innan vi börjar beskriva användning av tredjepartstillägg vill vi introducera 
utvidgningsbiblioteket Zii, vilket består av en uppsättning tillägg framtagna av Yii:s 
team av utvecklare och kommer att följa med i varje release.

För att använda Zii-tillägg måste man referera till motsvarande klass med hjälp av ett 
sökvägsalias på formen `zii.path.to.ClassName`. Rotalias `zii` är fördefinierat av Yii. 
Det refererar till Zii-biblioteks rotkatalog. Till exempel, i fråga om [CGridView] 
skulle vi använda följande kod i ett vyskript för att referera till tillägget:

~~~
[php]
$this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$dataProvider,
));
~~~


Applikationskomponent
---------------------

För att använda en [applikationskomponent](/doc/guide/basics.application#application-component),
behöver först ett nytt värde läggas till i 
[applikationskonfigurationen](/doc/guide/basics.application#application-configuration),
närmare bestämt i dess `components`-property, enligt följande:

~~~
[php]
return array(
    // 'preload'=>array('xyz',...),
    'components'=>array(
        'xyz'=>array(
            'class'=>'ext.xyz.XyzClass',
            'property1'=>'value1',
            'property2'=>'value2',
        ),
        // other component configurations
    ),
);
~~~

Därefter kan komponenten kommas åt från valfri plats i koden med hjälp av 
`Yii::app()->xyz`. Komponenten kommer att skapas enligt tillvägagångssättet lazy 
(vilket innebär att den skapas när den refereras till för första gången), såvida 
den inte listas i propertyn `preload`.


Behavior
--------

[Behavior](/doc/guide/basics.component#component-behavior) kan användas i alla 
slags komponenter. Dess användning omfattas av två steg. I det första steget 
kopplas en behavior till en målkomponent. I det andra steget anropas en 
behaviormetod via målkomponenten. Till exempel:

~~~
[php]
// $name uniquely identifies the behavior in the component
$component->attachBehavior($name,$behavior);
// test() is a method of $behavior
$component->test();
~~~

Mer vanligt är att koppla en behavior till en komponent genom konfiguration, 
istället för anrop till metoden `attachBehavior`. Till exempel, för att koppla 
en behavior till en 
[applikationskomponent](/doc/guide/basics.application#application-component)
kan följande
[applikationskonfiguration](/doc/guide/basics.application#application-configuration)
användas:

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'CDbConnection',
			'behaviors'=>array(
				'xyz'=>array(
					'class'=>'ext.xyz.XyzBehavior',
					'property1'=>'value1',
					'property2'=>'value2',
				),
			),
		),
		//....
	),
);
~~~

Ovanstående kod kopplar behavior:n `xyz` till applikationskomponenten `db`. 
Detta låter sig göras eftersom [CApplicationComponent] definierar propertyn 
`behaviors`. Genom att sätta denna property till en lista med 
behaviorkonfigurationer kommer komponenten, när den initialiseras, 
att koppla alla motsvarande behavior.

För klasserna [CController], [CFormModel] och [CActiveRecord], vilka vanligtvis 
behöver utökas, sker koppling av behavior genom att deras respektive metod 
`behaviors()` åsidosätts och omdefinieras.  I samband med initialiseringen 
kommer klasserna att automatiskt bindas till varje behavior som deklarerats i 
nämnda metod. Till exempel,

~~~
[php]
public function behaviors()
{
	return array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzBehavior',
			'property1'=>'value1',
			'property2'=>'value2',
		),
	);
}
~~~


Widget
------

[Widgetar](/doc/guide/basics.view#widget) används huvudsakligen i 
[vyer](/doc/guide/basics.view). Givet en widgetklass `XyzClass` tillhörande 
utvidgningen `xyz`, kan denna användas i en vy på följande sätt,

~~~
[php]
// widget that does not need body content
<?php $this->widget('ext.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

// widget that can contain body content
<?php $this->beginWidget('ext.xyz.XyzClass', array(
    'property1'=>'value1',
    'property2'=>'value2')); ?>

...body content of the widget...

<?php $this->endWidget(); ?>
~~~

Åtgärd
------

[Åtgärder](/doc/guide/basics.controller#action) används av en 
[kontroller](/doc/guide/basics.controller) till att agera till följd av en 
specifik request från användare. Givet en åtgärdsklass `XyzClass` tillhörande 
utvidgningen `xyz`, kan denna användas genom åsidosättande av kontrollerklassens 
metod [CController::actions]:

~~~
[php]
class TestController extends CController
{
	public function actions()
	{
		return array(
			'xyz'=>array(
				'class'=>'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// other actions
		);
	}
}
~~~

Därefter blir åtgärden tillgänglig via 
[route](/doc/guide/basics.controller#route) `test/xyz`.

Filter
------

[Filter](/doc/guide/basics.controller#filter) används även de av en 
[kontroller](/doc/guide/basics.controller). De för- och efterbearbetar en 
request från användare i samband med att den hanteras av en 
[åtgärd](/doc/guide/basics.controller#action). Givet en filterklass `XyzClass` 
tillhörande utvidgningen `xyz`, kan denna användas genom åsidosättande av 
kontrollerklassens metod [CController::filters]:

~~~
[php]
class TestController extends CController
{
	public function filters()
	{
		return array(
			array(
				'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// other filters
		);
	}
}
~~~

I ovanstående kan plus- och minusoperatorer i det första arrayelementet användas 
för att applicera filtret på endast en delmängd av åtgärder. För fler detaljer, 
se dokumentationen för [CController].

Kontroller
----------

En [kontroller](/doc/guide/basics.controller) tillhandahåller en uppsättning 
åtgärder som kan begäras av användare. För att använda en kontrollerutvidgning, 
behöver propertyn [CWebApplication::controllerMap] konfigureras i 
[applikationskonfigurationen](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	'controllerMap'=>array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// other controllers
	),
);
~~~

Därefter kan en åtgärd `a` i kontrollern kommas åt via en [route](/doc/guide/basics.controller#route) `xyz/a`.

Validator
---------

En validator används huvudsakligen i en [modell](/doc/guide/basics.model)-klass 
(en som ärver från och utvidgar antingen [CFormModel] eller [CActiveRecord]). 
Givet en validatorklass `XyzClass` tillhörande utvidgningen `xyz`, kan denna 
användas genom åsidosättande av modellklassens metod [CModel::rules]:

~~~
[php]
class MyModel extends CActiveRecord // or CFormModel
{
	public function rules()
	{
		return array(
			array(
				'attr1, attr2',
				'ext.xyz.XyzClass',
				'property1'=>'value1',
				'property2'=>'value2',
			),
			// other validation rules
		);
	}
}
~~~

Konsolkommando
--------------

En [konsolkommando](/doc/guide/topics.console)-utvidgning berikar vanligtvis 
verktyget `yiic` med ytterligare ett kommando. Givet ett konsolkommando 
`XyzClass` tillhörande utvidgningen `xyz`, kan detta användas genom att 
konfigurera konfigurationen för en konsolapplikation:

~~~
[php]
return array(
	'commandMap'=>array(
		'xyz'=>array(
			'class'=>'ext.xyz.XyzClass',
			'property1'=>'value1',
			'property2'=>'value2',
		),
		// other commands
	),
);
~~~

Därefter är verktyget `yiic` försett med ytterligare ett kommando `xyz`.

> Note|Märk: En konsolapplikation använder vanligen en konfigurationsfil som 
skiljer sig från den som används för en webbapplikation. Om en applikation 
skapas med hjälp av kommandot `yiic webapp`, blir konfigurationsfilen för 
konsolapplikationen `protected/yiic` `protected/config/console.php`, medan 
konfigurationsfilen för webbapplikationen blir `protected/config/main.php`.

Modul
-----

Se avsnittet om [moduler](/doc/guide/basics.module#using-module) för 
information om hur man använder en modul.

Generell komponent
------------------

För att använda en generell [komponent](/doc/guide/basics.component), behöver först dess klassfil inkluderas genom:

~~~
Yii::import('ext.xyz.XyzClass');
~~~

Sedan kan en instans av klassen skapas, dess propertyn konfigureras och dess 
metoder anropas. Man kan även ärva och utvidga komponentens klass.


<div class="revision">$Id: extension.use.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>