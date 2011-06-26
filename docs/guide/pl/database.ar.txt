Wzorzec Rekordu Aktywnego (ang. Active Record)
==============================================

Chociaż Yii DAO może uporać się z wirtualnymi oraz bazodanowymi zadaniami, istnieje 
szansa, że będziemy spędzać 90% naszego czasu pisząc wyrażenia SQL, które wykonują 
operacje CRUD (create - tworzenie, read - czytanie, update - aktualizowanie oraz 
delete - usuwanie). Trudno również jest zarządzać kodem, który jest pomieszany 
z wyrażeniami SQL. Do rozwiązania tych problemów możemy użyć wzorca Rekordu Aktywnego (ang. Active Record).


Aktywny Rekord (AR) jest popularną techniką mapowania obiektowo-relacyjnego. 
Każda klasa AR reprezentuje tabelę bazy danych (lub widok), których atrybuty reprezentowane są 
poprzez atrybuty klasy AR a instancja AR reprezentuje wiersz w  tej tabeli. 
Wspólne operacje CRUD są zaimplementowane jako metody AR. W rezultacie, posiadamy dostęp do 
naszych danych w bardziej obiektowo zorientowany sposób. Na przykład: możemy użyć poniższego kodu
aby wstawić nowy wiersz do tabeli `tbl_ost`:

~~~
[php]
$post=new Post;
$post->title='przykładowy post';
$post->content='zawartość postu';
$post->save();
~~~

W dalszej części opiszemy jak utworzyć AR i użyć go do wykonywania operacji CRUD. 
W następnej sekcji pokażemy jak używać AR by radzić sobie z relacjami w bazach danych. 
Dla uproszczeniami używamy w przykładach tej sekcji następującej tabeli bazy danych.
Zauważ, że jeśli używasz bazy danych MySQL, powinieneś zamienić `AUTOINCREMENT` na `AUTO_INCREMENT` 
w poniższym kodzie SQL. 


~~~
[sql]
CREATE TABLE tbl_post (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	title VARCHAR(128) NOT NULL,
	content TEXT NOT NULL,
	create_time INTEGER NOT NULL
);
~~~

> Tip|Wskazówka: AR nie został pomyślany do rozwiązywania wszystkich zadań związanych z bazą danych. 
Najlepiej używać go do modelowania tabel baz danych w konstrukcjach PHP oraz wykonywania 
zapytań, które nie zawierają skomplikowanego kodu SQL. Dla tych skomplikowanych 
scenariuszy powinno używać się Yii DAO.


Ustanawianie połączeń DB
--------------------------

AR jest zależy od połączenia DB gdy wykonuje operacje zależne od DB. Domyślnie, 
zakłada, że komponent aplikacji `db` dostarcza wymaganej instancji [CDbConnection] 
która reprezentuje połączenie z bazą danych. Poniżej znajduje się przykładowa konfiguracja 
aplikacji

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'system.db.CDbConnection',
			'connectionString'=>'sqlite:path/to/dbfile',
			// włączenie cache'owania schematu celem zwiększenia wydajności
			// 'schemaCachingDuration'=>3600,
		),
	),
);
~~~

> Tip|Wskazówka: Ponieważ Rekord Aktywny zależy od metadanych tabeli zawierających 
informacje o kolumnach, zajmuje mu to nieco czasu, aby je odczytać oraz zanalizować. 
Jeśli prawdopodobieństwo, że schemat twojej bazy danych jest małe, powinieneś 
włączyć cache'owanie schematu bazy danych poprzez skonfigurowanie właściwości 
[CDbConnection::schemaCachingDuration] poprzez przypisanie jej wartości większej niż 0.

