Frågeverktyg (Query Builder)
============================

Yii:s frågeverktyg erbjuder en objektorienterad variant för framställning av SQL-frågor. 
Verktyget möjliggör för utvecklare att använda klassmetoder och propertyn för specificering 
av de enskilda delarna av en SQL-fråga. Därefter sammanställer verktyget de olika delarna till 
en giltig SQL-fråga som kan exekveras genom amrop till DAO-metoderna som beskrivs i [Data Access Objects](/doc/guide/database.dao). 
Nedan visas exempel på typisk användning av frågeverktyget för att bygga en SQL SELECT-fråga:

~~~
[php]
$user = Yii::app()->db->createCommand()
	->select('id, username, profile')
	->from('tbl_user u')
	->join('tbl_profile p', 'u.id=p.user_id')
	->where('id=:id', array(':id'=>$id))
	->queryRow();
~~~


Frågeverktyget kommer bäst till sin rätt när en SQL-fråga behöver komponeras programmatiskt eller 
baserat på någon villkorlig logik i applikationen. Bland huvudsakliga fördelarna med att använda 
frågeverktyget ingår:

* Det tillåter att komplexa SQL-frågor byggs programmatiskt.

* Tabell- och kolumnnamn omsluts automatiskt med med diakritiskt tecken (`) så att konflikt undviks 
med reserverade ord och specialtecken i SQL.

* Även parametervärden omsluts och parameterbindning används när så är möjligt, vilket hjälper till 
att reducera risken för "SQL injection"-attacker.

* Ett visst mått av databasabstraktion erhålls, vilket underlättar migrering till andra databasplattformar.


Det är inte nödvändigt att använda frågeverktyget. Faktum är att enkla frågor lättare och snabbare kan 
skrivas direkt med SQL-satser.

> Note|Märk: Frågeverktyget kan inte användas för att modifiera en existerande fråga, specificerad som en
> SQL-sats. Följande kod kommer inte att fungera:
>
> ~~~
> [php]
> $command = Yii::app()->db->createCommand('SELECT * FROM tbl_user');
> // Nedanstående rad kommer INTE att lägga till ett WHERE-led till ovanstående SQL
> $command->where('id=:id', array(':id'=>$id));
> ~~~
>
> Med andra ord, blanda inte renodlad SQL med användning av frågeverktyget.


Förbereda frågeverktyget
------------------------

Yii:s frågeverktyg är relaterat till [CDbCommand], den huvudsakliga DB-frågeklassen beskriven i [Data Access Objects](/doc/guide/database.dao).

För att börja använda frågeverktyget skapar vi en ny instans av [CDbCommand]

~~~
[php]
$command = Yii::app()->db->createCommand();
~~~

Det vill säga, vi använder `Yii::app()->db` för att erhålla DB-anslutningen och anropar sedan [CDbConnection::createCommand()] 
för att skapa den önskade command-instansen.

Märk att i stället för att lämna med en komplett SQL-sats vid anropet av `createCommand()`, så som är brukligt 
i [Data Access Objects](/doc/guide/database.dao), lämnar vi den tom. 
Anledningen till  detta är att vi vill bygga enskilda delar av SQL-satsen genom att använda frågeverktygets 
metoder, vilka  beskrivs nedan.


Bygga datahämtningsfrågor
-------------------------

Datahämtningsfrågor refererar till SQL SELECT-satser. Frågeverktyget erbjuder en uppsättning metoder 
för att bygga individuella delar av en SELECT-sats. Eftersom alla dessa metoder lämnar [CDbCommand]-instansen 
i retur, kan de anropas i en kedja (method chaining), som exemplet i början av detta avsnitt visar.

* [select()|CDbCommand::select() ]: specificerar SELECT-delen av frågan
* [selectDistinct()|CDbCommand::selectDistinct]: specificerar SELECT-delen av frågan och aktiverar DISTINCT-flaggan
* [from()|CDbCommand::from() ]: specificerar FROM-delen av frågan
* [where()|CDbCommand::where() ]: specificerar WHERE-delen av frågan
* [join()|CDbCommand::join() ]: lägger till ett "inner join"-fragment till frågan
* [leftJoin()|CDbCommand::leftJoin]: lägger till ett "left outer join"-fragment
* [rightJoin()|CDbCommand::rightJoin]: lägger till ett "right outer join"-fragment
* [crossJoin()|CDbCommand::crossJoin]: lägger till ett "cross join query"-fragment
* [naturalJoin()|CDbCommand::naturalJoin]: lägger till ett "natural join"-fragment
* [group()|CDbCommand::group() ]: specificerar GROUP BY-delen av frågan
* [having()|CDbCommand::having() ]: specificerar HAVING-delen av frågan
* [order()|CDbCommand::order() ]: specificerar ORDER BY-delen av frågan
* [limit()|CDbCommand::limit() ]: specificerar LIMIT-delen av frågan
* [offset()|CDbCommand::offset() ]: specificerar OFFSET-delen av frågan
* [union()|CDbCommand::union() ]: lägger till ett UNION-fragment till frågan


I fortsättningen nedan förklaras hur man använder dessa frågeverktygets metoder. 
För enkelhets skull antar vi att den underliggande databasen är MySQL. 
Märk att om annan DBMS används, kan annorlunda "quoting" av tabell-/kolumnnamn/värden än i exemplen användas.


### select()

~~~
[php]
function select($columns='*')
~~~

Metoden [select()|CDbCommand::select() ] specificerar `SELECT`-delen av en fråga. Parametern `$columns`specificerar 
vilka kolumner som skall selekteras, antingen som en sträng av kommaseparerade kolumnnamn, eller en vektor 
innehållande kolumnnamn. Kolumnnamn kan innehålla tabellprefix ochd/eller kolumnalias. Metoden kommer att 
automatiskt omge kolumnnamn med diakritiskt tecken utom då en kolumnspecifikation innehåller parenteser 
(vilket innebär att kolumnen angetts som ett DB-uttryck).

Nedan följer några exempel:

~~~
[php]
// SELECT *
select()
// SELECT `id`, `username`
select('id, username')
// SELECT `tbl_user`.`id`, `username` AS `name`
select('tbl_user.id, username as name')
// SELECT `id`, `username`
select(array('id', 'username'))
// SELECT `id`, count(*) as num
select(array('id', 'count(*) as num'))
~~~


### selectDistinct()

~~~
[php]
function selectDistinct($columns)
~~~

Metoden [selectDistinct()|CDbCommand::selectDistinct] är snarlik [select()|CDbCommand::select() ] förutom att  
den aktiverar `DISTINCT`-flaggan. Till exempel, `selectDistinct('id, username')` kommer att generera följande SQL:

~~~
SELECT DISTINCT `id`, `username`
~~~


### from()

~~~
[php]
function from($tables)
~~~

Metoden [from()|CDbCommand::from() ] specificerar `FROM`-delen av en fråga. Parametern `$tables` specificerar 
vilka tabeller selektering skall ske från. Denna kan antingen vara en sträng av kommaseparerade tabellnamn, 
eller en vektor innehållande tabellnamn. Tabellnamn kan innehålla schemaprefix (t.ex. `public.tbl_user`) 
och/eller tabellalias (t.ex. `tbl_user u`). Metoden kommer att automatiskt omge tabellnamn med 
diakritiskt tecken utom då en tabellspecifikationen innehåller parenteser (vilket innebär att tabellen angetts 
som en subfråga eller ett DB-uttryck).

Nedan följer några exempel:

~~~
[php]
// FROM `tbl_user`
from('tbl_user')
// FROM `tbl_user` `u`, `public`.`tbl_profile` `p`
from('tbl_user u, public.tbl_profile p')
// FROM `tbl_user`, `tbl_profile`
from(array('tbl_user', 'tbl_profile'))
// FROM `tbl_user`, (select * from tbl_profile) p
from(array('tbl_user', '(select * from tbl_profile) p'))
~~~


### where()

~~~
[php]
function where($conditions, $params=array())
~~~

Metoden [where()|CDbCommand::where() ] specificerar `WHERE`-delen av en fråga. 
Parametern `$conditions` specificerar frågevillkor medan `$params` 
specificerar de parameterar som kommer att knytas till den kompletta frågan. 
Parametern `$conditions` kan ges antingen som en sträng (t.ex. `id=1`) eller 
som en vektor med följande format:

~~~
[php]
array(operator, operand1, operand2, ...)
~~~

där `operator` kan vara något av följande:

* `and`: operanderna sammanbinds med hjälp av `AND`. 
Till exempel, `array('and', 'id=1', 'id=2')` genererar `id=1 AND id=2`. 
Om en operand i sin tur ges i form av en vektor, kommer den att konverteras till sträng på samma sätt. 
Till exempel, `array('and', 'type=1', array('or', 'id=1', 'id=2'))` genererar `type=1 AND (id=1 OR id=2)`. 
Metoden lägger INTE till "quoting" med diakritiskt tecken, inte heller införs undantag "escaping" av specialteckenkoder.

* `or`: lika som `and`-operatorn förutom att operanderna sammanbinds med OR.

* `in`: operand 1 skall vara en kolumn eller ett DB-uttryck, operand 2 en vektor som representerar ett intervall 
av värden som kolumnen eller DB-uttrycket skall finnas inom. 
Till exempel, `array('in', 'id', array(1,2,3))` genererar `id IN (1,2,3)`. 
Metoden omger kolumnnamn med diakritiskt tecken samt inför vid behov undantag (escaping) för specialteckenkoder i range-operanden.

* `not in`: lika som `in`-operatorn förutom att `IN` ersätts av `NOT IN` i det genererade villkoret.

* `like`: operand 1 skall vara en kolumn eller ett DB-uttryck, operand 2 en vektor som representerar ett intervall 
av värden som kolumnen eller DB-uttrycket skall likna. 
Till exempel, `array('like', 'name', '%tester%')` genererar `name LIKE '%tester%'`. 
När intervallet anges som en vektor, kommer flera `LIKE`-predikat sammanbundna av `AND` att genereras. 
Till exempel, `array('like', 'name', array('%test%', '%sample%'))` genererar `name LIKE '%test%' AND name LIKE '%sample%'`. 
Metoden omger kolumnnamn med diakritiskt tecken samt inför vid behov undantag för specialteckenkoder i range-operanden.

* `not like`: lika som `like`-operatorn förutom att `LIKE` ersätts av `NOT LIKE` i det genererade villkoret.

* `or like`: lika som `like`-operatorn förutom att `OR` används vid sammanbindning av flera `LIKE`-predikat.

* `or not like`: lika som `not like`-operatorn förutom att `OR` används vid sammanbindning av flera `NOT LIKE`-predikat.


Nedan följer några exempel på användning av `where`:

~~~
[php]
// WHERE id=1 or id=2
where('id=1 or id=2')
// WHERE id=:id1 or id=:id2
where('id=:id1 or id=:id2', array(':id1'=>1, ':id2'=>2))
// WHERE id=1 OR id=2
where(array('or', 'id=1', 'id=2'))
// WHERE id=1 AND (type=2 OR type=3)
where(array('and', 'id=1', array('or', 'type=2', 'type=3')))
// WHERE `id` IN (1, 2)
where(array('in', 'id', array(1, 2))
// WHERE `id` NOT IN (1, 2)
where(array('not in', 'id', array(1,2)))
// WHERE `name` LIKE '%Qiang%'
where(array('like', 'name', '%Qiang%'))
// WHERE `name` LIKE '%Qiang' AND `name` LIKE '%Xue'
where(array('like', 'name', array('%Qiang', '%Xue')))
// WHERE `name` LIKE '%Qiang' OR `name` LIKE '%Xue'
where(array('or like', 'name', array('%Qiang', '%Xue')))
// WHERE `name` NOT LIKE '%Qiang%'
where(array('not like', 'name', '%Qiang%'))
// WHERE `name` NOT LIKE '%Qiang%' OR `name` NOT LIKE '%Xue%'
where(array('or not like', 'name', array('%Qiang%', '%Xue%')))
~~~

Vänligen notera att när operator innehåller `like`, behöver vi uttryckligen specificera jokertecken 
(så som `%` and `_`) i mönstren. Om mönstren hämtas från användarinmatning, bör vi även använda 
följande kod för att införa undantag för specialteckenkoder och därmed förhindra att de behandlas som jokertecken:

~~~
[php]
$keyword=$_GET['q'];
// escape % and _ characters
$keyword=strtr($keyword, array('%'=>'\%', '_'=>'\_'));
$command->where(array('like', 'title', '%'.$keyword.'%'));
~~~


### order()

~~~
[php]
function order($columns)
~~~

Metoden [order()|CDbCommand::order() ] specificerar `ORDER BY`-delen av en fråga.
Parametern `$columns` specificerar kolumnerna att sortera efter, antingen i form av en sträng som representerar 
kommaseparerade kolumnnamn samt sorteringsdirektiv (`ASC` or `DESC`), alternativt en vektor av 
kolumnnamn och sorteringsdirektiv. Kolumnnamn kan innehålla tabellprefix. Metoden omger automatiskt kolumnnamn 
med diakritiskt tecken förutom när kolumn innehåller parenteser (vilket innebär att kolumn ges i form av ett DB-uttryck).

Nedan följer några exempel:

~~~
[php]
// ORDER BY `name`, `id` DESC
order('name, id desc')
// ORDER BY `tbl_profile`.`name`, `id` DESC
order(array('tbl_profile.name', 'id desc'))
~~~


### limit() och offset()

~~~
[php]
function limit($limit, $offset=null)
function offset($offset)
~~~

Metoderna [limit()|CDbCommand::limit() ] och [offset()|CDbCommand::offset() ] specificerar 
`LIMIT`- och `OFFSET`-delarna av en fråga. Lägg märke till att vissa DBMS saknar stöd för `LIMIT`- och `OFFSET`-syntax. 
I sådant fall skriver frågeverktyget om SQL-satsen för simulering av funktionaliteten hos limit och offset.

Nedan följer några exempel:

~~~
[php]
// LIMIT 10
limit(10)
// LIMIT 10 OFFSET 20
limit(10, 20)
// OFFSET 20
offset(20)
~~~


### join() och dess varianter

~~~
[php]
function join($table, $conditions, $params=array())
function leftJoin($table, $conditions, $params=array())
function rightJoin($table, $conditions, $params=array())
function crossJoin($table)
function naturalJoin($table)
~~~

Metoden [join()|CDbCommand::join() ] och dess varianter specificerar sammanfogning med andra tabeller 
med hjälp av `INNER JOIN`, `LEFT OUTER JOIN`, `RIGHT OUTER JOIN`, `CROSS JOIN`, eller `NATURAL JOIN`. 
Parametern `$table` specificerar tabellen med vilken sammanfogning skall ske. Tabellnamnet kan innehålla 
schemaprefix och/eller alias. Metoden kommer att automatiskt omge tabellnamnet med 
diakritiskt tecken utom då en det innehåller parenteser, vilket innebär en subfråga eller ett DB-uttryck. 
Parametern `$conditions` specificerar villkor för sammanfogningen. Dess syntax är samma som för [where()|CDbCommand::where() ]. 
Och `$params` specificerar de parametrar som skall knytas till den kompletta frågan.

Till skillnad från frågeverktygets övriga metoder, medför varje nytt anrop till en join-metod att dess resultat kommer att 
läggas till efter de tidigare.

Nedan följer några exempel:

~~~
[php]
// JOIN `tbl_profile` ON user_id=id
join('tbl_profile', 'user_id=id')
// LEFT JOIN `pub`.`tbl_profile` `p` ON p.user_id=id AND type=1
leftJoin('pub.tbl_profile p', 'p.user_id=id AND type=:type', array(':type'=>1))
~~~


### group()

~~~
[php]
function group($columns)
~~~

Metoden [group()|CDbCommand::group() ] specificerar `GROUP BY`-delen av en fråga.
Parametern `$columns` specificerar kolumnerna att gruppindela efter, i strängformat representerande 
kommaseparerade kolumnnamn, alternativt som motsvarande vektor. Kolumnnamn kan innehålla tabellprefix. 
Metoden kommer att automatiskt omge kolumnnamn med diakritiskt tecken utom då en kolumnspecifikationen innehåller parenteser 
(vilket innebär att kolumnen angetts som en subfråga eller ett DB-uttryck).

Nedan följer några exempel:

~~~
[php]
// GROUP BY `name`, `id`
group('name, id')
// GROUP BY `tbl_profile`.`name`, `id`
group(array('tbl_profile.name', 'id')
~~~


### having()

~~~
[php]
function having($conditions, $params=array())
~~~

Metoden [having()|CDbCommand::having() ] specificerar `HAVING`-delen av en fråga. Dess användning 
har likhet med [where()|CDbCommand::where() ].

Nedan följer några exempel:

~~~
[php]
// HAVING id=1 or id=2
having('id=1 or id=2')
// HAVING id=1 OR id=2
having(array('or', 'id=1', 'id=2'))
~~~


### union()

~~~
[php]
function union($sql)
~~~

Metoden [union()|CDbCommand::union() ] specificerar `UNION`-delen av en fråga. Den lägger till `$sql` 
till existerande SQL genom användning av operatorn `UNION`. Anrop av `union()` upprepade gånger kommer att 
lägga till flera SQL-led efter existerande SQL.

Nedan följer några exempel:

~~~
[php]
// UNION (select * from tbl_profile)
union('select * from tbl_profile')
~~~


### Exekvera frågor

När en fråga byggts genom anrop till frågeverktygets metoder enligt ovan, kan vi anropa DAO-metoderna 
som beskrivs i [Data Access Objects](/doc/guide/database.dao), så att frågan exekveras. 
Till exempel, kan vi anropa [CDbCommand::queryRow()] för att erhålla en enda rad som resultat, alternativt 
[CDbCommand::queryAll()] för att erhålla samtliga rader tillsammans.

Exempel:

~~~
[php]
$users = Yii::app()->db->createCommand()
	->select('*')
	->from('tbl_user')
	->queryAll();
~~~


### Retrieving SQLs

Förutom att exekvera en fråga som byggts med frågeverktyget, kan vi även hämta den motsvarande SQL-satsen. 
Detta kan ske genom anrop av [CDbCommand::getText()].

~~~
[php]
$sql = Yii::app()->db->createCommand()
	->select('*')
	->from('tbl_user')
	->text;
~~~

Om det finns parametrar som skall knytas till frågan, kan dessa hämtas via propertyn [CDbCommand::params].


### Alternativ syntax för att bygga frågor

Ibland är användning av metodkedja (method chaining) för att bygga en fråga inte det optimala valet. 
Yii:s frågeverktyg tillåter att man bygger en fråga genom att helt enkelt tilldela värden till ett objekts propertyn. 
Specifikt finns för varje metod i frågeverktyget en motsvarande property med samma namn. Tilldelning av ett värde 
till propertyn är ekvivalent med att anropa motsvarande metod. Till exempel så är följande två uttryck ekvivalenta, 
förutsatt att `$command` representerar ett [CDbCommand]-objekt:

~~~
[php]
$command->select(array('id', 'username'));
$command->select = array('id', 'username');
~~~

Dessutom kan metoden [CDbConnection::createCommand()] acceptera en vektor som parameter. 
Namn-värdeparen i vektorn kommer att användas till att initialisera [CDbCommand]-instansens propertyn. 
Detta innebär att vi kan använda följande kod för att bygga en fråga:

~~~
[php]
$row = Yii::app()->db->createCommand(array(
	'select' => array('id', 'username'),
	'from' => 'tbl_user',
	'where' => 'id=:id',
	'params' => array(':id'=>1),
))->queryRow();
~~~


### Bygga upprepade frågor

En [CDbCommand]-instans kan återanvändas flera gånger för att bygga ytterligare frågor. 
Innan en ny fråga byggs behöver dock metoden [CDbCommand::reset()] anropas för att städa efter föregående fråga. 

Till exempel:

~~~
[php]
$command = Yii::app()->db->createCommand();
$users = $command->select('*')->from('tbl_users')->queryAll();
$command->reset();  // clean up the previous query
$posts = $command->select('*')->from('tbl_posts')->queryAll();
~~~


Bygga frågor för manipulering av data
-------------------------------------

Datamanipuleringsfrågor refererar till SQL-satser för insättning, uppdatering och borttagning av data i en databastabell. 
Som motsvarighet till dessa frågor tillhandahåller frågeverktyget metoderna `insert`, `update` och `delete`.
Till skillnad från SELECT-frågemetoderna som beskrivits ovan, bygger var och en av frågemetoderna för datamanipulering 
en komplett SQL-sats och exekverar den omedelbart.

* [insert()|CDbCommand::insert]: infogar en rad i en tabell
* [update()|CDbCommand::update]: uppdaterar data i en tabell
* [delete()|CDbCommand::delete]: tar bort data ur en tabell


Nedan beskrivs dessa frågemetoder för datamanipulering.


### insert()

~~~
[php]
function insert($table, $columns)
~~~

Metoden [insert()|CDbCommand::insert] bygger och exekverar en SQL `INSERT`-sats. 
Parametern `$table` specificerar vilken tabell att infoga i, medan `$columns` är en vektor 
innehållande namn-värdepar som specificerar vilka kolumnvärden som skall infogas. 
Metoden kommer att korrekt omge tabellnamn med diakritiskt tecken samt använda parameterbindning 
för värden som skall infogas.

Nedan följer ett exempel:

~~~
[php]
// bygg och exekvera följande SQL:
// INSERT INTO `tbl_user` (`name`, `email`) VALUES (:name, :email)
$command->insert('tbl_user', array(
	'name'=>'Tester',
	'email'=>'tester@example.com',
));
~~~


### update()

~~~
[php]
function update($table, $columns, $conditions='', $params=array())
~~~

Metoden [update()|CDbCommand::update] bygger och exekverar en SQL `UPDATE`-sats. 
Parametern `$table` specificerar vilken tabell som skall uppdateras; `$columns` är en vektor 
innehållande namn-värdepar som specificerar vilka kolumnvärden som skall uppdateras; 
`$conditions` och `$params` specificerar, precis som i [where()|CDbCommand::where() ], `WHERE`-ledet i `UPDATE`-satsen. 
Metoden kommer att korrekt omge tabellnamn med diakritiskt tecken samt använda parameterbindning 
för värden som skall uppdateras.

Nedan följer ett exempel:

~~~
[php]
// bygg och exekvera följande SQL:
// UPDATE `tbl_user` SET `name`=:name WHERE id=:id
$command->update('tbl_user', array(
	'name'=>'Tester',
), 'id=:id', array(':id'=>1));
~~~


### delete()

~~~
[php]
function delete($table, $conditions='', $params=array())
~~~

Metoden [delete()|CDbCommand::delete] bygger och exekverar en SQL `DELETE`-sats. 
Parametern `$table` specificerar tabell att ta bort från; `$conditions` och `$params` 
specificerar, precis som i [where()|CDbCommand::where() ], `WHERE`-ledet i 
`DELETE`-satsen. Metoden kommer att korrekt omge tabellnamn med diakritiskt tecken.

Nedan följer ett exempel:

~~~
[php]
// bygg och exekvera följande SQL:
// DELETE FROM `tbl_user` WHERE id=:id
$command->delete('tbl_user', 'id=:id', array(':id'=>1));
~~~

Bygga frågor som manipulerar databasschema
------------------------------------------

Förutom vanliga frågor som hämtar eller manipulerar data, erbjuder frågeverktyget även en uppsättning 
metoder som bygger och exekverar SQL-frågor som kan manipulera schema i en databas. 
Mer specifikt erbjuds stöd för följande frågor:

* [createTable()|CDbCommand::createTable]: skapar en tabell
* [renameTable()|CDbCommand::renameTable]: ändrar namn på en tabell
* [dropTable()|CDbCommand::dropTable]: tar bort en tabell
* [truncateTable()|CDbCommand::truncateTable]: tömmer en tabell på data
* [addColumn()|CDbCommand::addColumn]: lägger till en tabellkolumn
* [renameColumn()|CDbCommand::renameColumn]: ändrar namn på en tabellkolumn
* [alterColumn()|CDbCommand::alterColumn]: ändrar specifikation för en tabellkolumn
* [dropColumn()|CDbCommand::dropColumn]: tar bort en tabellkolumn
* [createIndex()|CDbCommand::createIndex]: skapar ett index
* [dropIndex()|CDbCommand::dropIndex]: tar bort ett index

> Info: Även om de faktiska SQL-satserna för schemamanipulering varierar en hel del 
mellan olika databashanterare (DBMS), försöker frågeverktyget att erbjuda ett enhetligt 
gränssnitt för att bygga dessa frågor. Detta förenklar uppgiften att flytta en databas 
från en DBMS till en annan.


###Abstrakta datatyper

Frågeverktyget introducerar en uppsättning abstrakta datatyper som kan användas till att definiera 
tabellkolumner. Till skillnad från de fysiska datatyperna som är specifika för enskilda DBMS och skiljer 
sig åt mellan olika DBMS, är de abstrakta datatyperna DBMS-oberoende. När abstrakta datatyper används 
till att definiera tabellkolumner, kommer frågeverktyget att konvertera till motsvarande fysiska datatyper.

Följande abstrakta datatyper stöds av frågeverktyget.

* `pk`: en generell primärnyckeltyp, konverteras till `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY` för MySQL;
* `string`: strängtyp, konverteras till `varchar(255)` för MySQL;
* `text`: texttyp (long string), konverteras till `text` för MySQL;
* `integer`: heltalstyp, konverteras till `int(11)` för MySQL;
* `float`: flyttalstyp, konverteras till `float` för MySQL;
* `decimal`: decimaltal, konverteras till `decimal` för MySQL;
* `datetime`: datumtid, konverteras till `datetime` för MySQL;
* `timestamp`: tidstämpel, konverteras till `timestamp` för MySQL;
* `time`: klockslag, konverteras till `time` för MySQL;
* `date`: datum, konverteras till `date` för MySQL;
* `binary`: binärdata, konverteras till `blob` för MySQL;
* `boolean`: boolsk data, konverteras till `tinyint(1)` för MySQL;
* `money`: monetär/valuta-data, konverteras till `decimal(19,4)` för MySQL. Denna typ har varit tillgänglig sedan version 1.1.8.


###createTable()

~~~
[php]
function createTable($table, $columns, $options=null)
~~~

Metoden [createTable()|CDbCommand::createTable] bygger och exekverar en SQL-sats som skapar en tabell. 
Parametern `$table` specificerar namnet på tabellen som skall skapas. Parametern `$columns` specificerar 
kolumnerna i den nya tabellen. De måste anges i form av namn-definitionspar (t.ex. `'username'=>'string'`). 
Parametern `$options` specificerar möjliga extra SQL-fragment som skall följa på genererad SQL. 
Metoden kommer att korrekt omge tabellnamn och kolumnnamn med diakritiskt tecken.

Vid specificering av kolumndefinition kan man använda en abstrakt datatyp som beskrivits ovan. 
Frågeverktyget kommer att konvertera den abstrakta datatypen till motsvarande fysiska datatyp, 
givet vilken DBMS som för tillfället används. Till exempel för MySQL, kommer `string` att 
konverteras till `varchar(255)`.

En kolumndefinition kan även innehålla icke-abstrakta datatyper eller specifikationer. De kommer att 
infogas i genererad SQL utan ändring. Till exempel `point`, som inte är en abstrakt datatyp kommer, 
om den används i en kolumndefinition, att uppträda oförändrad i resulterande SQL; `string NOT NULL` 
kommer att konverteras till `varchar(255) NOT NULL` (dvs endast den abstrakta typen `string` konverteras).

Nedan följer ett exempel på hur man skapar en tabell:

~~~
[php]
// CREATE TABLE `tbl_user` (
//     `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
//     `username` varchar(255) NOT NULL,
//     `location` point
// ) ENGINE=InnoDB
createTable('tbl_user', array(
	'id' => 'pk',
	'username' => 'string NOT NULL',
	'location' => 'point',
), 'ENGINE=InnoDB')
~~~


###renameTable()

~~~
[php]
function renameTable($table, $newName)
~~~

Metoden [renameTable()|CDbCommand::renameTable] bygger och exekverar en SQL-sats som ändrar 
namnet på en tabell. Parametern `$table` specificerar namnet på tabellen som skall namnändras. 
Parametern `$newName` specificerar det nya tabellnamnet. Frågeverktyget kommer att korrekt 
omge tabellnamnen med diakritiskt tecken.

Nedan följer ett exempel som visar hur man ändrar namn på en tabell:

~~~
[php]
// RENAME TABLE `tbl_users` TO `tbl_user`
renameTable('tbl_users', 'tbl_user')
~~~


###dropTable()

~~~
[php]
function dropTable($table)
~~~

Metoden [dropTable()|CDbCommand::dropTable] bygger och exekverar en SQL-sats som tar bort en tabell. 
Parametern `$table` specificerar namnet på tabellen som skall tas bort. Frågeverktyget kommer att 
korrekt omge tabellnamnet med diakritiskt tecken.

Nedan följer ett exempel på hur man tar bort en tabell:

~~~
[php]
// DROP TABLE `tbl_user`
dropTable('tbl_user')
~~~

###truncateTable()

~~~
[php]
function truncateTable($table)
~~~

Metoden [truncateTable()|CDbCommand::truncateTable] bygger och exekverar en SQL-sats som tar bort 
allt innehåll ur en tabell. Parametern `$table` specificerar namnet på tabellen som skall tömmas. 
Frågeverktyget kommer att korrekt omge tabellnamnet med diakritiskt tecken.

Nedan följer ett exempel på hur man tömmer en tabell på innehåll:

~~~
[php]
// TRUNCATE TABLE `tbl_user`
truncateTable('tbl_user')
~~~


###addColumn()

~~~
[php]
function addColumn($table, $column, $type)
~~~

Metoden [addColumn()|CDbCommand::addColumn] bygger och exekverar en SQL-sats som lägger till en ny 
tabellkolumn. Parametern `$table` specificerar namnet på tabellen som en ny kolumn skall tillfogas. 
Parametern `$column` specificerar namnet på den nya kolumnen. Och `$type` specificerar definitionen 
för den nya kolumnen. En kolumndefinition kan innehålla abstrakt datatyp, som beskrivits i avsnittet 
om "createTable". Frågeverktyget kommer att korrekt omge tabellnamn och kolumnnamn med diakritiskt tecken.

Nedan följer ett exempel på hur man lägger till en kolumn:

~~~
[php]
// ALTER TABLE `tbl_user` ADD `email` varchar(255) NOT NULL
addColumn('tbl_user', 'email', 'string NOT NULL')
~~~


###dropColumn()

~~~
[php]
function dropColumn($table, $column)
~~~

Metoden [dropColumn()|CDbCommand::dropColumn] bygger och exekverar en SQL-sats som tar bort en 
tabellkolumn. Parametern `$table` specificerar namnet på tabellen ur vilken en kolumn skall tas bort. 
Parametern `$column` specificerar namnet på kolumnen som skall tas bort. Frågeverktyget kommer 
att korrekt omge tabellnamn och kolumnnamn med diakritiskt tecken.

Nedan följer ett exempel på hur man tar bort en kolumn:

~~~
[php]
// ALTER TABLE `tbl_user` DROP COLUMN `location`
dropColumn('tbl_user', 'location')
~~~


###renameColumn()

~~~
[php]
function renameColumn($table, $name, $newName)
~~~

Metoden [renameColumn()|CDbCommand::renameColumn] bygger och exekverar en SQL-sats som ändrar namn 
på en tabellkolumn. Parametern `$table` specificerar namnet på tabellen vars kolumn skall byta namn. 
Parametern `$name` specificerar det befintliga kolumnnamnet. `$newName` specificerar önskat kolumnnamn. 
Frågeverktyget kommer att korrekt omge tabellnamn och kolumnnamn med diakritiskt tecken.

Nedan följer ett exempel på hur man ändrar en kolumns namn:

~~~
[php]
// ALTER TABLE `tbl_users` CHANGE `name` `username` varchar(255) NOT NULL
renameColumn('tbl_user', 'name', 'username')
~~~


###alterColumn()

~~~
[php]
function alterColumn($table, $column, $type)
~~~

Metoden [alterColumn()|CDbCommand::alterColumn] bygger och exekverar en SQL-sats som ändrar definition 
för en tabellkolumn. Parametern `$table` specificerar namnet på tabellen vars kolumndefinition skall ändras. 
Parametern `$column` specificerar namnet på kolumnen som skall ändras. Och `$type` specificerar den nya 
kolumndefinitionen. En kolumndefinition kan innehålla abstrakt datatyp, så som beskrivits tidigare för 
"createTable". Frågeverktyget kommer att korrekt omge tabellnamn och kolumnnamn med diakritiskt tecken.

Nedan följer ett exempel på hur man ändrar specifikation för en kolumn:

~~~
[php]
// ALTER TABLE `tbl_user` CHANGE `username` `username` varchar(255) NOT NULL
alterColumn('tbl_user', 'username', 'string NOT NULL')
~~~




###addForeignKey()

~~~
[php]
function addForeignKey($name, $table, $columns,
	$refTable, $refColumns, $delete=null, $update=null)
~~~

Metoden [addForeignKey()|CDbCommand::addForeignKey] bygger och exekverar en SQL-sats som lägger till 
ett integritetsvillkor (constraint) till en tabell. Parametern `$name` specificerar namnet på villkoret. 
Parametrarna `$table` och `$columns` specificerar tabell- och kolumnnamn för referensattributet (FK). 
Vid flera kolumner skall dessa separeras med kommatecken. Parametrarna `$refTable` och `$refColumns` 
specificerar tabell- och kolumnnamn som referensattributet refererar till. Parametrarna `$delete` och 
`$update` specificerar alternativen`ON DELETE` och `ON UPDATE` i SQL-satsen. De flesta databashanterare 
stöder följande alternativ: `RESTRICT`, `CASCADE`, `NO ACTION`, `SET DEFAULT`, `SET NULL`. 
Frågeverktyget kommer att korrekt omge tabellnamn, indexnamn och kolumnnamn med diakritiskt tecken.

Nedan följer ett exempel på hur man skapar en restriktion för referensattribut:

~~~
[php]
// ALTER TABLE `tbl_profile` ADD CONSTRAINT `fk_profile_user_id`
// FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`id`)
// ON DELETE CASCADE ON UPDATE CASCADE
addForeignKey('fk_profile_user_id', 'tbl_profile', 'user_id',
	'tbl_user', 'id', 'CASCADE', 'CASCADE')
~~~


###dropForeignKey()

~~~
[php]
function dropForeignKey($name, $table)
~~~

Metoden [dropForeignKey()|CDbCommand::dropForeignKey] bygger och exekverar en SQL-sats som tar bort ett 
integritetsvillkor. Parametern `$name` specificerar namnet på det villkor som skall tas bort. 
Parametern  `$table` specificerar namnet på tabellen som innehåller villkoret. Frågeverktyget kommer 
att korrekt omge tabellnamnet och villkorsnamnet med diakritiskt tecken.

Nedan följer ett exempel på hur man tar bort en restriktion för referensattribut:

~~~
[php]
// ALTER TABLE `tbl_profile` DROP FOREIGN KEY `fk_profile_user_id`
dropForeignKey('fk_profile_user_id', 'tbl_profile')
~~~


###createIndex()

~~~
[php]
function createIndex($name, $table, $column, $unique=false)
~~~

Metoden [createIndex()|CDbCommand::createIndex] bygger och exekverar en SQL-sats som skapar ett index. 
Parametern `$name` specificerar namn på det index som skall skapas. Parametern `$table` specificerar 
namnet på tabellen som indexet skall tillhöra. Parametern `$column` specificerar namnet på kolumnen 
som skall indexeras. Och parametern `$unique` specificerar huruvida ett unikt index skall skapas. 
Om indexet består av flera kolumner, måste dessa separeras med kommatecken. 
Frågeverktyget kommer att korrekt omge tabellnamn, indexnamn och kolumnnamn med diakritiskt tecken.

Nedan följer ett exempel på hur man skapar ett index:

~~~
[php]
// CREATE INDEX `idx_username` ON `tbl_user` (`username`)
createIndex('idx_username', 'tbl_user')
~~~


###dropIndex()

~~~
[php]
function dropIndex($name, $table)
~~~

Metoden [dropIndex()|CDbCommand::dropIndex] bygger och exekverar en SQL-sats som tar bort ett index. 
Parametern `$name` specificerar namn för indexet som skall tas bort. Parametern `$table` specificerar 
namnet på tabellen som indexet tillhör. Frågeverktyget kommer att korrekt omge tabellnamn och indexnamn 
med diakritiskt tecken.

Nedan följer ett exempel på hur man tar bort ett index:

~~~
[php]
// DROP INDEX `idx_username` ON `tbl_user`
dropIndex('idx_username', 'tbl_user')
~~~

<div class="revision">$Id: database.query-builder.txt 3408 2011-09-28 20:50:28Z alexander.makarow $</div>