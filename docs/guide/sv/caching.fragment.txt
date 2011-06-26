Fragmentcachning
================

Med fragmentcachning menas cachning av fragment av en sida. Till exempel, om en 
sida presenterar en sammanställning av årlig försäljning i form av en tabell, 
kan tabellen lagras i cacheminne, vilket sparar in tiden som skulle gått åt till 
att generera den vid varje request.

För att använda fragmentcachning, anropa 
[CController::beginCache()|CBaseController::beginCache()] och 
[CController::endCache()|CBaseController::endCache()] i en kontrollers vyskript. 
Dessa två metoder markerar början respektive slutet av det sidinnehåll som skall 
cachelagras. Liksom vid [datacachning](/doc/guide/caching.data), behövs ett ID 
för att identifiera det cachade fragmentet.

~~~
[php]
...annat HTML-innehåll...
<?php if($this->beginCache($id)) { ?>
...innehåll som skall cachelagras...
<?php $this->endCache(); } ?>
...annat HTML-innehåll...
~~~

Om [beginCache()|CBaseController::beginCache()] ovan returnerar false, kommer 
det cachelagrade fragmentet att automatisk sättas in på plats; i annat fall 
kommer innehåll som omsluts av `if`-satsen att exekveras och cachelagras vid 
anrop till [endCache()|CBaseController::endCache()].

Cache-alternativ
----------------

Vid anrop till [beginCache()|CBaseController::beginCache()], kan en array 
innehållande cache-alternativ för anpassning av fragmentcachningen lämnas som 
andra parameter. Faktum är att metoderna 
[beginCache()|CBaseController::beginCache()] och 
[endCache()|CBaseController::endCache()] bildar en ändamålsenlig wrapper kring 
widget:en [COutputCache]. Av denna anledning kan cachningsalternativen utgöra 
initialvärden för varje property hos [COutputCache].

### Livslängd

Det kanske vanligaste alternativet är [duration|COutputCache::duration], som 
specificerar hur länge cachelagrat innehåll kan förbli giltigt. Det är 
närbesläktat med expiration-parametern hos [CCache::set()]. Följande kod 
cachelagrar innehållsfragmentet i upp till en timme:

~~~
[php]
...annat HTML-innehåll...
<?php if($this->beginCache($id, array('duration'=>3600))) { ?>
...innehåll som skall cachelagras...
<?php $this->endCache(); } ?>
...annat HTML-innehåll...
~~~

Om duration inte anges, antar den standardvärdet 60, vilket innebär att det 
cachelagrade innehållet blir ogiltigt efter 60 sekunder.

Med start från version 1.1.8, gäller att om duration sätts till 0, kommer allt 
befintligt cachelagrat innehåll att tas bort från cache. Om duration ges ett 
negativt värde, avaktiveras cachningen, men befintligt cachelagrat innehåll 
blir kvar i cachen. I versioner före 1.1.8, avaktiveras cachen om duration 
sätts till 0 eller negativt värde.

### Beroende

Liksom vid [datacachning](/doc/guide/caching.data) kan innehållsfragment som 
cachelagras även ha beroenden. Till exempel, innehållet i en för tillfället 
visad postning beror av om postningen modifierats eller inte.

För att specificera en dependency, sätt [dependency|COutputCache::dependency]-
alternativet, vars värde antingen kan vara ett objekt som implementerar 
[ICacheDependency] eller en array innehållande konfigurationsvärden som kan 
användas för att generera dependency-objektet. Följande kod specificerar att 
innehållsfragmentet beror på om värdet för kolumnen `lastModified`  ändrats:

~~~
[php]
...annat HTML-innehåll...
<?php if($this->beginCache($id, array('dependency'=>array(
		'class'=>'system.caching.dependencies.CDbCacheDependency',
		'sql'=>'SELECT MAX(lastModified) FROM Post')))) { ?>
...innehåll som skall cachelagras...
<?php $this->endCache(); } ?>
...annat HTML-innehåll...
~~~

### Variation

