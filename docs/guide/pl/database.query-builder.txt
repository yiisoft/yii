Konstruktor zapytań
===================

Konstruktor zapytań w Yii umożliwia zorientowane obiektowo pisanie zapytań SQL. Daje to programiście możliwość używania właściwości i metod klasy w celu określenia poszczególnych części zapytania SQL. W ten sposób dołącza te różne części do poprawnego zapytania SQL, które może zostać później wywołane poprzez metody DAO opisane w [obiekcie dostępu do bazy danych (DAO)](/doc/guide/database.dao). Następujący kod pokazuje typowe użycie konstruktora zapytań tworzące zapytanie SELECT w SQL-u:

~~~
[php]
$user = Yii::app()->db->createCommand()
	->select('id, username, profile')
	->from('tbl_user u')
	->join('tbl_profile p', 'u.id=p.user_id')
	->where('id=:id', array(':id'=>$id))
	->queryRow();
~~~

Konstruktora zapytań najlepiej używać gdy potrzebujesz złożyć instrukcje SQL proceduralnie lub też bazując na pewnych warunkacj logicznych zaistniałych w twojej aplikacji. Głównymi korzyściami stosowania konstruktora zapytań są:

* możliwość tworzenia skomplikowanych instrukcji SQL w sposób programistyczny.

* automatyczne dodawanie cudzysłowu do nazw tabeli i ich kolumn w celu ochrony przed konfliktem z zarezerwowanymi w SQL słowami i znakami specjalnymi.

* dodatkowo w cudzysłó brane są wartości parametrów a także używane jest wiązanie parametrów gdzie tylko to możliwe, co pozwala zredukować ryzyko ataków SQL injection.

* oferta pewnego stopnia abstrakcji bazy danych, co umożliwia migrację do innych platform bazodanowych.


Nie jest konieczne używanie kreatora zapytań. W rzeczywistości, jeśli twoje zapytania są proste, łatwiej i szybciej napisać je bezpośrednio w instrukcji SQL. 

> Note|Uwaga: Konstruktor zapytań nie może być używany do modyfikowania istniejącego zapytania utworzonego za pomocą instrukcji SQL.
> Na przykład, poniższy kod nie zadziała:
>
> ~~~
> [php]
> $command = Yii::app()->db->createCommand('SELECT * FROM tbl_user');
> // poniższa linia NIE DOŁĄCZY klauzuli WHERE do powyższego zapytania SQL
> $command->where('id=:id', array(':id'=>$id));
> ~~~
>
> Inaczej rzecz ujmując, nie należy jednocześnie wykorzystywać zwykłego SQL-a z konstruktorem zapytań.


Przygotowanie konstruktora zapytań.
-----------------------

Konstruktor zapytań dostarczony jest w ramach [CDbCommand], głównej klasy zapytań bazodanowych opisanych w [DAO](/doc/guide/database.dao).

Aby rozpocząć używanie konstruktora zapytań tworzymy nową instancję [CDbCommand] w następujący sposób:

~~~
[php]
$command = Yii::app()->db->createCommand();
~~~

Oznacza to, że używamy `Yii::app()->db` w celu uzyskania połączenia z bazą danych a następnie wywołujemy [CDbConnection::createCommand()] w celu utworzenia potrzebnej instancji polecenia (ang. command).

Zauważ, że zamiast przekazywania całej instrukcji SQL do wywołania `createCommand()` tak jak robiliśmy to w [DAO](/doc/guide/database.dao), pozostawiamy je puste. Dzieje się tak, dlatego, że będziemy tworzyć pojedynczo poszczególne części instrukcji SQL używając metod konstruktora zapytań, które opiszemy w dalszej części.


Tworzenie zapytań zwracających dane
----------------

Zapytania zwracające dane odnoszą się do instrukcji SELECT w SQL-u. Konstruktor zapytań dostarcza zbioru metod tworzących poszczególne części instrukcji SELECT. Ponieważ wszystkie te metody zwracają instancję [CDbCommand], możemy wywoływać je łąńcuchowo, tak jak pokazano to w przykładzie na początku tego rozdziału.

