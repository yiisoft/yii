Component
=========

Yii-applikationer är uppbyggda av komponenter, objekt framtagna efter en 
specifikation. En komponent är en instans av [CComponent] eller en nedärvd 
klass. Användning av en komponent involverar huvudsakligen att sätta/läsa dess 
propertyn samt signalera/hantera dess event. Basklassen [CComponent] 
specificerar hur hur man definierar property och event.

Component Property
------------------

En komponents property är snarlik en publik medlemsvariabel i ett objekt. Vi kan 
läsa dess värde eller tilldela den ett värde. 
Till exempel,

~~~
[php]
$width=$component->textWidth;     // get the textWidth property
$component->enableCaching=true;   // set the enableCaching property
~~~

För att definiera en property kan vi helt enkelt definiera en publik 
medlemsvariabel i komponentklassen. Ett mer flexibelt sätt är att definiera 
getter- och setter-metoder i komponentklassen på följande sätt:

~~~
[php]
public function getTextWidth()
{
    return $this->_textWidth;
}

public function setTextWidth($value)
{
    $this->_textWidth=$value;
}
~~~

Ovanstående kodexempel definierar en skrivbar property `textWidth` (namnet är 
inte skiftlägeskänsligt). Vid läsning av propertyn kommer `getTextWidth()` att 
anropas och dess returvärde bildar propertyns värde. Likaledes, vid skrivning 
till propertyn kommer `setTextWidth()` att köras. Propertyn kan bara läsas om en 
settermetod inte definierats, försök att skriva leder till en exception. 
Användande av getter- och settermetoder för att definiera en property ger den 
extra fördelen att ytterligare logik  (t.ex. för att genomföra validering, signalera 
event) kan exekveras i samband med läsning/skrivning av propertyn.

> Note|Märk: Det finns en liten skillnad mellan en property definierad genom getter-
/settermetoder resp. som en medlemsvariabel i klassen. I det förra fallet är namnet 
skiftlägesokänsligt (case-insensitive), i det senare skiftlägeskänsligt.

Component Event
---------------

Komponenthändelser är speciella property som tar metoder (s.k. `event handlers`) 
som värde. Anslutning (tilldelning) av en metod till en event-property leder 
till att metoden automatiskt kommer att genomlöpas på de ställen där händelsen 
signaleras. Av denna anledning kan beteendet hos en komponent anpassas på sätt 
som inte var möjliga att förutse vid utvecklingen av komponenten.

En komponents händelse definieras genom att en metod definieras vars namn börjar 
med `on`. Precis som propertyn definierade med getter-/settermetoder är 
händelsers namn okänsliga för skiftläge (case-insensitive). Följande kodexempel 
definierar en `onClicked`-händelse:

~~~
[php]
public function onClicked($event)
{
	$this->raiseEvent('onClicked', $event);
}
~~~

där `$event` är en instans av [CEvent] eller en nedärvd klass, representerande 
parametern event.

Vi kan ansluta en metod till denna händelse som nedan:

~~~
[php]
$component->onClicked=$callback;
~~~

där `$callback` refererar till en giltig PHP callback i form av en global 
funktion eller en klassmetod. I det senare fallet måste denna callback ges i 
form av en array: `array($object,'methodName')`.

Signaturen för en händelsehanterare (event handler) måste se ut så här:

~~~
[php]
function methodName($event)
{
    ......
}
~~~

där `$event` är parametern som beskriver händelsen (med ursprung i 
`raiseEvent()`-anropet). `$event`-parametern är en instans av [CEvent] eller 
nedärvd klass. Som ett minimum innehåller den information om vem som signalerat 
händelsen.

En händelsehanterare kan även ha formen av en anonym funktion, vilket stöds av
PHP 5.3 eller senare. Till exempel,

~~~
[php]
$component->onClicked=function($event) {
	......
}
~~~

Om vi nu anropar `onClicked()` kommer `onClicked`-händelsen att signaleras (inuti
`onClicked()`), vilket leder till att den anslutna händelsehanteraren anropas automatiskt.

En händelse kan bli ansluten till flera händelsehanterare. Då händelsen 
signaleras kommer hanterarna att anropas i den ordning de anslutits till 
händelsen. En hanterare kan förhindra att de följande hanterarna körs genom att 
sätta [$event->handled|CEvent::handled] till true.

Komponents "Behavior"
---------------------

Komponenter stöder designmönstret [mixin](http://en.wikipedia.org/wiki/Mixin) och kan sammankopplas 
med en eller flera behavior. En  *behavior* är ett objekt vars metoder kan 
ärvas 'inherited' av dess sammankopplade komponent genom att aggregera funktionalitet 
i stället för specialisering (dvs normalt klassarv). En komponent kan sammankopplas med 
flera behavior och på så sätt åstadkomma 'multipelt arv'.

Behavior-klasser måste implementera gränssnittet [IBehavior]. De flesta behavior 
kan utvidga basklassen [CBehavior]. Om en behavior behöver sammankoplas med en 
[modell](/doc/guide/basics.model) kan den även utgå från dvs utvidga 
[CModelBehavior] eller [CActiveRecordBehavior], vilka implementerar tillkommande 
finesser specifika för modeller.

För att använda en behavior, måste den först - genom anrop till dess 
[attach()|IBehavior::attach]-metod - sammankopplas med en komponent. Därefter 
kan vi anropa en behavior-metod via komponenten.

~~~
[php]
// $name uniquely identifies the behavior in the component
$component->attachBehavior($name,$behavior);
// test() is a method of $behavior
$component->test();
~~~

En sammankopplad behavior kan adresseras precis som en normal property i 
komponenten. Till exempel, om en behavior med namnet `tree` är sammankopplad med 
en komponent, kan en referens till denna behavior erhållas på följande sätt:

~~~
[php]
$behavior=$component->tree;
// equivalent to the following:
// $behavior=$component->asa('tree');
~~~

En behavior kan tillfälligt stängas av så att dess metoder ej är tillgängliga 
via komponenten. Till exempel,

~~~
[php]
$component->disableBehavior($name);
// the following statement will throw an exception
$component->test();
$component->enableBehavior($name);
// it works now
$component->test();
~~~

Det är möjligt att två behavior sammankopplade med en och samma komponent har 
metoder med likadant namn. I så fall kommer metoden i den först sammankopplade 
behavior att ges prioritet.

När de används tillsammans med [händelser](/doc/guide/basics.component#component-event) är behavior ännu 
mer kraftfulla. En behavior kan, när den är sammankopplad med en komponent, 
koppla någon/några av sina metoder till komponentens händelser (events). En 
behavior kan, genom att göra detta, ges en chans att observera eller ändra 
komponentens normala exekveringsflöde.

En behavior:s propertyn kan även nås via komponenten den är knuten till. 
Dessa propertyn omfattar såväl publika medlemsvariabler som propertyn definierade 
via get- och/eller set-metoder i denna behavior. Till exempel, om en behavior har en property `xyz` 
och behaviorn kopplas till komponenten `$a` kan uttrycket `$a->xyz` användas för tillgång till
behaviorns property.

<div class="revision">$Id: basics.component.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>