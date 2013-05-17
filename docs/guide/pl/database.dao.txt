Data Access Objects (DAO)
=========================

Data Access Objects (DAO) dostarcza generycznego API umożliwiającego dostęp do danych 
przechowywanych w różnych systemach zarządzania bazą danych (ang. DBMS, pol. SZBD). W rezultacie, 
użyty SZBD może zostać zastąpiony przez inny bez potrzeby zmiany kodu używającego
DAO aby uzyskać dostęp do danych.

Yii DAO zostało utworzone w oparciu o [PHP Data Objects (PDO)](http://php.net/manual/en/book.pdo.php) 
które jest rozszerzeniem dostarczającym ujednolicony dostęp do danych dla wielu 
popularnych SZBD, takich jak MySQL, PostgreSQL. Dlatego też, by używać Yii DAO, 
rozszerzenie PDO oraz poszczególne sterowniki PDO dla baz danych (np. `PDO_MYSQL`) 
muszą być zainstalowane.

Yii DAO składa się głównie z następujących czterech klas:

   - [CDbConnection]: reprezentuje połączenie z bazą danych,
   - [CDbCommand]: reprezentuje instrukcję SQL, wykonywaną dla bazy danych,
   - [CDbDataReader]: reprezentuje przeglądany jedynie w przód strumień wierszy pochodzących z zestawu wyników zapytania,
   - [CDbTransaction]: reprezentuje transakcję DB.   


W dalszej części, przedstawimy użycie Yii DAO w różnych scenariuszach.

Ustanawianie połączenia z bazą danych
-------------------------------------

Aby ustanowić połączenie z bazą danych należy utworzyć instancję [CDbConnection] 
a następnie aktywować ją. Aby połączyć się z bazą danych potrzebny jest adres DNS. 
Użytkownik oraz hasło mogą być również potrzebne aby ustanowić połączenie. W przypadku 
gdy podczas łączenia nastąpi błąd (np. podano zły adres DNS lub złe hasło/nazwę użytkownika) 
zostanie rzucony odpowiedni wyjątek.

~~~
[php]
$connection=new CDbConnection($dsn,$username,$password);
// ustanawianie połączenia. Możesz użyć try...catch aby złapać potencjalne wyjątki
$connection->active=true;
......
$connection->active=false;  // zamknij połączenie
~~~

Format adresu DNS zależy od używanego, dla danej bazy danych, sterownika PDO. Uogólniając, 
DNS składa się z nazwy sterownika PDO, po którym następuje dwukropek a następnie 
zależna od sterownika składnia połączenia. Zobacz [dokumentację PDO](http://www.php.net/manual/en/pdo.construct.php) 
aby uzyskać więcej informacji. Poniżej znajduje się lista najczęściej używanych formatów DNS:

   - SQLite: `sqlite:/scieżka/do/pliku/bazy`,
   - MySQL: `mysql:host=localhost;dbname=testdb`,
   - PostgreSQL: `pgsql:host=localhost;port=5432;dbname=testdb`,
   - SQL Server: `mssql:host=localhost;dbname=testdb`,
   - Oracle: `oci:dbname=//localhost:1521/testdb`

Ponieważ klasa [CDbConnection] dziedziczy z klasy [CApplicationComponent], możemy użyć jej jako 
[komponent aplikacji](/doc/guide/basics.application#application-component). 
Aby to zrobić, należy skonfigurować komponent aplikacji 'db' (można użyć innej nazwy) 
w [konfiguracji aplikacji](/doc/guide/basics.application#application-configuration) 
w następujący sposób:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'db'=>array(
			'class'=>'CDbConnection',
			'connectionString'=>'mysql:host=localhost;dbname=testdb',
			'username'=>'root',
			'password'=>'hasło',
			'emulatePrepare'=>true,  // wymagane przez pewne instalacje MySQL			
		),
	),
)
~~~

Dostęp do połączenia DB, można uzyskać poprzez `Yii::app()->db`. Jest ono automatycznie 
aktywowane, chyba że wyraźnie skonfigurujemy [CDbConnection::autoConnect] jako false. 
Używając tego podejścia, jedno połączenie DB może być dzielone w wielu miejscach w naszym kodzie.

Wykonywanie instrukcji SQL
--------------------------

Gdy połączenie z bazą danych zostało ustanowione, można wykonywać instrukcje SQL za 
pomocą [CDbCommand]. Utworzenie instancji [CDbCommand] odbywa się poprzez wywołanie 
metody [CDbConnection::createCommand()] z określoną instrukcją SQL:

~~~
[php]
$connection=Yii::app()->db;   // zakładamy, że masz skonfigurowane połączenie "db"
// Jeśli nie, możesz bezpośrednio utworzyć połączenie z bazą danych:
// $connection=new CDbConnection($dsn,$username,$password);
$command=$connection->createCommand($sql);
// jeśli jest to wymagane, instrukcja SQL może być zmieniona następująco:
// $command->text=$newSQL;
~~~


Instrukcja SQL może zostać wykonana za pomocą [CDbCommand] w jeden z dwóch poniższych sposobów:

   - [execute()|CDbCommand::execute]: wykonuje instrukcję SQL nie będącą zapytaniem 
   taką jak `INSERT`, `UPDATE` oraz `DELETE`. Jeśli wywołanie zakończy się sukcesem, metoda zwróci 
   liczbę wierszy, na które wpłynęło wykonywanie instrukcji.

   - [query()|CDbCommand::query]: wykonuje instrukcję SQL, taką jak `SELECT`, która zwraca wiersze z danymi. 
   Jeśli wywołanie zakończy się sukcesem, metoda zwróci instancję [CDbDataReader], za pomocą której 
   można przejrzeć wynikowe wiersze danych. Dla wygody został zaimplementowany zestaw metod `queryXXX()`, 
   które to zwracają bezpośrednio wyniki zapytań.

Jeżeli podczas wykonywania indtrukcji SQL wystąpił błąd zostanie rzucony wyjątek.

~~~
[php]
$rowCount=$command->execute();   // wykonaj instrukcję SQL nie będące zapytaniem
$dataReader=$command->query();   // wykonaj zapytanie SQL
$rows=$command->queryAll();      // zapytaj i zwróć wszystkie wynikowe wiersze 
$row=$command->queryRow();       // zapytaj i zwróć pierwszy wiersz spośród wyników
$column=$command->queryColumn(); // zapytaj i zwróć pierwszą kolumnę spośród wyników
$value=$command->queryScalar();  // zapytaj i zwróć pierwsze pole w pierwszym wierszu
~~~

Pobieranie wyników zapytań
--------------------------

Po wygenerowaniu przez metodę [CDbCommand::query()] instancji klasy [CDbDataReader], można 
zwrócić wiersze danych wynikowych poprzez powtarzanie wywoływania metody [CDbDataReader::read()]. 
Instancję [CDbDataReader] można używać w konstrukcji `foreach` języka PHP co powoduje 
dostęp do danych wiersz po wierszu.

~~~
[php]
$dataReader=$command->query();
// powtarzaj wywołanie read() dopóki nie zwróci ono wartości false
while(($row=$dataReader->read())!==false) { ... }
// używanie foreach do przeglądania każdego wiersza danych
foreach($dataReader as $row) { ... }
// zwrócenie wszystkich wierszy za jednym razem za pomocą jednej tablicy
$rows=$dataReader->readAll();
~~~

> Note|Uwaga: W odróżnieniu od metody [query()|CDbCommand::query], wszystkie metody typu `queryXXX()` 
zwracają dane bezpośrednio. Na przykład, [queryRow()|CDbCommand::queryRow]
zwraca tablicę reprezentującą pierwszy wiersz wyniku zapytań.

Używanie transakcji
-------------------

Kiedy aplikacja wykonuje kilka zapytań, za każdym razem czytając i/lub zapisując informacje w bazie danych, 
ważnym jest by upewnić się, że na bazie danych nie została wykonana tylko część z tych zapytań.
W takim przypadku może zostać zainicjowana transakcja reprezentowana w Yii poprzez instancję [CDbTransaction]:

   - Rozpocznij transakcję.
   - Wykonaj zapytania jedno po drugim. Żadna zmiana w bazie danych nie jest widoczna na zewnątrz.
   - Potwierdź (commit) transakcję. Zmiany będą widoczne jeśli transakcja się powiedzie.
   - Jeśli jedno z zapytań nie powiedzie się, cała transakcja zostanie anulowana (roll-back).

Powyższy logika może zostać zaimplementowana używając następującego kodu:

~~~
[php]
$transaction=$connection->beginTransaction();
try
{
	$connection->createCommand($sql1)->execute();
	$connection->createCommand($sql2)->execute();
	//.... pozostałe wywołania SQLi
	$transaction->commit();
}
catch(Exception $e) // jeśli zapytanie nie powiedzie się, wołany jest wyjątek
{
	$transaction->rollBack();
}
~~~

Przypinanie parametrów
----------------------

Aby uniknąć [ataków SQL injection](http://en.wikipedia.org/wiki/SQL_injection)
oraz aby zwiększyć wydajność wykonywania często używanych instrukcji SQL, można "przygotować" 
instrukcję SQL z opcjonalnymi symbolami zastępczymi (ang. placeholders) parametrów, które to będą zastąpione
przez aktualne parametry podczas procesu przypinania parametrów.

Symbole zastępcze parametrów mogą być zarówno nazwane (reprezentowane jako unikalne tokeny) 
lub mogą nie posiadać nazwy (reprezentowane za pomocą znaku zapytania). Aby zastąpić te 
symbole aktualnymi parametrami wywołaj metodę [CDbCommand::bindParam()] lub [CDbCommand::bindValue()].
Parametry te nie muszą być objęte cudzysłowem, użyty sterownik bazy danych zrobi to za Ciebie. 
Przypinanie parametrów musi nastąpić zanim instrukcja SQL zostanie wykonana.

~~~
[php]
// SQL z dwoma symbolami zastępczymi ":username" oraz ":email"
$sql="INSERT INTO tbl_user (username, email) VALUES(:username,:email)";
$command=$connection->createCommand($sql);
// zastąp symbol ":username" aktualną wartością parametru username
$command->bindParam(":username",$username,PDO::PARAM_STR);
// zastąp symbol ":email" aktualną wartością parametru email
$command->bindParam(":email",$email,PDO::PARAM_STR);
$command->execute();
// wstaw inny wiersz z nowym zestawem parametrów 
$command->bindParam(":username",$username2,PDO::PARAM_STR);
$command->bindParam(":email",$email2,PDO::PARAM_STR);
$command->execute();
~~~

Metody [bindParam()|CDbCommand::bindParam] oraz [bindValue()|CDbCommand::bindValue] 
są bardzo podobne. Jedyną różnicą jest to, że pierwsza przypina do parametru referencję 
zmiennej gdy druga wartość zmiennej. Dla parametrów które reprezentują duże bloki pamięci danych,
pierwsza z nich jest korzystna ze względu na wydajność.

Aby uzyskać więcej informacji na temat przypinania parametrów zobacz 
[odpowiednią dokumentację PHP](http://www.php.net/manual/en/pdostatement.bindparam.php).

Przypinanie kolumn
------------------

Podczas pobierania wyników zapytania, można również przypiąć do kolumny zmienne PHP 
tak, by były one automatycznie wypełniane najnowszymi danymi za każdym razem kiedy pobieramy wiersz.

~~~
[php]
$sql="SELECT username, email FROM tbl_user";
$dataReader=$connection->createCommand($sql)->query();
// przypnij pierwszą kolumnę (username) do zmiennej $username
$dataReader->bindColumn(1,$username);
// przypnij 2 kolumnę (email) do zmiennej $email
$dataReader->bindColumn(2,$email);
while($dataReader->read()!==false)
{
    // zmienne $username oraz $email zawierają nazwę użytkownika oraz email dla aktualnego wiersza
}
~~~

Używanie prefiksów w tabelach
-----------------------------

Yii dostarcza zintegrowanego wsparcia dla prefiksów tabel.
Prefiks tabeli to łańcuch znaków, który poprzedza nazwę tabel w bazie danych, 
z którą jesteśmy połączeni. Prefiksów używa się najczęsciej we współdzielonych środowiku hostingowym, 
gdzie wiele aplikacji dzieli jedną tabelę bazodanową i używa różnych prefiksów tabeli 
w celu rozróżnienia ich od siebie. Na przykład, jedna aplikacja może używać prefiksu
`tbl_`, druga zaś innego, np. `yii_`.

Aby móc używać prefiksów tabel, należy przypisać właściwości [CDbConnection::tablePrefix] 
pożądany prefiks tabeli. Następnie, w zapytaniach SQL należy używać konstrukcji `{{TableName}}`,
gdzie `TableName` wskazuje na nazwę tabeli bez prefiksu. Na przykład, jeśli baza danych zawiera 
tabelę o nazwie `tbl_user`, gdzie `tbl_` zostało skonfigurowane jako prefiks tabeli, 
wtedy możemy używać następującego kodu, aby zapytać o użytkowników:

~~~
[php]
$sql='SELECT * FROM {{user}}';
$users=$connection->createCommand($sql)->queryAll();
~~~

<div class="revision">$Id: database.dao.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>