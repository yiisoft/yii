Sökvägsalias och namnområde
===========================

Yii använder sökvägsalias extensivt. Ett sökvägsalias är associerat till en 
katalog eller filsökväg. Det specificeras med punktnotation, snarlikt detta vanligt 
förekommande namnområdesformat (namespace format):

~~~
RootAlias.path.to.target
~~~

där `RootAlias` är alias för någon befintlig katalog.

Med hjälp av [YiiBase::getPathOfAlias()] kan ett alias översättas till motsvarande 
sökväg. Till exempel, `system.web.CController` skulle översättas till 
`yii/framework/web/CController`.

Nya rotsökvägsalias kan definieras genom anrop av [YiiBase::setPathOfAlias()].


Rotalias
--------

För att underlätta fördefinierar Yii följande rot-alias:

 - `system`: refererar till Yii:s framework-katalog;
 - `zii`: refererar till [Zii-bibliotekets](/doc/guide/extension.use#zii-extensions) katalog;
 - `application`: refererar till applikationens [rotkatalog](/doc/guide/basics.application#application-base-directory);
 - `webroot`: refererar till katalogen som innehåller [startskriptet](/doc/guide/basics.entry).
 - `ext`: refererar till katalogen som innehåller alla [tredjepartstillägg](/doc/guide/extension.overview).

Om applikationen dessutom använder sig av [moduler](/doc/guide/basics.module), 
fördefinieras, för varje modul, ett rotalias med samma namn som modul-ID, 
refererande till modulens rotsökväg. Till exempel, om en applikation använder en modul 
vars ID är `users`, kommer ett rotalias med namnet `users` att fördefinieras.


Importera klasser
-----------------

Med hjälp av alias är det mycket bekvämt att importera definitionen för en klass. 
Till exempel, för att inkludera klassen [CController], kan vi göra följande anrop:

~~~
[php]
Yii::import('system.web.CController');
~~~

Metoden [import|YiiBase::import] skiljer sig från `include` och `require` genom 
att den är effektivare. Klassdefinitionen som skall importeras inkluderas inte 
förrän den refereras till första gången (implementeras via PHP:s mekanism för 
automatisk laddning). Att importera samma namnområde flera gånger är även det 
mycket snabbare än `include_once` och `require_once`.

> Tip|Tips: När en klass som definieras av Yii-ramverket refereras, behöver denna 
inte importeras eller inkluderas. Alla kärnklasser i Yii importeras i förväg.


###Använda klassmappning

Med start från version 1.1.5, tillåter Yii användardefinierade klasser att importeras 
i förväg via en klassmappningsmekanism som även används för Yii:s kärnklasser. 
I förväg importerade klasser kan användas överallt i en Yii-applikation utan att de 
explicit importeras eller inkluderas. Denna finess kommer mest till sin fördel för 
ramverk eller bibliotek som baseras på (är påbyggnader till) Yii.

För att förvägsimportera en uppsättning klasser behöver följande kod köras innan 
[CWebApplication::run()] anropas:

~~~
[php]
Yii::$classMap=array(
	'ClassName1' => 'path/to/ClassName1.php',
	'ClassName2' => 'path/to/ClassName2.php',
	......
);
~~~


Importera kataloger
-------------------

Följande syntax kan användas för att importera en hel katalog, så att 
klassfilerna i katalogen automatiskt inkluderas när så erfordras.

~~~
[php]
Yii::import('system.web.*');
~~~

Förutom [import|YiiBase::import], används alias även på många andra ställen för 
att referera till klasser. Till exempel kan ett alias lämnas med till 
[Yii::createComponent()] för att skapa en instans av motsvarande klass, även om 
klassfilen inte tidigare inkluderats.


Namnområde
---------

Ett namnområde (namespace) refererar till logisk gruppering av ett antal klassnamn så att de 
kan särskiljas från andra klasser med samma namn. Förväxla inte sökvägsalias med namnområde. 
Ett sökvägsalias är enbart ett bekvämt sätt att referera till en fil eller en katalog. 
Det har inget samband med namnområden.

> Tip|Tips: Då PHP före version 5.3.0 inte har inbyggt stöd för namnområden, går det 
inte att ha instanser av två klasser med samma namn men olika definitioner. Av 
denna anledning har alla Yii-klasser namn med prefixet 'C' (som i 'class'), så 
att de kan skiljas från användardefinierade klasser. Det rekommenderas att C-
prefixet reserveras för användning endast av Yii-ramverket, och att 
användardefinierade klasser förses med någon annan prefixbokstav.


Klasser med namnområde
----------------------

En klass med namnområde refererar till en klass deklarerad inom ett icke-globalt namnområde.
Till exempel är klassen `application\components\GoogleMap` deklarerad inom namnområdet
`application\components`. Användning av klasser med namnområde kräver PHP 5.3.0 eller senare.

Med start från version 1.1.5, är det möjligt att använda en klass med namnområde utan att 
först explicit inkludera den. Till exempel kan vi skapa en ny instans av 
`application\components\GoogleMap` utan att explicit inkludera motsvarande klassfil. 
Detta möjliggörs genom Yii:s utökade mekanismen för automatisk laddning av klasser.

För att det skall gå att automatiskt ladda en klass med namnområde, måste namnområdet 
namnges på ett sätt som liknar namngivning av ett sökvägsalias.
Till exempel klassen `application\components\GoogleMap` måste lagras i en fil som 
kan ges alias `application.components.GoogleMap`.


<div class="revision">$Id: basics.namespace.txt 3086 2011-03-15 00:04:53Z qiang.xue $</div>