Innehåll som cachelagras kan varieras i enlighet med olika parametrar. Till exempel, 
den personliga profilen kan se annorlunda ut för olika användare. Vid 
cachelagring av profilinnehållet vill vi att den cachelagrade kopian varieras 
baserat på användaridentiteter. Detta innebär huvudsakligen att skilda ID:n 
skall användas vid anrop till [beginCache()|CBaseController::beginCache()].

Som alternativ till att utvecklare varierar ID:n enligt något schema, har 
[COutputCache] en sådan finess inbyggd. Nedan följer en sammanställning.

   - [varyByRoute|COutputCache::varyByRoute]: genom att sätta detta alternativ till true, kommer det cachelagrade innehållet att varieras enligt [route](/doc/guide/basics.controller#route). Det innebär att varje kombination av begärd kontroller och åtgärd kommer att få separat cachelagrat innehåll.

   - [varyBySession|COutputCache::varyBySession]: genom att sätta detta alternativ till true, kan det cachelagrade innehållet varieras efter sessions-ID. Det innebär att respektive användarsession kan få olika innehåll presenterat, i samtliga fall levererat från cache.

   - [varyByParam|COutputCache::varyByParam]: genom att sätta detta alternativ till en array med namn, kan det cachelagrade innehållet varieras efter värden i de specificerade GET-parametrarna. Till exempel, om en sida presenterar innehållet från en postning baserat på  GET-parametern `id`, kan innehållet i varje postning cachelagras genom att [varyByParam|COutputCache::varyByParam] sätts till en `array('id')`. Utan en sådan variation skulle bara en enda postning kunna lagras.

   - [varyByExpression|COutputCache::varyByExpression]: genom att sätta detta alternativ till ett PHP-uttryck 
kan det cachelagrade innehållet fås att variera i enlighet med värdet av detta PHP-uttryck. 

### Typ av Request

Ibland är det önskvärt att aktivera fragmentcachning för endast vissa typer av 
request. Till exempel, för en sida som presenterar ett formulär, vill vi endast 
cachelagra formuläret när det initialt efterfrågas (via en GET-request). All 
följande presentation av formuläret (via en POST-request), bör inte cachelagras 
eftersom formuläret kan innehålla användarinmatning. Detta kan åstadkommas genom 
att specificera alternativet [requestTypes|COutputCache::requestTypes]:

~~~
[php]
...annat HTML-innehåll...
<?php if($this->beginCache($id, array('requestTypes'=>array('GET')))) { ?>
...innehåll som skall cachelagras...
<?php $this->endCache(); } ?>
...annat HTML-innehåll...
~~~

Nästlad cachning
----------------

Fragmentcachning kan vara nästlad. Det innebär att ett cachelagrat fragment 
omsluts av ett större fragment, även det cachelagrat. Till exempel, 
kommentarerna (till postningen) cachelagras i ett inre fragment, vilket 
cachelagras tillsammans med själva postningen i en yttre fragmentcache.

~~~
[php]
...annat HTML-innehåll...
<?php if($this->beginCache($id1)) { ?>
...yttre innehåll som skall cachelagras...
	<?php if($this->beginCache($id2)) { ?>
	...inre innehåll som skall cachelagras...
	<?php $this->endCache(); } ?>
...yttre innehåll som skall cachelagras...
<?php $this->endCache(); } ?>
...annat HTML-innehåll...
~~~

Andra cachningsalternativ kan sättas för nästlade cachefragment. till exempel, 
kan den inre och den yttre cachen i ovanstående exempel ges olika duration-värden. 
När innehållet i den yttre cachen blir ogiltigt, kan den inre cachen 
fortfarande tillhandahålla giltiga inre fragment. Detta är dock inte sant i 
omvänd ordning. Om den yttre cachen innehåller giltig data kommer alltid den 
cachelagrade kopian att tillhandahållas, även om innehållet i den inre cachen 
redan blivit för gammalt och klassat ogiltigt.

<div class="revision">$Id: caching.fragment.txt 3315 2011-06-24 15:18:11Z qiang.xue $</div>