* [select()|CDbCommand::select()]: definiuje część SELECT zapytania
* [selectDistinct()|CDbCommand::selectDistinct]: definiuje część SELECT zapytania i zwraca flagę DISTINCT
* [from()|CDbCommand::from()]: definiuje część FROM zapytania
* [where()|CDbCommand::where()]: definiuje część WHERE zapytania
* [join()|CDbCommand::join]: dołącza fragment zapytania inner join 
* [leftJoin()|CDbCommand::leftJoin]: dołącza fragment zapytania outer join 
* [rightJoin()|CDbCommand::rightJoin]: dołącza fragment zapytania right inner join 
* [crossJoin()|CDbCommand::crossJoin]: dołącza fragment zapytania cross join 
* [naturalJoin()|CDbCommand::naturalJoin]: dołącza fragment zapytania natural join 
* [group()|CDbCommand::group()]: definiuje część GROUP BY zapytania
* [having()|CDbCommand::having()]: definiuje część HAVING zapytania
* [order()|CDbCommand::order()]: definiuje część ORDER BY zapytania
* [limit()|CDbCommand::limit()]: definiuje część LIMIT zapytania
* [offset()|CDbCommand::offset()]: definiuje część OFFSET zapytania
* [union()|CDbCommand::union()]: dołącza fragment zapytania UNION 


W dalszej części, wyjaśnimi jak używać tych metod kreatora zapytań. Zakładamy, że używamy MySQL. Zauważ, że jeśli używasz innego DBMS, dołączanie cudzysłowów do tabel/kolumn/wartości może się różnić od tego z przykładów.


### select()

~~~
[php]
function select($columns='*')
~~~

Metoda [select()|CDbCommand::select()] określa część `SELECT` zapytania. Parametr `$columns`, który może mieć postać łańcucha znaków reprezentującego rodzielone przecinkami nazwy kolumn lub też tablicy z nazwami kolumn, definiuje kolumny które zostaną wybrane w zapytaniu. Nazwy kolumn mogą zawierać prefixy tabel i/lub aliasy kolumn. Metoda ta automatycznie zacytuje (doda cudzysłowy) nazwy kolumn, chyba że kolumna zawiera nawiasy (co oznacza, że kolumna jest wyrażeniem bazodanowym).

Poniżej znajduje się kilka przykładów:

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

Metoda [selectDistinct()|CDbCommand::selectDistinct] jest podobna do [select()|CDbCommand::select()] z tą różnicą że zwraca ona flagę `DISTINCT`. Na przykład, `selectDistinct('id, username')` wygeneruje następujący SQL:

~~~
SELECT DISTINCT `id`, `username`
~~~


### from()

~~~
[php]
function from($tables)
~~~

Metoda [from()|CDbCommand::from()] określa część `FROM` zapytania. Może nią być zarówno łańcuch znaków reprezentujący oddzielone przecinkami nazwy tabel lub też tablica z nazwami tabel. Nazwy tabel mogą zawierać prefiksy schematów bazy danych (np. `public.tbl_user`) i/lub aliasy tabel (np. `tbl_user u`). Metoda ta automatycznie doda cudzysłowy do nazw tabel, chyba że zawierają one nawiasy (co oznacza, że tabela została przekazana jako podzapytanie lub wyrażenie).

Poniżej znajduje się kilka przykładów:

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

Metoda [where()|CDbCommand::where()] określa część `WHERE` zapytania. Parametr `$conditions` określa warunki zapytania a `$params` definiuje parametry, które zostaną związanane go całego zapytania. Parametr metody `$conditions` może być zarówno łańcuchem (np. `id=1`) lub też tablicą o następującym formacie:

~~~
[php]
array(operator, argument1, argument2, ...)
~~~

gdzie `operator` może być jednym z: 

* `and`: argumenty powinny zostać złączone razem przy użyciu `AND`. Na przykład, `array('and', 'id=1', 'id=2')` wygeneruje `id=1 AND id=2`. Jeśli argument jest tablicą, zostanie on przekonwertowany do łańcucha przy użyciu przed chwilą opisanej reguły. Na przykład, `array('and', 'type=1', array('or', 'id=1', 'id=2'))` wygeneruje `type=1 AND (id=1 OR id=2)`. Metoda ta NIE dodaje cudzysłowów ani nie wycina znaków sterujących (ang. quoting and escaping).

