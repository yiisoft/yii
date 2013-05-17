Dynamiskt innehåll
==================

Vid användning av [fragmentcachning](/doc/guide/caching.fragment) eller 
[sidcachning](/doc/guide/caching.page), uppstår ofta situationen att större 
delen av utdata är relativt statiskt, så när som på något eller några enstaka ställen. 
Till exempel en hjälpsida kan presentera statisk hjälpinformation med namnet på 
den för tillfället inloggade användaren synligt längst upp.

För att lösa detta problem, kan cacheinnehållet varieras i enlighet med 
användarnamnet, men det vore ett stort slöseri med värdefullt cacheutrymme då 
större delen av innehållet är oförändrat, förutom användarnamnet. Vi kan också 
dela upp sidan i ett flertal fragment och cachelagra dem var för sig, men detta 
komplicerar vyn och gör att koden blir mycket komplex. Ett bättre angreppssätt 
är att använda finessen *dynamskt innehåll* som tillhandahålls av [CController].

Med dynamiskt innehåll menas ett fragment av utmatning som inte skall 
cachelagras, även om det ingår i en fragmentcache. För att alltid göra 
innehållet dynamiskt måste det genereras varje gång, även när det omslutande 
innehållet levereras från cache. Av denna anledning krävs det att dynamiskt 
innehåll genereras av någon metod eller funktion.

Anrop av [CController::renderDynamic()] infogar dynamiskt innehåll på önskad 
plats.

~~~
[php]
...other HTML content...
<?php if($this->beginCache($id)) { ?>
...fragment content to be cached...
	<?php $this->renderDynamic($callback); ?>
...fragment content to be cached...
<?php $this->endCache(); } ?>
...other HTML content...
~~~

I ovanstående refererar `$callback` till en giltig PHP-callback. Det kan vara en 
sträng som refererar till namnet på en metod i den aktuella kontrollerklassen, 
eller en global funktion. Det kan också vara en array som refererar till en 
klassmetod. Varje annan parameter till 
[renderDynamic()|CController::renderDynamic()] kommer att skickas med till 
callback-rutinen. callback-rutinen bör returnera det dynamiska innehållet i 
stället för att presentera det.

<div class="revision">$Id: caching.dynamic.txt 163 2008-11-05 12:51:48Z weizhuo $</div>