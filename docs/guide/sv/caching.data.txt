Datacachning
============

Datacachning handlar om att lagra någon PHP-variabel i cacheminne och hämta 
tillbaka den senare från cache. För detta ändamål tillhandahåller 
cachekomponentens basklass [CCache] två metoder som används för det mesta: 
[set()|CCache::set] och [get()|CCache::get].

För att lagra en variabel `$value` i cacheminnet väljer vi ett unikt ID och anropar [set()|CCache::set]:

~~~
[php]
Yii::app()->cache->set($id, $value);
~~~

Cachelagrade data förblir i cachen obegränsat länge om de inte tas bort på grund 
av någon cachningspolicy (t.ex. slut på cacheutrymme medförande att det äldsta 
datat tas bort). För ändring av detta beteende, kan en förfallotidsparameter 
lämnas med i anropet till [set()|CCache::set] så att data städas bort från 
cachen efter en viss tid:

~~~
[php]
// keep the value in cache for at most 30 seconds
Yii::app()->cache->set($id, $value, 30);
~~~

Senare, när variabeln behöver kommas åt (antingen i samma eller någon annan 
webbrequest), återhämtas den från cache genom anrop till [get()|CCache::get] med 
ID bifogat. Om värdet som returneras är false, innebär detta att sökt värde inte 
är tillgängligt i cachen utan måste genereras på nytt.

~~~
[php]
$value=Yii::app()->cache->get($id);
if($value===false)
{
	// regenerate $value because it is not found in cache
	// and save it in cache for later use:
	// Yii::app()->cache->set($id,$value);
}
~~~

Vid val av ID för en variabel som skall cachas, tillse att ID:t är unikt bland 
alla andra variabler som kan komma att cachas i applikationen. Det är INTE ett 
krav att ID:t är unikt över fler applikationer då cachekomponenten är 
intelligent nog att åtskilja ID:n tillhörande skilda applikationer.

Vissa cachelagringar, så som MemCache, APC, stöder återhämtning av multipla
cachelagrade värden genom ett satsvis arbetssätt, vilket kan reducera
onödig resursanvändning vid återhämtning av cachelagrad data. Metoden [mget()|CCache::mget] 
finns tillgänglig för detta ändamål. I händelse av att den underliggande cachelagringen 
inte stöder nämnda finess, simuleras den av [mget()|CCache::mget].

För att ta bort ett cachat värde, anropa [delete()|CCache::delete]; för att ta 
bort allting från cache, anropa [flush()|CCache::flush]. Var mycket försiktig 
med att anropa [flush()|CCache::flush] eftersom även cachelagrat data från andra 
applikationer tas bort.

> Tip|Tips: Eftersom [CCache] implementerar `ArrayAccess`, kan en cachekomponent 
användas som en array. Här följer några exempel:

> ~~~ 
> [php] 
> $cache=Yii::app()->cache; 
> $cache['var1']=$value1;  // equivalent to: $cache->set('var1',$value1); 
> $value2=$cache['var2'];  // equivalent to: $value2=$cache->get('var2'); 
> ~~~

Cacheberoende
-------------

Förutom utgången förfallotid, kan cachelagrat data också ogiltiggöras som följd 
av någon förändring i beroenden. Till exempel, om innehållet i någon fil 
cachelagras och filen ändras, så skall kopian i cachen markeras som ogiltig och 
det senaste innehållet från filen läsas i stället.

Ett beroende representeras som en instans av [CCacheDependency] eller nedärvd 
klass. Dependency-instansen bifogas data som skall cachas i anropet till [set()|CCache::set].

~~~
[php]
// the value will expire in 30 seconds
// it may also be invalidated earlier if the dependent file is changed
Yii::app()->cache->set($id, $value, 30, new CFileCacheDependency('FileName'));
~~~

Om vi nu återhämtar `$value` från cachen medelst anrop till [get()|CCache::get], 
kommer beroendet att utvärderas. Om en förändring skett erhålls returvärdet 
false, vilket indikerar att data behöver genereras på nytt.

Nedan följer en sammanställning av tillgängliga cacheberoenden:

   - [CFileCacheDependency]: beroendet ändras vid förändring av filens tidangivelse avseende senaste ändring.

   - [CDirectoryCacheDependency]: beroendet ändras vid förändring av någon fil i katalogen eller dess underkataloger.

   - [CDbCacheDependency]: beroendet ändras vid förändring i resultatet av databasfrågan given av specificerad SQL-sats.

   - [CGlobalStateCacheDependency]: beroendet ändras vid förändring av värdet för det specificerade globala tillståndet. Ett globalt tillstånd är en variabel som behåller sitt värde över multipla request och multipla sessioner i en applikation. Det definieras via [CApplication::setGlobalState()].

   - [CChainedCacheDependency]: beroendet ändras om något av beroendena i kedjan förändras.

   - [CExpressionDependency]: beroendet ändras om värdet av det specificerade PHP-uttrycket ändras. 


Cachning av databasfrågor
-------------------------

Sedan version 1.1.7, har Yii stöd för cachning av databasfrågor.
Med hjälp av datacachning lagrar frågecachning resultatet av en DB-fråga 
och kan på så vis spara exekveringstid i databasen för en fråga som upprepas senare, 
eftersom den kan hämtas direkt från cache.