* `or`: podobny do operatora `and` z tą różnicą, że argumenty łączone są za pomocą OR.

* `in`: pierwszy argument powinien być kolumną bądź też wyrażeniem bazodanowym, a drugi argument powinien być tablicą reprezentującą zakres wartości w których kolumna lub wyrażenie powinny się znajdować. Na przykład, `array('in', 'id', array(1,2,3))` wygeneruje `id IN (1,2,3)`. Metoda ta poprawnie doda cudzysłowy do nazw kolumn oraz wytnie znaki sterujące dla wartości w zakresie.

* `not in`: podony do operatora `in` z tą różnicą, że `IN` zastąpione jest `NOT IN` w wygenerowanym warunku.

* `like`: pierwszy argument powinien być kolumną lub też wyrażeniem bazodanowym, a drugi argument łańcuchem lub tablicą reprezentującą zakres wartości, do których je przypasowujemy. Na przykład, `array('like', 'name', '%tester%')` wygeneruje `name LIKE '%tester%'`. Jeśli przekazany zostałzakres wartości w postaci tablicy, `LIKE` zostanie  wielokrotnie zostanie użyte i połączone za pomocą operatora `AND`. Na przykład, `array('like', 'name', array('test', 'sample'))` wygeneruje `name LIKE '%test%' AND name LIKE '%sample%'`. Metoda ta poprawnie doda cudzysłowy oraz wytnie znaki sterujace dla wartości w zakresie.

* `not like`: podobny do operatora `like` z tą różnicą, że `LIKE` zastąpione jest `NOT LIKE` w wygenerowanym warunku.

* `or like`: podobne do operatora `like` z tą różnicą, że `OR` jest używany do łączenia kilku predykatów `LIKE`.

* `or not like`: podobne do operatora `not like` z tą różnicą, że `OR` jest używany do łączenia kilku predykatów `NOT LIKE`.


Poniżej znajduje się kilka przykładów używania `where`:

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

Zauważ, że jeśli operator zawiera `like`, musimy bezpośrednio zdefiniować symbole wieloznaczne (takie jak `%` i `_`) we wzorcu. Jeśli wzorce pochodzą z danych wejściowych użytkownika, powinniśmy również użyć następującego kodu w celu usunięcia znaków specjalnych aby zapobiec traktowaniu ich jako symbole wieloznaczne:

~~~
[php]
$keyword=$_GET['q'];
// usuwanie znaków % oraz _ 
$keyword=strtr($keyword, array('%'=>'\%', '_'=>'\_'));
$command->where(array('like', 'title', '%'.$keyword.'%'));
~~~


### order()

~~~
[php]
function order($columns)
~~~

Metoda [order()|CDbCommand::order()] określa część `ORDER BY` zapytania. 
Parametr `$columns` definiuje kolumny, po których będziemy sortować. Może on być zarówno łańcuchem reprezentującym rozdzielone przecinkiem kolumny i kierunki sortowania (`ASC` or `DESC`) lub tablicą kolumn i kierunków sortowania. Nazwy kolumn mogą zawierać prefiksy tabeli. Metoda automatycznie doda cudzysłowy do nazwy kolumn chyba, że kolumna zawiera nawiasy (co oznacze, że kolumna jest wyrażeniem bazodanowym).

Poniżej znajduje się kilka przykładów:

~~~
[php]
// ORDER BY `name`, `id` DESC
order('name, id desc')
// ORDER BY `tbl_profile`.`name`, `id` DESC
order(array('tbl_profile.name', 'id desc'))
~~~


### limit() oraz offset()

~~~
[php]
function limit($limit, $offset=null)
function offset($offset)
~~~

Metody [limit()|CDbCommand::limit()] oraz [offset()|CDbCommand::offset()] określają cześć `LIMIT` i `OFFSET` zapytania. Zauważ, że część DBMS może nie wspierać składni `LIMIT` oraz `OFFSET`. W takim przypadku konstruktor zapytań przepisze całą instrukcję tak aby zasymulować funkcje limit i offset.