Wsparcie dla AR jest ograniczone przez DBMS. Aktualnie, tylko następujące DBMS są wspierane:

   - [MySQL 4.1 lub późniejsze](http://www.mysql.com)
   - [PostgreSQL 7.3 lub późniejsze](http://www.postgres.com)
   - [SQLite 2 oraz 3](http://www.sqlite.org)
   - [Microsoft SQL Server 2000 lub nowsze](http://www.microsoft.com/sqlserver/)
   - [Oracle](http://www.oracle.com)

Jeśli chcesz używać komponentu aplikacji innego niż `db` lub jeśli chcesz pracować z wieloma 
bazami danych używając AR, powinieneś nadpisać metodę [CActiveRecord::getDbConnection()]. 
Klasa [CActiveRecord] jest klasą bazową dla wszystkich klas AR.

> Tip|Wskazówka: Istnieją dwa sposoby pracowania z wieloma bazami danych z użyciem AR. 
Jeśli schematy bazy danych różnią się, możesz utworzyć różne bazowe klasy AR z różniącymi się 
implementacjami metody [getDbConnection()|CActiveRecord::getDbConnection]. W przeciwnym przypadku, 
lepszym pomysłem jest dynamiczna zmiana statycznej zmiennej [CActiveRecord::db].

Definiowanie klasy AR
-----------------

Aby uzyskać dostęp do tabeli bazy danych musimy najpierw zdefiniować klasę AR poprzez 
rozszerzenie [CActiveRecord]. Każda klasa AR reprezentuje jedną tabele bazy danych 
a jedna instancja reprezentuje wiersz tej tabeli. Następujący przykład pokazuje minimalny 
kod potrzebny aby klasa AR reprezentowała tabelę `tbl_ost`.

~~~
[php]
class Post extends CActiveRecord
{
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
~~~

> Tip|Wskazówka: Ponieważ klasy AR są często używane w wielu różnych miejscach, 
> możemy zaimportować zawartość folderu zawierającego klasy AR zamiast
> dołączać je jedna po drugiej. Na przykład, jeśli wszystkie nasze pliki zawierające klasy AR 
> znajdują się w katalogu `protected/models`, możemy skonfigurować aplikację w następujący sposób:
> ~~~
> [php]
> return array(
> 	'import'=>array(
> 		'application.models.*',
> 	),
> );
> ~~~

Domyślnie, nazwa klasy AR jest identyczna z nazwą tabeli w bazie danych. Jeśli nazwa 
klasy różni się, nadpisz metodę [tableName()|CActiveRecord::tableName]. Metoda [model()|CActiveRecord::model] 
jest zadeklarowana jako taka dla każdej klasy AR (wyjaśnienie tego nastąpi później).

> Info|Info: Aby móc używać [prefiksów tablic](/doc/guide/database.dao#using-table-prefix)
> metoda [tableName()|CActiveRecord::tableName] dla klasy AR
> może być nadpisana następująco,
> ~~~
> [php]
> public function tableName()
> {
>     return '{{post}}';
> }
> ~~~
> Oznacza to, że zamiast zwracać pełną i dokładną nazwę tabeli, zwracamy jej nazwę bez 
> prefiksu, dodatkowo zamkniętą w podwójnych nawiasach klamrowych.  

Wartości kolumn wiersza tabeli są dostępne jako właściwości odpowiednich instancji klasy AR.
Na przykład, następujący kod ustawia kolumnę (atrybut) `title`:

~~~
[php]
$post=new Post;
$post->title='przykładowy post';
~~~

Chociaż nigdy bezpośrednio nie zdeklarowaliśmy właściwości `title` w klasie `tbl_ost`, 
jest ona dostępna w powyższym kodzie. Dzieje się tak, ponieważ `title` jest kolumną 
w tabeli `tbl_ost` a CActiveRecord czyni ją dostępną poprzez właściwość przy pomocy 
magicznej metody PHP `__get()`. Jeśli w ten sam sposób spróbujemy uzyskać dostęp 
do nieistniejącej kolumny wyjątek zostanie rzucony.

> Info|Info: W tym poradniku nazywamy kolumny przy użyciu notacji wielbłąda (np. `createTime`).
Czynimy tak, ze względu na to, że kolumny dostępne są w taki sam sposób jak zwykłe właściwości obiektu,
które również używają notacji wielbłąda. Chociaż, używanie notacji wielbłąda czyni nasz kod PHP 
bardziej konsekwentnym w nazewnictwie, może to powodować problemy związane z wielkością liter w pewnych DBMS.
Na przykład, PostgreSQL traktuje domyślnie nazwy kolumn jako niezależne od wielkości liter i dlatego musimy
używać nazwy kolumn podanej w cudzysłowiu w warunkach zapytania jeśli kolumna zawiera pomieszane duże i małe litery.
Z tego powodu, mądrą decyzją jest nazywanie kolumn (a także tabel) jedynie przy użyciu małych liter (np. `create_time`) 
w celu uniknięcia potencjalnych problemów z wielkością liter.

~~~
[php]
public function primaryKey()
{
	return 'id';
	// Dla złożonych kluczy głównych, zwróć następująca tablicę
	// return array('pk1', 'pk2');
}
~~~

Tworzenie rekordu
---------------

Aby wstawić nowy wiersz do tabeli bazy danych, tworzymy nową instancję odpowiedniej 
klasy AR, ustawiamy jej właściwości powiązane z kolumnami tabel i wołamy metodę 
[save()|CActiveRecord::save] aby zakończyć wstawianie.

~~~
[php]
$post=new Post;
$post->title='przykładowy post';
$post->content='zawartość przykładowego postu';
$post->create_time=time();
$post->save();
~~~

Jeśli klucz główny tabeli jest autoinkrementowalny, po wstawieniu, instancja będzie 
zawierała zaktualizowaną wartość klucza głównego. w powyższym przykładzie właściwość
`id` odpowiada wartości klucza głównego nowo wstawionego postu, mimo że nigdy nie zmienialiśmy
tej wartości bezpośrednio.

Jeśli kolumna została zdefiniowana wraz z jakąś statyczną, domyślną wartością (np. 
łańcuch znaków, liczba) w schemacie tabeli bazy danych, odpowiadająca jej własność 
w instancji AR będzie automatycznie posiadała tą wartość, gdy instancja zostanie utworzona.
Jednym ze sposobów zmiany tej wartości domyślnej jest bezpośrednie zdeklarowanie 
właściwości w klasie AR:

~~~
[php]
class Post extends CActiveRecord
{
	public $title='proszę wprowadź tytuł';
	......
}

$post=new Post;
echo $post->title;  // to wyświetli tekst: proszę wprowadź tytuł
~~~

Atrybut może mieć przypisaną wartość typu [CDbExpression]
zanim rekord zostanie zapisany (zarówno podczas wstawiania jak i aktualizacji) do bazy danych.
Na przykład, w celu zapisania stempla czasu (ang. timestamp) zwracanego przez funkcję MYSQL 
`NOW()`, możemy użyć następującego kodu:

~~~
[php]
$post=new Post;
$post->create_time=new CDbExpression('NOW()');
// $post->create_time='NOW()'; nie zadziała gdyż 
// 'NOW()' będzie potraktowany jako łańcuch znaków
$post->save();
~~~

> Tip|Wskazówka: Chociaż AR umożliwia wykonywanie operacji na bazie danych pez potrzeby
pisania uciążliwych zapytań SQL, często chcemy wiedzieć jakie zapytanie SQL jest 
wykonywane w tle przez AR. Możemy uzyskać tą informację poprzez włączenie w Yii 
[funkcjonalności logowania](/doc/guide/topics.logging). Na przykład, możemy włączyć
[CWebLogRoute] w konfiguracji aplikacji, dzięki czemu bedziemy widzieć wykonywane zapytania 
SQL na końcu każdej strony. Możemy ustawić właściwość 
[CDbConnection::enableParamLogging] jako true w konfiguracji aplikacji, tak, że wartości 
parametrów powiązanych z instrukcją SQL będą również logowane.

Odczytywanie rekordu
--------------

Aby odczytać dane z tabeli bazodanowej wołany jedną z następujących metod `find`:

~~~
[php]
// znajduje pierwszy wiersz spełniający określone warunki
$post=Post::model()->find($condition,$params);
// znajduje wiersz o konkretnym kluczu głównym
$post=Post::model()->findByPk($postID,$condition,$params);
// znajduje wiersz o określonych wartościach atrybutów
$post=Post::model()->findByAttributes($attributes,$condition,$params);
// znajduje pierwszy wiersz używając określonego wyrażenia SQL
$post=Post::model()->findBySql($sql,$params);
~~~

Powyżej, wywołujemy metodę `find` wraz z `Post::model()`. Pamiętaj, że ta statyczna metoda `model()`
jest wymagana dla każdej klasy AR. Metoda ta zwraca instancję A, która jest używana 
by otrzymać dostęp do metod na poziomie klasy w kontekście obiektu (coś podobnego
do statycznych metod klas).

Jeśli metoda `find` znajdzie wiersz spełniający warunki zapytania, zwróci ona instancję 
`tbl_ost`, której właściwości będą zawierać odpowiadające kolumnom wartości wiersza tabeli.
Możemy wtedy czytać załadowane wartości tak jak to robimy w przypadku właściwości obiektu, 
na przykład, `echo $post->title;`.

Metoda `find` zwróci wartość null, jeśli nie znajdzie niczego w bazie danych, co spełniałoby
dane warunki zapytania.

Podczas wywoływania metody `find` używamy `$condition` (warunków) oraz `$params` (parametrów) 
aby określić warunki zapytania. Tutaj `$condition` może być ciągiem znaków reprezentujących 
klauzulę `WHERE` w wyrażeniu SQL a `$params` jest tablicą parametrów, których wartości 
powinny być przypięte do placeholderów w `$condition`. Na przykład:

~~~
[php]
// znajdź wiersz z postID=10
$post=Post::model()->find('postID=:postID', array(':postID'=>10));
~~~

> Note|Uwaga: W powyższym przykładzie będziemy musieli unikać referencji do kolumny `postID`
dla pewnych DBMS. Na przykład,  jesli używamy PostrgreSQL, będziemy musieli zapisać warunek jako `"postID"=:postID`,
ze względu na to, że PostgreSQL domyślnie rozróżnia wielkość liter kolumn.

Możemy również używać `$condition` do zdefiniowania bardziej rozbudowanych warunków 
zapytań. Zamiast łańcuchem znaków, `$condition` może być instancją [CDbCriteria], 
która pozwala na określenie warunków innych niż klauzula `WHERE`. Na przykład:

~~~
[php]
$criteria=new CDbCriteria;
$criteria->select='title';  // wybierz tylko kolumnę 'title' 
$criteria->condition='postID=:postID';
$criteria->params=array(':postID'=>10);
$post=Post::model()->find($criteria); // $params nie jest wymagane
~~~

Zauważ, że kiedy używamy [CDbCriteria] jako warunków zapytania, wartość parametru `$params` 
jest niepotrzebna, gdyż może być ona zdefiniowana w [CDbCriteria], tak jak pokazano powyżej.

Alternatywnym sposobem do [CDbCriteria] jest przekazanie tablicy do metody `find`.
Klucze i wartości tablicy odpowiadają nazwom i wartościom właściwości kryteriów. 
Powyższy przykład może zostać przepisany w następujący sposób:

~~~
[php]
$post=Post::model()->find(array(
	'select'=>'title',
	'condition'=>'postID=:postID',
	'params'=>array(':postID'=>10),
));
~~~

> Info|Info: Kiedy warunki zapytania polegają ma porównywaniu kolumn 
z określonymi wartościami, możemy użyć [findByAttributes()|CActiveRecord::findByAttributes].
Parametrami `$attributes` zostaje wtedy tablica wartości indeksowana poprzez nazwy kolumn.
W części frameworków, to zadanie może zostać zrealizowane poprzez wywołanie metod 
podobnych do `findByNameAndTitle`. Chociaż to podejście wygląda bardzo atrakcyjnie 
często powoduje zamieszanie, konflikty oraz problemy np. z czułością na wielkość liter 
w nazwach kolumn.

Kiedy więcej wierszy danych pasuje do określonych warunków zapytania, możemy
dostarczyć je wszystkie razem używając następujących metod `findAll`, z których
każda ma swój odpowiednik w metodzie `find`, tak jak było to wcześniej opisane.

~~~
[php]
// znajduje wszystkie wiersze spełniające określone warunki
$posts=Post::model()->findAll($condition,$params);
// znajduje wszystkie wiersze o konkretnym kluczu głównym
$posts=Post::model()->findAllByPk($postIDs,$condition,$params);
// znajduje wiersze o określonych wartościach atrybutów
$posts=Post::model()->findAllByAttributes($attributes,$condition,$params);
// znajduje wiersze używając określonego wyrażenia SQL
$posts=Post::model()->findAllBySql($sql,$params);
~~~

Jeśli nic nie pasuje do warunków zapytania, `findAll` zwróci pustą tablicę. 
Różni się tym od metody `find`, która zwróci wartość null jeśli nic nie zostało znalezione.


Poza opisanymi powyżej metodami `find` and `findAll`, dla wygody dostarczono następujących metod:

~~~
[php]
// zwraca ilość wierszy spełniających określone warunki
$n=Post::model()->count($condition,$params);
// zwraca ilość wierszy używanych w określonym wyrażeniu SQL
$n=Post::model()->countBySql($sql,$params);
// sprawdza czy istnieje przynajmniej jeden wiersz spełniający określone warunki
$exists=Post::model()->exists($condition,$params);
~~~

Aktualizowanie rekordu
---------------

Po tym jak instancja AR została wypełniona wartościami kolumn, możemy zmienić je 
i zapisać je z powrotem w tabeli bazodanowej.

~~~
[php]
$post=Post::model()->findByPk(10);
$post->title='nowy tytuł postu';
$post->save(); // zapisz zmiany do bazy danych
~~~

Jak widać, używamy tej samej metody [save()|CActiveRecord::save] do wykonania operacji 
wstawiania jak i aktualizowania. Jeśli instancja AR została utworzona za pomocą 
operatora `new`, wywołanie [save()|CActiveRecord::save] spowoduje wstawienie nowego wiersza
do tabeli bazy danych; jeśli instancja AR jest rezultatem wywołania metody `find` 
lub `findAll`, wywołanie [save()|CActiveRecord::save] spowoduje zaktualizowanie istniejącego
wiersza w tabeli. Oczywiście, możemy użyć [CActiveRecord::isNewRecord] aby powiedzieć 
czy instancja AR jest nowa czy też nie.

Istnieje również możliwość aktualizacji jednego lub więcej wiersz w tabeli bazy danych
bez konieczności ich wcześniejszego wczytywania. AR dostarcza w tym celu następujących, 
pomocnych metod na poziomie klasy: 

~~~
[php]
// aktualizuje wiersze spełniający określone warunki
Post::model()->updateAll($attributes,$condition,$params);
// aktualizuje wiersze pasujące do określonych warunki oraz klucza(y) głównego(ych)
Post::model()->updateByPk($pk,$attributes,$condition,$params);
// aktualizuje licznik kolumn w wierszach spełniających określone warunki
Post::model()->updateCounters($counters,$condition,$params);
~~~

W powyższych przykładach, `$attributes` jest tablicą wartości kolumn indeksowanych 
przez nazwy kolumn; `$counters` jest tablicą zwiększających się wartości indeksowanych przez
nazwy kolumn; `$condition` oraz `$params` są opisane w poprzedniej sekcji.

Usuwanie rekordu
---------------

Możemy również usunąć wiersz danych jeśli instancja AR została wypełniona tym wierszem.

~~~
[php]
$post=Post::model()->findByPk(10); // zakładamy, że istnieje post, którego ID wynosi 10
$post->delete(); // usuń wiersz danych z tabeli bazy danych
~~~

Zauważ, że po usunięciu, instancja AR pozostaje niezmieniona, ale odpowiadający jej 
wiersz w tabeli bazodanowej już nie istnieje.

Następujące metody klasowe zostały dostarczone aby móc usuwać wiersze bez konieczności ich 
wcześniejszego wczytywania:

~~~
[php]
// usuń wiersze spełniające określone warunki
Post::model()->deleteAll($condition,$params);
// usuń wiersze pasujące do określonych warunków oraz klucza(y) głównego(ych)
Post::model()->deleteByPk($pk,$condition,$params);
~~~

Walidacja danych
---------------

Często podczas wstawiania lub aktualizowania wiersza mamy potrzebę sprawdzić 
czy wartości kolumny spełniają pewne warunki. Jest to szczególnie ważne, jeśli 
wartości kolumn dostarczane są przez użytkowników końcowych. Uogólniające, nigdy 
nie powinniśmy ufać niczemu przychodzącemu od strony klienta.

AR automatycznie dokonuje walidacji podczas wywoływania metody [save()|CActiveRecord::save]. 
Walidacja bazuje na regułach określonych w metodzie [rules()|CModel::rules] klasy AR.
Aby uzyskać więcej szczegółów jak określać reguły walidacji, zobacz sekcję  
[Deklarowanie reguł walidacji](/doc/guide/form.model#declaring-validation-rules). 
Poniżej znajduje się typowy przepływ podczas zapisywania rekordu.

~~~
[php]
if($post->save())
{
  // dane są poprawne oraz zostały szczęśliwie zapisane/zaktualizowane  
}
else
{
  // dane są niepoprawne. Wywołaj getErrors() aby otrzymać komunikaty błędów
}
~~~

Kiedy dane używane do wstawiania lub aktualizowania są dostarczane przez użytkownika 
końcowego za pomocą formularza HTML, potrzebujemy przypisać je do odpowiadających im
właściwości AR. Możemy to zrobić następująco:

~~~
[php]
$post->title=$_POST['title'];
$post->content=$_POST['content'];
$post->save();
~~~

Jeśli mamy wiele kolumn, będziemy widzieli długą listę takich przypisań. Można to
zmniejszyć poprzez używanie właściwości [attributes|CActiveRecord::attributes] 
w sposób jaki pokazano poniżej. Więcej szczegółów można znaleźć w sekcji 
[Zabezpieczaniu przypisań atrybutów](/doc/guide/form.model#securing-attribute-assignments)
oraz sekcji [Tworzeniu akcji](/doc/guide/form.action).

~~~
[php]
// zakładamy, że $_POST['Post'] jest tablicą wartości kolumn indeksowanych przez 
// nazwy kolumn
$post->attributes=$_POST['Post'];
$post->save();
~~~

Porównywanie rekordów
-----------------

Tak jak wiersze tabel, instancje AR są jednoznacznie identyfikowane przez ich wartości 
kluczy głównych. Dlatego też, porównując dwie instancje AR, musimy porównać jedynie ich
wartości kluczy głównych, zakładając, że należą do tej samej klasy AR. Jednakże 
najprostszym sposobem jest wywołanie [CActiveRecord::equals()].

> Info|Info: W odróżnieniu do implementacji AR w innych frameworkach, Yii wspiera 
klucze złożone w AR. Klucze złożone zawierają więcej niż jedną kolumnę. Odpowiednio, 
wartości kluczy głównych reprezentowane są w Yii pod postacią tablicy. Właściwość
[primaryKey|CActiveRecord::primaryKey] zawiera wartość klucza głównego instancji AR.

Dostosowywanie (do własnych potrzeb)
-------------

[CActiveRecord] dostarcza kilku metod "wypełniaczy", które mogą być nadpisane 
w klasach potomnych aby dostosować ich sterowanie kolejnością zadań (ang. workflow).

   - [beforeValidate|CModel::beforeValidate] oraz
[afterValidate|CModel::afterValidate]: są wywoływane przed (ang. before) i po (ang. after) walidacji.

   - [beforeSave|CActiveRecord::beforeSave] oraz
[afterSave|CActiveRecord::afterSave]: są wywoływane przed i po zapisie instancji AR.

   - [beforeDelete|CActiveRecord::beforeDelete] oraz
[afterDelete|CActiveRecord::afterDelete]: są wywoływane przed i po usunięciu instancji AR.

   - [afterConstruct|CActiveRecord::afterConstruct]: jest wywoływana dla każdej instancji AR 
   utworzonej za pomocą operatora `new`.

   - [beforeFind|CActiveRecord::beforeFind]: jest wywoływana zanim instancja AR zostanie
   użyta do wykonania zapytania (np. `find()`, `findAll()`).

   - [afterFind|CActiveRecord::afterFind]: jest wywoływana dla każdej instancji AR utworzonej 
   jako rezultat zapytania.

Używanie transakcji w AR
-------------------------

Każda instancja AR zawiera właściwość nazwaną [dbConnection|CActiveRecord::dbConnection] 
która jest instancją [CDbConnection]. Możemy wtedy używać funkcjonalności 
[transakcji](/doc/guide/database.dao#using-transactions) dostarczanej przez Yii DAO 
jeśli występuje taka potrzeba podczas pracy z AR:

~~~
[php]
$model=Post::model();
$transaction=$model->dbConnection->beginTransaction();
try
{
	// szukanie i zapisywanie są dwoma krokami, które mogą być przychodzić za pomocą 
	// innych żądań, dlatego też używamy transakcji aby być pewnymi logiki oraz spójności danych
	$post=$model->findByPk(10);
	$post->title='nowy tytuł postu';
	$post->save();
	$transaction->commit();
}
catch(Exception $e)
{
	$transaction->rollBack();
}
~~~

Nazwane podzbiory (ang. Named Scopes)
------------

> Note|Uwaga: Pierwotny pomysł na nazwane podzbiory pochodzi z Ruby on Rails.

*Nazwany podzbiór* reprezentuje *nazwane* kryteria zapytania, które mogą być połączone z innymi nazwanymi podzbiorami  
i zastosowane do zapytania generowanego przez rekord aktywny. 

Nazwane podzbiory deklarowane są głównie w metodzie [CActiveRecord::scopes()] jako pary nazwa-kryterium.
Następujący kod definiuje dwa nazwane podzbiory, `opublikowane` oraz `najnowsze` w klasie modelu `tbl_ost`:

~~~
[php]
class Post extends CActiveRecord
{
	......
	public function scopes()
	{
		return array(
			'published'=>array(
				'condition'=>'status=1',
			),
			'recently'=>array(
				'order'=>'create_time DESC',
				'limit'=>5,
			),
		);
	}
}
~~~

Każdy nazwany podzbiór jest definiowany jako tablica, która może zostać użyta do inicjalizacji instancji klasy [CDbCriteria].
Na przykład, nazwany zbiór `najnowsze` określa właściwość `order` jako `createTime DESC` oraz nadaje właściwości `limit` wartość 5,
które przetłumaczone na kryteria zapytania, powinny zwrócić ostatnich 5 postów.

Nazwane podzbiory używane są najczęściej jako modyfikatory dla wywołań metody `find`. Kilka nazwanych podzbiorów może zostać ze sobą połączonych 
i w rezultacie tego zwrócić bardziej ograniczony zbiór wynikowy. Na przykład, aby znaleźć ostatnio opublikowane posty, możemy użyć następującego kodu:

~~~
[php]
$posts=Post::model()->obublikowane()->najnowsze()->findAll();
~~~

Ogólnie rzecz biorąc, nazwane podzbiory musza pojawić się po lewej stronie od wywołania metody `find`. Każde z nich dostarcza kryteria zapytania ,
które są łączone z pozostałymi kryteriami, włączając w to te, które zostały przekazane do metody `find`. Efekt końcowy jest podobny do tego,  
jakby do zapytania dodano listę filtrów.

> Note|Uwaga: Nazwane podzbiory mogą być tylko używane wyłącznie z poziomu metod klasowych.   
Oznacza to, że muszą one być wywołane w przy użyciu `NazwaKlasy::model()`.


### Parametryzowane nazwane podzbiory (ang. Parameterized Named Scopes)

Nazwane podzbiory mogą by sparametryzowane. Na przykład, chcemy dostosować liczbę postów określoną w nazwanym podzbiorze `najnowsze` 
Aby to zrobić, zamiast deklarować nazwany podzbiór w metodzie [CActiveRecord::scopes], musimy zdefiniować nową metodę, 
której nazwa jest taka sama jak jak nazwa podzbioru:

~~~
[php]
public function najnowsze($limit=5)
{
	$this->getDbCriteria()->mergeWith(array(
		'order'=>'create_time DESC',
		'limit'=>$limit,
	));
	return $this;
}
~~~

Następnie, możemy używać następującego wyrażenia, aby uzyskać 3 ostatnie opublikowane posty:

~~~
[php]
$posts=Post::model()->opublikowane()->najnowsze(3)->findAll();
~~~

Jeśli w powyższym kodzie nie przekazalibyśmy 3, otrzymalibyśmy domyślnie 5 ostatnio opublikowanych postów.

### Domyślne podzbiory

Klasa modelu może posiadać domyślne podzbiory, które będą stosowane dla wszystkich zapytań (włączająć w to zapytania relacyjne) dla modelu. Na przykład, strona wspierająca wielojęzyczność może chcież wyświetlać zawartość w aktualnie wybranym języku. Ponieważ, możemy mieć wiele zapytań związanych z zawartością strony, możemy zdecydować się na zdefiniowanie domyślnych podzbiorów, aby rozwiązać ten problem. Aby to zrobić, nadpisujemy  metodę [CActiveRecord::defaultScope] w następujący sposób:


~~~
[php]
class Content extends CActiveRecord
{
  public function defaultScope()
  {
    return array(
      'condition'=>"language='".Yii::app()->language."'",
    );
  }
}
~~~

Od teraz, nastepujące wywołanie metody będzie automatycznie używało powyżej zdefiniowanych 
kreteriów zapytania:

~~~
[php]
$contents=Content::model()->findAll();
~~~

> Note|Uwaga: Zauważ, że domyślne i nazwane podzbiory mają zastosowanie tylko dla zapytań `SELECT`. Są one ignorowane dla zapytań `INSERT`, `UPDATE` oraz `DELETE`.
> Ponadto, podczas deklarowania podzbiorów (domyślnych i nazwanych), klasa rekordu aktywnego nie może być używana do tworzenia zapytań do bazy w metodzie deklarującej podzbiór.

<div class="revision">$Id: database.ar.txt 3318 2011-06-24 21:40:34Z qiang.xue $</div>