> Info: Vissa DBMS (t.ex. [MySQL](http://dev.mysql.com/doc/refman/5.1/en/query-cache.html))
> stöder även frågecachning i databasservern. Jämfört med denna frågecachning i server, är
> finessen som avhandlas här mer flexibel samt har potential att vara mer effektiv.


### Aktivera frågecachning

För att aktivera frågecachning, tillse att [CDbConnection::queryCacheID] refererar till ID för en 
giltig cache-applikationskomponent (standardvärde `cache`).


### Använda frågecachning med DAO

För att använda frågecachning, anropa metoden [CDbConnection::cache()] när DB-frågor genomförs.
Följande är ett exempel:

~~~
[php]
$sql = 'SELECT * FROM tbl_post LIMIT 20';
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');
$rows = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryAll();
~~~

När ovanstående programsatser körs, kommer Yii att först att undersöka om det finns 
ett giltigt resultat i cache för den SQL-sats som skall exekveras. Det sker genom att 
följande tre villkor kontrolleras:

- cache innehåller en post indexerad av aktuell SQL-sats.
- postens giltighetstid ej överskriden (mindre än 1000 sekunder sedan den först sparades i cache).
- dess dependency ej ändrad (maxvärdet av `update_time` oförändrat sedan frågeresultatet sparades i cache).

Om samtliga villkor ovan är uppfyllda, kommer det cachelagrade resultatet att returneras.
I annat fall skickas SQL-satsen till databasservern för exekvering och tillhörande resultat lagras 
i cache, samt returneras.


### Använda frågecachning med ActiveRecord

Frågecachning kan även användas med [Active Record](/doc/guide/database.ar).
För att göra detta anropas en snarlik metod [CActiveRecord::cache()] på följande sätt:

~~~
[php]
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');
$posts = Post::model()->cache(1000, $dependency)->findAll();
// relationell AR-fråga
$posts = Post::model()->cache(1000, $dependency)->with('author')->findAll();
~~~

Metoden `cache()` här, är i praktiken en genväg till [CDbConnection::cache()].
Internt, vid exekvering av SQL-satsen som ActiveRecord genererat, kommer Yii 
att försöka använda frågecachning så som beskrivits i förra delavsnittet.


### Cachning av multipla frågor

Som standard gäller att ett anrop till metoden `cache()` (i antingen [CDbConnection] eller [CActiveRecord]),
medför att nästa SQL-sats markeras för cachning. Varje annan SQL-fråga kommer INTE att cachelagras, 
såvida inte `cache()` anropas igen. Till exempel,

~~~
[php]
$sql = 'SELECT * FROM tbl_post LIMIT 20';
$dependency = new CDbCacheDependency('SELECT MAX(update_time) FROM tbl_post');

$rows = Yii::app()->db->cache(1000, $dependency)->createCommand($sql)->queryAll();
// frågecachning kommer INTE att användas
$rows = Yii::app()->db->createCommand($sql)->queryAll();
~~~

Genom att lämna med en extra `$queryCount`-parameter till metoden `cache()`, kan vi 
framtvinga frågecachning av multipla frågor. När vi i följande exempel anropar `cache()`, 
specificeras att frågecachning skall användas för de två följande frågorna:

~~~
[php]
// ...
$rows = Yii::app()->db->cache(1000, $dependency, 2)->createCommand($sql)->queryAll();
// frågecachning KOMMER ATT användas
$rows = Yii::app()->db->createCommand($sql)->queryAll();
~~~

Som bekant är det möjligt att flera SQL-frågor exekveras när en relationell AR-fråga 
genomförs (möjlig att kontrollera i [loggmeddelanden](/doc/guide/topics.logging)).
Till exempel, om sambandet mellan `Post` och `Comment` är `HAS_MANY`, 
kommer följande kod att faktiskt exekvera två DB-frågor:

- first selekteras posterna med antal begränsat till 20;
- därefter hämtas kommentarerna som tillhör de tidigare selekterade posterna.

~~~
[php]
$posts = Post::model()->with('comments')->findAll(array(
	'limit'=>20,
));
~~~

Om vi använder frågecachning på följande sätt kommer endast den första DB-frågan att cachelagras:

~~~
[php]
$posts = Post::model()->cache(1000, $dependency)->with('comments')->findAll(array(
	'limit'=>20,
));
~~~

För att cachelagra båda DB-frågorna behöver vi lämna med den extra parametern som indikerar 
hur många DB-frågor vi vill cachelagra härnäst:

~~~
[php]
$posts = Post::model()->cache(1000, $dependency, 2)->with('comments')->findAll(array(
	'limit'=>20,
));
~~~


### Begränsningar

Frågecachning fungerar inte med frågeresultat som innehåller resursidentifierare (resource handles). 
Till exempel, när kolumntypen `BLOB` används i vissa DBMS, kommer frågeresultatet att returnera 
en resursidentifierare som kolumndata.

Somliga cachelagringsmetoder har begränsningar i minnesstorlek. Till exempel memcache begränsar 
storleken på varje post till 1MB. Om ett frågeresultat överskrider denna maxstorlek kommer 
cachelagringen att misslyckas.


<div class="revision">$Id: caching.data.txt 3125 2011-03-25 17:05:31Z qiang.xue $</div>