Poniżej znajduje się kilka przykładów:

~~~
[php]
// LIMIT 10
limit(10)
// LIMIT 10 OFFSET 20
limit(10, 20)
// OFFSET 20
offset(20)
~~~


### join() oraz jego warianty

~~~
[php]
function join($table, $conditions, $params=array())
function leftJoin($table, $conditions, $params=array())
function rightJoin($table, $conditions, $params=array())
function crossJoin($table)
function naturalJoin($table)
~~~

Metoda [join()|CDbCommand::join()] i jej warianty określa w jaki sposób tabele złączyć pozostałe tabele używając `INNER JOIN`, `LEFT OUTER JOIN`, `RIGHT OUTER JOIN`, `CROSS JOIN` lub `NATURAL JOIN`. Parametr `$table` definiuje nazwę tabeli dołączanej. Nazwa tabeli może zawierać prefiks schematu bazy danych i/lub alias. Metoda ta doda cudzysłowy do nazw tabel, chyba że zawiera ona nawiasy, bo oznacze że jest ona wyrażeniem bądź podzapytaniem. Parametr `$conditions` określa warunki złączenia. Jego składnia jest taka sama jak [where()|CDbCommand::where()]. Parametr `$params` określa parametry, które zostaną związane z całym zapytaniem. 

Zauważ, że w odróżnieniu do pozostałych metod konstruktora zapytań każde wywołanie metody join zostanie dołączone do poprzedniego wywołania.

Poniżej znajduje się kilka przykładów:

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

Metoda [group()|CDbCommand::group()] określa część  `GROUP BY` zapytania.
Parametr `$columns` definiuje kolumny, po których będziemy grupować. Może nim być zarówno łańcuch reprezentujący rozdzielone przecinkami kolumny jak i tablica kolumn. Nazwy kolumn mogą zawierać prefiksy tabel. Metoda ta automatycznie doda cudzysłów do nazwy kolumn chyba że zawierają one nawiasy (co oznacza, że kolumna jest wyrażeniem bazodanowym).

Poniżej znajduje się kilka przykładów:

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

Metoda [having()|CDbCommand::having()] określa część `HAVING` zapytania. Jej użycie jest identyczne z [where()|CDbCommand::where()].

Poniżej znajduje się kilka przykładów:

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

Metoda [union()|CDbCommand::union()] określa część `UNION` zapytania. Dołącza ona wartość `$sql` do istniejącego zapytania SQL przy użyciu operatora `UNION`. Kilkukrotne wywołanie `union()` spowoduje dołączenie kilku SQLi do istniejącej instrukcji SQL.

Poniżej znajduje się kilka przykładów:

~~~
[php]
// UNION (select * from tbl_profile)
union('select * from tbl_profile')
~~~


### Wykonywanie zapytań

Po wywołaniu powyższych metod konstruktora zapytań tworzących zapytanie, możemy wywołać metody DAO, tak jak je opisano w [DAO](/doc/guide/database.dao) celem wykonania zapytania. Na przykład, możemy wywołać metodę [CDbCommand::queryRow()] aby zwrócić wiersz rezultatu lub też metodę [CDbCommand::queryAll()] aby otrzymać wszystkie wiersze naraz.
Oto przykład:

~~~
[php]
$users = Yii::app()->db->createCommand()
	->select('*')
	->from('tbl_user')
	->queryAll();
~~~


### Zwracanie SQLi

Poza zwracaniem zapytań utworzonych przez konstruktora zapytań, możemy zwracać również odpowiadające in instrukcje SQL dzięki wywołaniu metody [CDbCommand::getText()].

~~~
[php]
$sql = Yii::app()->db->createCommand()
	->select('*')
	->from('tbl_user')
	->text;
~~~

Jeśli posiadamy jakiekolwiek parametry, które powinny zostać powiązane z zapytaniem, mogą one zostać zwrócone przez właściwość [CDbCommand::params].


### Alternatywna składnia tworzenia zapytań

