Sidcachning
===========

Sidcachning refererar till cachning av innehållet för en hel sida. Sidcachning 
kan inträffa på olika ställen. Till exempel kan klienten/webbläsaren, genom val 
av en lämplig page header, fås att cachelagra sidan som presenteras, under en 
begränsasd tid. Själva webbapplikationen kan också cachelagra sidinnehållet. I 
detta delavsnitt, fokuserar vi på det senare tillvägagångssättet.

Sidcachning kan betraktas som ett specialfall av 
[fragmentcachning](/doc/guide/caching.fragment). Eftersom innehållet i en sida 
ofta genereras genom applicering av en layout på en vy, kommer det inte att 
fungera med endast anrop till [beginCache()|CBaseController::beginCache] och 
[endCache()|CBaseController::endCache] i layoutfilen. Detta beror på att 
layouten appliceras inuti [CController::render()]-metoden EFTER utvärderingen av 
innehållsvyn.

För cachning av en hel sida skall åtgärden som genererar sidinnehållet hoppas 
över. [COutputCache] kan användas som ett 
[filter](/doc/guide/basics.controller#filter) för att åstadkomma detta. Följande kod 
visar hur cachefiltret kan konfigureras:

~~~
[php]
public function filters()
{
	return array(
		array(
			'COutputCache',
			'duration'=>100,
			'varyByParam'=>array('id'),
		),
	);
}
~~~

Ovanstående filterkonfiguration skulle applicera filtret på alla åtgärder i 
kontrollern. Detta kan begränsas till en eller ett fåtal åtgärder genom 
användning av plus-operatorn. Fler detaljer finns i 
[filter](/doc/guide/basics.controller#filter).

> Tip|Tips: Klassen [COutputCache] kan användas som ett filter eftersom den är en 
utvidgning av [CFilterWidget], vilket innebär att den är både en widget och ett 
filter. Faktum är att sättet en widget arbetar på är mycket likt ett filter: en 
widget (filter) börjar innan något omslutet innehåll (åtgärd) utvärderas, och 
denna widget (filter) slutar efter utvärdering av omslutet innehåll (åtgärd).

<div class="revision">$Id: caching.page.txt 1014 2009-05-10 12:25:55Z qiang.xue $</div>