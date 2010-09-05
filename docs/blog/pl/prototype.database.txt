Ustawienia bazy danych
===================

Posiadając utworzony szkielet aplikacji oraz zakończony projekt bazy danych, zajmiemy się w tej części utworzeniem bazy danych dla bloga oraz ustanowieniem połączenie do niej we wspomnianym szkielecie aplikacji.


Tworzenie bazy danych
-----------------

Zdecydowaliśmy się utworzyć bazę danych SQLite. Ponieważ wsparcie dla baz danych opiera się na [PDO](http://www.php.net/manual/en/book.pdo.php), możemy bardzo łatwo przełączać się pomiędzy używaniem różnych typów baz danych (np. MySQL, PostgreSQL) bez potrzeby dokonywania zmian w kodzie aplikacji.

Utworzyliśmy plik bazy danych `blog.db` w katalogu `/wwwroot/blog/protected/data`. Zauważ, że wymagania SQLite, powodują że zarówno katalog jak i plik bazy danych powinny być zapisywalne przez proces serwera. Można po prostu skopiować plik bazy danych z dema blogu znajdującego się w instalacji Yii w `/wwwroot/yii/demos/blog/protected/data/blog.db`. Możemy również wygenerować bazę danych poprzez wykonanie zapytań SQL zawartych w pliku `/wwwroot/yii/demos/blog/protected/data/schema.sqlite.sql`.

> Tip|Wskazówka: aby móc wykonywać wyrażenia SQL, możemy używać narzędzia linii poleceń `sqlite3`, które można znaleźć na [oficjalnej stronie SQLite](http://www.sqlite.org/download.html).


Ustanawianie połączenia z bazą danych
--------------------------------

Aby używać bazę danych blogu w utworzonym szkielecie aplikacji, musimy zmodyfikować jego [konfigurację aplikacji](http://www.yiiframework.com/doc/guide/basics.application#application-configuration), która przechowywana jest w skrypcie PHP `/wwwroot/blog/protected/config/main.php`. Skrypt zwraca asocjacyjną tablicę zawierającą pary nazwa-wartość, z których każda używana jest do zainicjalizowania zapisywalnej właściwości [instancji aplikacji](http://www.yiiframework.com/doc/guide/basics.application).

Skonfigurujemy komponent `db` w następujący sposób,

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:/wwwroot/blog/protected/data/blog.db',
			'tablePrefix'=>'tbl_',
		),
	),
	......
);
~~~

Powyższa konfiguracja mówi nam, że posiadamy [komponent aplikacji](http://www.yiiframework.com/doc/guide/basics.application#application-component) `db`, którego właściwość `connectionString` powinna zostać zainicjalizowana wartością `sqlite:/wwwroot/blog/protected/data/blog.db` oraz którego właściwość `tablePrefix` powinna mieć wartość `tbl_`.

Przy użyciu tej konfiguracji, możemy uzyskać dostęp do obiektu połączenia z bazą danych przy użyciu wyrażenia `Yii::app()->db` w dowolnym miejscu naszego kodu. Zauważ, że `Yii::app()` zwraca instancję aplikacji, którą utworzyliśmy w skrypcie wejściowym. Jeśli jesteś zainteresowany poznaniem metod posiadanych przez połączenie DB oraz jego właściwościami, możesz udać się do [dokumentacji klas|CDbConnection]. Jednakże w większości przypadków nie będziemy używali tego połączenia z bazą danych wprost. W zamian, będziemy używać tak zwanego [rekordu aktywnego](http://www.yiiframework.com/doc/guide/database.ar) w celu uzyskania dostępu do bazy danych. 

Będziemy chcieli wyjaśnić po trosze właściwość `tablePrefix`, którą ustawiliśmy w konfiguracji. Mówi ona połączeniu `db`, iż powinno ono uwzględniać fakt używania przez nas prefiksu `tbl_` jako prefiksu do nazw naszych tabel bazodanowych. W szczególności, jeśli w zapytaniu SQL znajduje się token, zamknięty w podwójnych nawiasach klamrowych (np. `{{post}}`), wtedy połączenie `db` powinno przetłumaczyć je na nazwę tabeli zawierającą ten prefiks (np. `tbl_post`) zanim prześle go do wywołania do DBMS. Funkcjonalność ta jest szczególnie użyteczna, jeśli w przyszłości będziemy mieli potrzebę zmiany prefiksu bazy danych, bez konieczności dotykania kodu źródłowego. Na przykład, jeśli tworzymy ogólny system zarządzania treścią (CMS), możemy wykorzystać tę funkcję, tak, że jeśli będziemy instalować go w nowym środowisku, damy użytkownikom możliwość wyboru prefiksu tabeli jakiego chcą.

> Tip|Wskazówka: Jeśli chcesz używać MYSQL zamiast SQLite do przechowywania danych, 
> możesz utworzyć bazę danych nazwaną `blog` używając wyrażenia SQL  
> z pliku `/wwwroot/yii/demos/blog/protected/data/schema.mysql.sql`. 
> Następnie, zmodyfikuj konfigurację w następujący sposób
>
> ~~~
> [php]
> return array(
>     ......
>     'components'=>array(
>         ......
>         'db'=>array(
>             'connectionString' => 'mysql:host=localhost;dbname=blog',
>             'emulatePrepare' => true,
>             'username' => 'root',
>             'password' => '',
>             'charset' => 'utf8',
>             'tablePrefix' => 'tbl_',
>         ),
>     ),
> 	......
> );
> ~~~


<div class="revision">$Id: prototype.database.txt 2332 2010-08-24 20:55:36Z mdomba $</div>