Czasami, tworzenie łańcucha metod w celu utworzenia zapytania może nie być optymalnym wyborem. Kreator zapytań pozwala tworzyć zapytanie poprzez proste przypiania do właściwości obiektu. W szczególności, dla każdej metody konstruktora zapyta istnieje odpowiadająca jej właściwość o tej samej nazwie. Przypisanie wartości do tej właściwości jest jednoznaczne z wywołaniem odpowiadającej tej właściwości metody. Na przykład, następujące dwie instrukcje są równoznaczne, przy założeniu, że zmienna `$command` reprezentuje obiekt [CDbCommand]:

~~~
[php]
$command->select(array('id', 'username'));
$command->select = array('id', 'username');
~~~

Ponadto, do metody [CDbConnection::createCommand()] możemy przekazać parametr w postaci tablicy. Pary nazwa-wartość w tablicy będą użyte w celu zainicjalizowania właściwości tworzonej instancji [CDbCommand]. Oznacza to, że możemy używać następującego kodu celem utworzenia zapytania:

~~~
[php]
$row = Yii::app()->db->createCommand(array(
	'select' => array('id', 'username'),
	'from' => 'tbl_user',
	'where' => 'id=:id',
	'params' => array(':id'=>1),
))->queryRow();
~~~


### Tworzenie wielu zapytań

Instancja [CDbCommand] może być wielokrotnie używana do konstruowania kilku zapytań. Jednakże, przed utworzeniem nowego zapytania, należy wywołać metodę [CDbCommand::reset()] aby wyczyścić poprzednie zapytanie. Na przykład:

~~~
[php]
$command = Yii::app()->createCommand();
$users = $command->select('*')->from('tbl_users')->queryAll();
$command->reset();  // wyszyść poprzednie zapytanie
$posts = $command->select('*')->from('tbl_posts')->queryAll();
~~~


Tworzenie zapytań manipulujących danymi
----------------------------------

Zapytania manipulujące danymi odnoszą się do instrukcji SQL dla wstawiania, aktualizowania oraz usuwania danych z tabeli bazodanowej. Kreator zapytań dostarcza dla nich odpowiednio metody `insert`, `update` oraz `delete`. W odróżnieniu do metod tworzących zapytanie SELECt opisanych wcześniej, każda z tych metod manipulujących danymi określa całe zapytanie SQL.

* [insert()|CDbCommand::insert]: wstawia wiersz do tabeli
* [update()|CDbCommand::update]: aktualizuje dane w tabeli
* [delete()|CDbCommand::delete]: usuwa dane z tabeli


Poniżej opiszemy te metody.


### insert()

~~~
[php]
function insert($table, $columns)
~~~

Metoda [insert()|CDbCommand::insert] buduje i wywołuje instrukcję `INSERT`. Parametr `$table` definiuje, do której tabeli będziemy dodawać, zaś `$columns` jest tablicą par nazwa-wartość określających wartości wstawiane do kolumn. Metoda ta doda poprawnie cudzysłowy do nazwy tabeli oraz użyje wiązania parametrów dla wartości, które mają zostać wstawione.

Oto przykład:

~~~
[php]
// zbuduj i wykonaj następujący kod SQL:
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

Metoda [update()|CDbCommand::update] buduje i wywołuje instrukcję SQL `UPDATE`. Parametr `$table` definiuje którą tabelę będziemy aktualizować; parametr `$columns` jest tablicą par nazwa-wartość określających watrości kolumn, które zostaną zaktualizowane; parametry `$conditions` oraz `$params` są takie jak w metodzie [where()|CDbCommand::where()], która opisuje klauzulę `WHERE` w instrukcji `UPDATE`. Metoda ta doda poprawnie cudzysłów do nazwy tabeli i użyje wiązania parametrów dla wartości, które będą zaktualizowane.

Oto przykład:

~~~
[php]
// zbuduj i wykonaj następujący kod SQL:
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

Metoda [delete()|CDbCommand::delete()] buduje i wywołuje instrukcję SQL `DELETE`. Parametr `$table` definiuje, która tabela zostanie zaktualizowana; parametry `$conditions` oraz `$params` są takie jak w metodzie [where()|CDbCommand::where()], która określa klauzulę `WHERE` w instrukcji `DELETE`. Metoda ta doda poprawnie cudzysłowy do nazwy tabeli.

