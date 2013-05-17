Data Access Objects (DAO)
=========================

DAO (Obiecte accesare date) pune la dispozitie un API generic pentru accesul
datelor stocate in diverse DBMS (Sisteme de Management Baze de Date). 
Ca urmare, DBMS-ul poate fi schimbat oricand cu altul, fara sa fie nevoie
sa schimbam codul nostru in care folosim DAO sa accesam datele. 

Yii DAO este construit pe baza [PDO](http://php.net/manual/en/book.pdo.php) (obiecte date PHP)
care este o extensie PHP ce pune la dispozitie accesul unificat la date stocate
in diverse DBMS cunoscute, precum MySQL si PostgreSQL. De aceea, pentru a folosi
Yii DAO, trebuie instalate extensia PDO si driverul PDO de baze de date specific 
(ex. `PDO_MYSQL`).

Yii DAO este format in principal din urmatoarele patru clase:

   - [CDbConnection]: reprezinta conexiunea la o baza de date.
   - [CDbCommand]: reprezinta o instructiune SQL de executat.
   - [CDbDataReader]: reprezinta un flux doar de citire de randuri dintr-un set de rezultate.
   - [CDbTransaction]: reprezinta o tranzactie DB.

In cele ce urmeaza, explicam folosirea Yii DAO in diverse scenarii.

Stabilirea conexiunii la baza de date
-------------------------------------

Pentru a stabili o conexiune, cream o instanta [CDbConnection] si o activam.
Avem nevoie de un DSN (nume pentru sursa de date) pentru a specifica
informatiile necesare pentru conectarea la baza de date. Pot fi necesare si
un nume si o parola pentru stabilirea conexiunii. Va fi generata o exceptie
in cazul in care apare o eroare la stabilirea conexiunii (ex. DSN gresit sau
nume/parola gresite).

~~~
[php]
$connection=new CDbConnection($dsn,$username,$password);
// stabilirea conexiunii. Putem incerca try-catch pentru a identifica exceptii posibile
$connection->active=true;
......
$connection->active=false;  // inchidere conexiune
~~~

Formatul DSN-ului depinde de driverul PDO folosit. In general,
DSN este format din numele driver-ului PDO, urmat de semnul `:`, urmat de sintaxa
conexiunii specifice driver-ului. Pentru informatii complete, trebuie vazuta
[documentatia PDO](http://www.php.net/manual/en/pdo.construct.php). 
Mai jos este o lista de format-uri obisnuite pentru DSN:

   - SQLite: `sqlite:/path/to/dbfile`
   - MySQL: `mysql:host=localhost;dbname=testdb`
   - PostgreSQL: `pgsql:host=localhost;port=5432;dbname=testdb`

Pentru ca [CDbConnection] este derivata din clasa [CApplicationComponent], o putem
folosi de asemenea pe postul de [componenta aplicatie](/doc/guide/basics.application#application-component).
Pentru a face acest lucru, configuram componenta aplicatie
`db` (sau alta componenta aplicatie, daca se doreste)
din [configurarea aplicatiei](/doc/guide/basics.application#application-configuration) dupa cum urmeaza:

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
			'password'=>'password',
		),
	),
)
~~~

Putem dupa aceea sa accesam conexiunea DB prin `Yii::app()->db`, care este
activata automat (putem interzice acest comportament prin setarea cu false a
proprietatii [CDbConnection::autoConnect]. Folosind aceasta metoda
conexiunea DB poate fi utilizata oriunde in cod.

Executarea instructiunilor SQL
------------------------------

O data ce este stabilita o conexiune DB, instructiunile SQL pot fi executate
folosind [CDbCommand]. Cream o instanta [CDbCommand] prin apelarea
[CDbConnection::createCommand()] cu instructiunea SQL specificata:

~~~
[php]
$command=$connection->createCommand($sql);
// daca este necesar, instructiunea SQL poate fi actualizata asa:
// $command->text=$newSQL;
~~~

O instructiune SQL este executata prin [CDbCommand] in unul din urmatoarele doua moduri:

   - [execute()|CDbCommand::execute]: executa o instructiune SQL,
precum `INSERT`, `UPDATE` si `DELETE`. Daca are succes, returneaza
numarul de randuri afectate.

   - [query()|CDbCommand::query]: executa o instructiune SQL care returneaza
randuri de date, precum `SELECT`. Daca are succes, returneaza o instanta [CDbDataReader]
pe care putem sa o parcurgem pentru a folosi randurile de date rezultate.
Pentru usurinta, este implementat de asemenea un set de metode `queryXXX()`
care returneaza direct rezultatele cererii.

Va fi generata o exceptie daca apare vreo eroare in timpul executiei instructiunilor SQL.

~~~
[php]
$rowCount=$command->execute();   // executa SQL
$dataReader=$command->query();   // executa o cerere SQL
$rows=$command->queryAll();      // o cerere care returneaza toate randurile rezultate
$row=$command->queryRow();       // o cerere care returneaza primul rand dintre rezultate
$column=$command->queryColumn(); // o cerere care returneaza prima coloana din rezultate
$value=$command->queryScalar();  // o cerere care returneaza primul camp din primul rand
~~~

Extragerea rezultatelor cererii
-------------------------------

Dupa ce [CDbCommand::query()] genereaza instanta [CDbDataReader], putem extrage
randurile cu datele rezultate prin apelarea repetata a [CDbDataReader::read()].
Putem de asemenea folosi [CDbDataReader] intr-un `foreach` pentru a extrage fiecare rand in parte.

~~~
[php]
$dataReader=$command->query();
// apelam repetat read() pana cand returneaza false
while(($row=$dataReader->read())!==false) { ... }
// folosim foreach pentru a trece prin fiecare rand de date
foreach($dataReader as $row) { ... }
// extragem toate randurile o data intr-un singur array
$rows=$dataReader->readAll();
~~~

> Note|Nota: Spre deosebire de [query()|CDbCommand::query], toate metodele `queryXXX()`
returneaza date direct. De exemplu, [queryRow()|CDbCommand::queryRow]
returneaza un array care reprezinta primul rand din rezultatele cererii.

Folosirea tranzactiilor
-----------------------

Cand o aplicatie executa cateva cereri, operatii de citire sau/si scriere
in baza de date, este important sa ne asiguram ca baza de date sa contina toate schimbarile
facute de aceste operatii. In aceste cazuri, poate fi initiata o tranzactie,
reprezentata prin instanta [CDbTransaction] din Yii:

   - Incepem tranzactia.
   - Executam cererile una cate una. Orice actualizare nu este vizibila in exteriorul bazei de date.
   - Executam tranzactia. Actualizarile devin vizibile daca tranzactia a avut succes.
   - Daca o cerere esueaza, intreaga tranzactie este derulata inapoi.

Fluxul de lucru de mai sus poate fi implementat folosind urmatorul cod:

~~~
[php]
$transaction=$connection->beginTransaction();
try
{
	$connection->createCommand($sql1)->execute();
	$connection->createCommand($sql2)->execute();
	//.... alte executii SQL
	$transaction->commit();
}
catch(Exception $e) // daca o cerere esueaza, este generata o exceptie
{
	$transaction->rollBack();
}
~~~

Conectarea de parametri (Binding)
---------------------------------

Pentru a evita [atacurile SQL injection] (http://en.wikipedia.org/wiki/SQL_injection) si pentru a
imbunatati performanta executiilor cererilor SQL folosite des, putem "prepara"
o instructiune SQL cu placeholder-e de parametri optionali care vor fi inlocuiti
cu parametrii reali in timpul procesului de conectare de parametri.

Placeholder-ele de parametri pot avea nume (token-uri unice)
sau pot fi anonime (prin semne de intrebare). Apelam
[CDbCommand::bindParam()] sau [CDbCommand::bindValue()] pentru a inlocui aceste
placeholder-e cu parametrii reali. Conectarea parametrilor trebuie facuta inainte
ca instructiunea SQL sa fie executata.

~~~
[php]
// o cerere SQL cu 2 placeholdere ":username" si ":email"
$sql="INSERT INTO users(username, email) VALUES(:username,:email)";
$command=$connection->createCommand($sql);

// inlocuim placeholder-ul ":username" cu valoarea reala username
$command->bindParam(":username",$username,PDO::PARAM_STR);

// inlocuim placeholder-ul ":email" cu valoarea reala email
$command->bindParam(":email",$email,PDO::PARAM_STR);
$command->execute();

// inseram un alt rand cu un nou set de parametri
$command->bindParam(":username",$username2,PDO::PARAM_STR);
$command->bindParam(":email",$email2,PDO::PARAM_STR);
$command->execute();
~~~

Metodele [bindParam()|CDbCommand::bindParam] si [bindValue()|CDbCommand::bindValue] sunt foarte
asemanatoare. Singura diferenta este ca [bindParam()|CDbCommand::bindParam] conecteaza un parametru
cu o referinta a unei variabile PHP, in timp ce [bindValue()|CDbCommand::bindValue] conecteaza
un parametru cu o valoare. Pentru parametri care reprezinta blocuri mai de date memorate, este de preferat
sa folosim [bindParam()|CDbCommand::bindParam] din considerente de perfomanta.

Pentru mai multe detalii despre conectarea de parametrii, trebuie vazuta 
[sectiunea din documentatia PHP](http://www.php.net/manual/en/pdostatement.bindparam.php).

Conectarea coloanelor (Binding)
-------------------------------

Cand extragem rezultatele cererii, putem sa conectam si coloane la variabile PHP pentru a fi
populate automat cu ultimele date, de fiecare data cand se extrage un rand nou.

~~~
[php]
$sql="SELECT username, email FROM users";
$dataReader=$connection->createCommand($sql)->query();
// conectam prima coloana (username) cu variabila $username
$dataReader->bindColumn(1,$username);
// conectam a doua coloana (email) cu variabila $email
$dataReader->bindColumn(2,$email);
while($dataReader->read()!==false)
{
    // $username si $email contin username si email din randul curent
}
~~~

<div class="revision">$Id: database.dao.txt 367 2008-12-16 20:18:30Z qiang.xue $</div>