Oto przykład:

~~~
[php]
// zbuduj i wykonaj następujący kod SQL:
// DELETE FROM `tbl_user` WHERE id=:id
$command->delete('tbl_user', 'id=:id', array(':id'=>1));
~~~

Tworzenie zapytań manipulująych schematami
------------------------------------

Poza zwykłymi zapytaniami zwracającymi i manipulującymi danymi, kreator zapytań oferuje zestaw metod tworzących i wykonywujących zapytania SQL, które potrafią manipulować schematem bazy danej. W szczegolności wspiera on następujące zapytania:

* [createTable()|CDbCommand::createTable]: tworzy tabelę
* [renameTable()|CDbCommand::renameTable]: zmienia nazwę tabeli
* [dropTable()|CDbCommand::dropTable]: usuwa tabelę
* [truncateTable()|CDbCommand::truncateTable]: usuwa wszystkie wpisy z tabeli
* [addColumn()|CDbCommand::addColumn]: dodaje kolumnę do tabeli
* [renameColumn()|CDbCommand::renameColumn]: zmienia nazwę kolumny tabeli 
* [alterColumn()|CDbCommand::alterColumn]: zmienia kolumnę tabeli
* [dropColumn()|CDbCommand::dropColumn]: usuwa kolumnę tabeli
* [createIndex()|CDbCommand::createIndex]: tworzy index
* [dropIndex()CDbCommand::dropIndex]: usuwa index

> Info|Info: Pomimo faktu iż aktualnie zapytania SQL manipulujące schematami bazy danych różnią się znacznie dla różnych DBMS, to kreator zapytań stara się dostarczyć ujednoliconego interfejsu tworzącego te zapytania. Upraszcza to zadanie migracji baz danych z jednego DBMS do innego.


###Abstrakcyjne typy danych

Kreator zapytań wprowadza zestaw abstrakcyjnych typów danych, które mogą być używane do definiowania kolumn tabeli. W odróżnieniu do fizycznych typów danych, które są określone dla każdego z DBMS i różnią się dla różnych DBMS, abstrakcyjne typy danych są niezależne od DBMS. Kiedy abstrakcyjny typ danych używany jest do definiowania kolumn tabeli, konstruktor zapytań przekonwertuje je do odpowiadających im fizycznych typów danych.

Następujące abstrakcyjne typy danych wspomagane są przez kreator zapytań.

* `pk`: generyczny klucz podstawowy, dla MySQL-a zostanie on przekonwertowany na `int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY`;
* `string`: typ łańcuchowy, dla MySQL-a zostanie on przekonwertowany do `varchar(255)`;
* `text`: typ tekstowy (długi ciąg łańcuchowy), dla MySQL-a zostanie on przekonwertowany na `text`;
* `integer`: liczba całkowita, dla MySQL-a zostanie pzrekonwertowana na `int(11)`;
* `float`: liczba rzeczywista, dla MySQL-a zostanie przekonwertowana na `float`;
* `decimal`: typ dziesiętny, dla MySQL-a zostanie przekonwertowany na `decimal`;
* `datetime`: typ reprezentujący datę i czas, dla MySQL-a zostanie przekonwertowany na `datetime`;
* `timestamp`: stępel czasu, dla MySQL-a zostanie przekonwertowany na `timestamp`;
* `time`: typ reprezentujący czas, dla MySQL-a zostanie przekonwertowany na `time`;
* `date`: typ reprezentujący datę, dla MySQL-a zostanie przekonwertowany na `date`;
* `binary`: typ danych binarnych, dla MySQL-a zostanie przekonwertowany na `blob`;
* `boolean`: typ logiczny, dla MySQL-a zostanie przekonwertowany na `tinyint(1)`;
* `money`: typ walutowy, dla MySQL-a zostanie przekonwertowany na `decimal(19,4)`. Typ ten jest dostępny od wersji 1.1.8.


###createTable()

~~~
[php]
function createTable($table, $columns, $options=null)
~~~

Metoda [createTable()|CDbCommand::createTable] buduje i wykonuje instrukcję SQL tworzącą tabelę. Parametr `$table` określa nazwę tabeli, która zostanie utworzona. Parametr `$columns` określa kolumny w nowej tabeli. Muszą one przekazane jako pary nazwa-definicja (np. `'username'=>'string'`). Parametr `$options` określa dodatkowe fragmentu SQL-a, które powinny zostać dołączone do generowanego SQL-a. Kreator zapytań doda odpowiednio cudzysłowy do nazwy tabeli.

Podczas definiowana kolumn można używac opisanych wcześniej abstrakcyjnych typów danych. Konstruktor zapytań przekonwertuje je odpowiadających im fizycznych typów danych, odpowiednio do aktualnie używanego DBMS. Na przykład, `string` zostanie przekonwertowany do `varchar(255)` dla MySQL-a.

Definicja kolumny może również zawierać nieabstrakcyjne typy danych oraz definicje. Będą one umieszczone w generowanych SQL-u bez zmian. Na przykład, `point` nie jest abstrakcyjnym typem danych i jeśli zostanie użyty w definicji kolumny, pojawi się niezmieniony w zwracanym SQL-u; zaś `string NOT NULL` zostanie przekonwertowany do `varchar(255) NOT NULL` (tylko abstrakcyjny typ `string` został przekonwertowany).

Poniżej znajduje się przykład pokazujący jak utworzyć tabelę:

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

Metoda [renameTable()|CDbCommand::renameTable] buduje i wykonuje instruckję SQL zmieniające nazwę tabeli. Parametr `$table` określa nazwę tabeli, której nazwa zostanie zmieniona. Parametr `$newName` określa nowa nazwę tej tabeli. Kreator zapytań doda prawidłowo cudzysowy do nazw abeli.

Poniżej znajduje się przykład pokazujący jak zmienić nazwę tabelę:

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

Metoda [dropTable()|CDbCommand::dropTable] buduje i wykonuje instrukcję SQL usuwające tabelę. Parametr `$table` określa nazwę tabeli, która zostanie usunięta. Kreator zapytań doda prawidłowo cudzysłowy do nazwy tabeli.

Poniżej znajduje się przykład pokazujący jak usunąć tabelę:

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

Metoda [truncateTable()|CDbCommand::truncateTable] buduje i wykonuje instrukcję SQL czyszczącą tabelę. Parametr `$table` określa nazwę tabeli, która zostanie wyczyszczona. Kreator zapytań doda prawidłowo cudzysłowy do nazwy tabeli.

Poniżej znajduje się przykład pokazujący jak wyczyścić tabelę:

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

Metoda [addColumn()|CDbCommand::addColumn] buduje i wykonuje instrukcję SQL dodające nową kolumnę do tabeli. Parametr `$table` określa nazwę tabeli do której nowa kolumna zostanie dodana. Parametr `$column` określa nazwę nowej kolumny a parametr `$type` specyfikuje definicję nowej kolumny. Definicja kolumny może zawierać abstrakcyjne typy danaych, tak jak opisano to w "createTable". Kreator zapyta doda prawidłowo cudzysłowy dla nazwy tabeli oraz nazwy kolumny.

Poniżej znajduje się przykład pokazujący jak dodać kolumnę do tabeli:

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

Metoda [dropColumn()|CDbCommand::dropColumn] buduje i wykonuje instrukcję usuwającą kolumnę w tabeli. Parametr `$table` określa nazwę tabeli, której kolumna będzie usunięta. Parametr `$column` określa nazwę kolumny, która zostanie usunięta. Konstruktor zapytań doda prawidłowo cudzysłowy dla nazwy tabeli oraz nazwy kolumny.

Poniżej znajduje się przykład pokazujący jak usunąć kolumnę z tabeli:

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

Metoda [renameColumn()|CDbCommand::renameColumn] buduje i wykonuje instrukcję SQL służąca zmianie nazw kolumny w tabeli. Parametr `$table` określa nazwę tabeli, w której nazwa kolumn zostanie zmieniona. Parametr `$name` określa starą nazwę kolumny. Parametr `$newName` definiuje nową nazwę kolumny. Konstruktor zapyta doda prawidłowo cudzysłowy zarówno dla nazwy tabli jak i dla nazw kolumn. 

Poniżej znajduje się przykład pokazujący jak zmienić nazwę kolumny w tabeli:

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

Metoda [alterColumn()|CDbCommand::alterColumn] buduje i wykonuje instrukcję SQL zmieniającą kolumnę tabeli. Parametr `$table` określa nazwę tabeli, której kolumna będzie zmieniania. Parametr `$column` określa nazwę kolumny, która będzie zmieniana. Parametr `$type` określa nową definicję kolumny. Definicja kolumny może zawierać abstrakcyjne typy danych, tak jak to zostało opisane dla "createTable". Kreator zapyta doda prawidłowo cudzysłowy zarówno dla nazwy tabeli jak i dla nazwy kolumny.

Poniżej znajduje się przykład pokazujący jak zmienić kolmnę w tabeli:

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
Metoda [addForeignKey()|CDbCommand::addForeignKey] buduje i wykonuje instrukcję SQL dodającą ograniczenia klucza obcego w tabeli. Parametr `$name` określa nazwę klucza obcego. Parametry `$table` oraz `$columns` określają nazwę tabeli oraz nazwę kolumny, której ten klucz obcy dotyczy. Jeśli podajemy wiele kolumn, powinny być one rozdzielone znakiem spacji. Parametry `$refTable` oraz `$refColumns` określają nazwę tabeli oraz kolumny, do której klucz obcy referuje. Parametry `$delete` oraz `$update` określają odpowiednio opcję `ON DELETE` oraz `ON UPDATE` w instrukcji SQL. Większość DBMS wspiera następujące opcje: `RESTRICT`, `CASCADE`, `NO ACTION`, `SET DEFAULT`, `SET NULL`. Konstruktor zapytań prawidłowo doda cudzysłowy do nazwy tabeli, nazwy indeksu oraz nazw(y) kolumn(y).


Poniżej znajduje się przykład pokazujący jak dodać ograniczenie klucza obcego:

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

Metoda [dropForeignKey()|CDbCommand::dropForeignKey] buduje i wykonuje instrukcję SQL usuwającą ograniczenie klucza obcego. Parametr `$name` określa ograniczenie klucza obcego, który zostanie usunięty. Parametr `$table` określa nazwe tabeli, w której ten klucz się znajduje. Konstruktor zapytań doda odpowiednio cudzysłowy do nazwy tabeli jak i do nazwy ograniczeń.

Poniżej znajduje się przykład pokazujący jak usunąć ograniczenie dla klucza obcego:

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

Metoda [createIndex()|CDbCommand::createIndex] buduje i wykonuje instrukcję SQL tworzącą indeks. Parametr `$name` określa nazwę indeksu, który zostanie utworzony. Parametr `$table` określa nazwę tabeli w której znajduje się index. Parametr `$column` określa nazwę kolumny, która zostanie zindeksowana. Parametr `$unique` określa, czy tworzony indeks powinien być unikalny. Jeśli indeks zawiera wiele kolumn, muszą być one rozdzielone przecinkami. Konstruktor zapytań doda poprawnie cudzysłowy do nazw tabeli, nazw indeksów oraz nazw kolumn(y).

Poniżej znajduje się przykład pokazujący jak utworzyć indeks.

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

Metoda [dropIndex()|CDbCommand::dropIndex] buduje i wykonuje instrukcję SQL usuwającą index. Parametr `$name` określa nazwę indeksu, który będzie usuwany. Parametr `$table` określa nazwę tabeli, w której znajduje się indeks. Kreator zapytań doda cudzysłowy zarówno do nazw tabeli jak i do indeksu.

Poniżej znajduje się przykład pokazujący jak usunąć indeks.

~~~
[php]
// DROP INDEX `idx_username` ON `tbl_user`
dropIndex('idx_username', 'tbl_user')
~~~

<div class="revision">$Id: database.query-builder.txt 3408 2011-09-28 20:50:28Z alexander.makarow $</div>