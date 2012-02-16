Active Record
=============

Desi Yii se poate descurca virtual cu orice task in ce priveste bazele de date,
este foarte posibil ca in 90% din timpul nostru in care scriem instructiuni SQL
sa scriem instructiuni SQL pentru operatii CRUD obisnuite (create, read, update si delete).
In plus, este dificil de intretinut codul in viitor. Pentru a rezolva aceste
probleme, putem folosi Active Record.

Active Record (AR) este o tehnica foarte populara ORM (Object-Relational Mapping).
Fiecare clasa AR reprezinta o tabela din baza de date ale carei atribute sunt reprezentate
ca proprietati ale clasei AR, iar o instanta a clasei AR reprezinta un rand din acea tabela
din baza de date. Operatiile CRUD obisnuite sunt implementate ca metode in clasa AR.
Rezultatul este ca putem accesa tabela din baza de date exact la fel cum accesam un obiect
al unei clase oarecare. De exemplu, putem sa folosim urmatorul cod pentru a insera
un nou rand in tabela `Post`:

~~~
[php]
$post=new Post;
$post->title='Titlu post';
$post->content='Continutul post-ului';
$post->save();
~~~

In cele ce urmeaza descriem cum se configureaza Active Record si cum
folosim Active Record in operatiile CRUD obisnuite. Vom arata si cum putem folosi
Active Record pentru a ne descurca cu relatiile dintre tabele, dar in sectiunea urmatoare.
Pentru simplitate, vom folosi urmatoarea tabela dintr-o baza de date pentru toate exemplele
din aceasta sectiune.

~~~
[sql]
CREATE TABLE Post (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	title VARCHAR(128) NOT NULL,
	content TEXT NOT NULL,
	createTime INTEGER NOT NULL
);
~~~

> Note|Nota: AR nu are scopul de a rezolva toate task-urile in legatura cu bazele de date.
AR este cel mai bine folosit in cazul operatiunilor SQL obisnuite. Pentru scenarii complexe,
ar trebui folosit Yii DAO.

Stabilirea unei conexiuni DB
----------------------------

AR se bazeaza pe o conexiune DB pentru a executa operatiile SQL. Implicit,
componenta `db` asigura instanta clasei [CDbConnection] care
este folosita pentru conexiunea DB, cel putin asa se presupune. Urmatoarea configuratie
de aplicatie arata un exemplu:

~~~
[php]
return array(
	'components'=>array(
		'db'=>array(
			'class'=>'system.db.CDbConnection',
			'connectionString'=>'sqlite:path/to/dbfile',
			// stergem comentariul de mai jos pentru a activa schema de caching pentru performanta
			// 'schemaCachingDuration'=>3600,
		),
	),
);
~~~

> Tip|Sfat: Pentru ca AR se bazeaza pe metadatele despre tabele pentru a determina
informatiile despre coloane, citirea acestor metadate si analiza lor vor lua mereu timp.
Daca schema bazei de date nu va fi schimbata prea curand, ar trebui sa activam caching-ul de scheme
prin configurarea proprietatii [CDbConnection::schemaCachingDuration] cu o valoare mai mare decat 0.

Suportul pentru AR este limitat de catre DBMS. In acest moment, au suport doar urmatoarele DBMS:

   - [MySQL 4.1 sau mai nou](http://www.mysql.com)
   - [PostgreSQL 7.3 sau mai nou](http://www.postgres.com)
   - [SQLite 2 sau 3](http://www.sqlite.org)

Daca vrem sa folosim o alta componenta decat `db`, sau daca vrem sa lucram
cu mai multe baze de date folosind AR, atunci ar trebui sa suprascriem
[CActiveRecord::getDbConnection()]. Clasa [CActiveRecord] este clasa de baza pentru
toate clasele AR.

> Tip|Sfat: Exista doua posibilitati in lucrul cu mai multe baze de date in AR.
Daca schemele bazelor de date sunt diferite, atunci putem crea clase de baza AR diferite
cu implementari diferite ale [getDbConnection()|CActiveRecord::getDbConnection]. Daca schemele
bazelor de date sunt la fel, atunci schimbarea dinamica a variabilei
[CActiveRecord::db] este o idee mult mai buna.

Definirea clasei AR
-------------------

Pentru a accesa o tabela din baza de date, mai intai trebuie sa definim o clasa AR
prin derivarea clasei [CActiveRecord]. Fiecare clasa AR reprezinta o singura tabela, iar
o instanta a clasei AR reprezinta un rand din acea tabela. Urmatorul exemplu este codul
minim necesar pentru o clasa AR care reprezimta tabela `Post`:

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

> Tip|Nota: Clasele AR sunt de obicei folosite in mai multe locuri si de aceea
> putem importa intregul director care contine clasele AR, in loc sa le includem pe fiecare
> una cate una. De exemplu, daca toate fisierele cu clasele AR sunt in directorul
> `protected/models`, putem configura aplicatia in felul urmator:
> ~~~
> [php]
> return array(
> 	'import'=>array(
> 		'application.models.*',
> 	),
> );
> ~~~

Implicit, numele clasei AR este acelasi cu numele tabelei din baza de date.
Daca se doreste altfel, trebuie suprascria metoda [tableName()|CActiveRecord::tableName].
Metoda [model()|CActiveRecord::model] este declarata astfel pentru fiecare clasa AR
(vom explica imediat).

Valorile coloanelor unui rand dintr-o tabela pot fi accesate ca proprietati ale instantei
clasei AR corespunzatoare. De exemplu, urmatorul cod seteaza coloana (atributul) `title`:

~~~
[php]
$post=new Post;
$post->title='Titlul post-ului';
~~~

Desi nu declaram niciodata in mod explicit proprietatea `title` din clasa `Post`,
putem accesa aceasta proprietate pentru ca `title` este o coloana din tabela
`Post`, iar CActiveRecord ne face accesibila aceasta coloana ca proprietate
cu ajutorul metodei PHP `__get()`. O exceptie va fi generata daca incercam sa accesam
o coloana inexistenta din tabela.

> Info: Pentru o vizibilitate mai mare, este cel mai eficient sa urmam regula camel case
cand denumim tabelele (si coloanele lor) din baza de date. In particular, numele de tabele
sunt formate prin capitalizarea (prima litera este mare) fiecarui cuvant din numele tabelei, si alaturarea fiecarui
cuvant fara sa punem spatiu; numele coloanelor sunt asemanatoare numelor tabelelor, cu singura
diferenta ca prima litera trebuie sa fie litera mica. De exemplu, folosim `Post` pentru a
denumi tabela care memoreaza post-urile; vom folosi `createTime` pentru a denumi
coloana tabelei care este cheie primara. Denumind astfel tabelele si coloanele, facem tabelele
sa arate exact ca tipurile de clase si coloanele sa arate exact ca variabilele.
De notat ca folosirea camel case poate crea unele inconveniente cu unele DBMS-uri,
ca MySQL, pentru ca s-ar putea comporta diferit in sisteme de operare diferite.



Creating Record
---------------

Ca sa inseram un nou rand intr-o tabela a bazei de date, cream o instanta a
clasei AR corespunzatoare, ii setam proprietatile (care sunt asociate cu coloanele
tabelei) si apelam metoda [save()|CActiveRecord::save] pentru a termina inserarea.

~~~
[php]
$post=new Post;
$post->title='Titlul post-ului';
$post->content='Continutul post-ului';
$post->createTime=time();
$post->save();
~~~

Daca cheia primara a tabelei se incrementeaza automat, dupa ce se termina inserarea,
instanta AR va contine o cheie primara actualizata. In exemplul de mai sus,
proprietatea `id` va reflecta valoarea cheii primare a post-ului nou inserat, chiar daca
nu il modificam explicit.

Daca o coloana este definita cu o valoare implicita oarecare (ex. un string, un numar)
in schema tabelei, proprietatea corespunzatoare in instanta AR va avea automat
atribuita aceasta valoare atunci cand instanta AR este creata. O cale de a modifica
aceasta valoare este prin a declara explicit proprietatea in clasa AR:

~~~
[php]
class Post extends CActiveRecord
{
	public $title='Va rugam introduceti un titlu';
	......
}

$post=new Post;
echo $post->title;  // Aceasta instructiune va afisa: Va rugam introduceti un titlu
~~~

Incepand cu versiunea 1.0.2 a Yii, unui atribut i se poate asigna o valoare de tip
[CDbExpression] inainte ca inregistrarea sa fie salvata (fie insert fie update) in baza de date.
De exemplu, pentru a salva timestamp-ul returnat de functia MySQL `NOW()`, putem folosi
urmatorul cod:

~~~
[php]
$post=new Post;
$post->createTime=new CDbExpression('NOW()');
// $post->createTime='NOW()'; nu va functionat pentru ca
// 'NOW()' va fi tratat ca un string
$post->save();
~~~


Citirea inregistrarilor
-----------------------

Pentru a citi date dintr-o tabela, putem folosi una dintre metodele `find` dupa cum urmeaza.

~~~
[php]
// find pentru a cauta primul rand care indeplineste conditia specificata
$post=Post::model()->find($condition,$params);
// find pentru a cauta randul cu cheia primara specificata
$post=Post::model()->findByPk($postID,$condition,$params);
// find pentru a cauta randul cu valorile atributelor specificate
$post=Post::model()->findByAttributes($attributes,$condition,$params);
// find pentru a cauta primul rand folosind instructiunea SQL specificata
$post=Post::model()->findBySql($sql,$params);
~~~

In cele de mai sus, apelam metoda `find` cu `Post::model()`. Sa ne aducem aminte
ca metoda statica `model()` este necesara pentru fiecare clasa AR. Metoda returneaza
o instanta AR care este folosita pentru a accesa metodele clasei (asemanator cu metodele
unei clase statice) in contextul unui obiect. 

Daca metoda `find` gaseste un rand care indeplineste conditiile cererii, atunci
va intoarce o instanta `Post` ale carei proprietati contin valorile coloanelor
corespunzatoare din randul din tabela. Putem acum citi valorile incarcate exact la fel
cum putem citi valorile proprietatilor unui obiect, de exemplu, `echo $post->title;`.

Metoda `find` va intoarce null daca nu este gasit nici un rand din baza de date
care indeplineste conditiile. 

Cand apelam `find`, folosim `$condition` si `$params` pentru a transmite conditiile. 
`$condition` poate fi un string in care se retine clauza `WHERE` dintr-o instructiune
SQL. `$params` este un array de parametri ale caror valori ar trebui sa fie conectate corespunzator
la placeholder-ele din `$condition`. De exemplu,

~~~
[php]
// find pentru a gasi randul cu postID=10
$post=Post::model()->find('postID=:postID', array(':postID'=>10));
~~~

Putem de asemenea folosi `$condition` pentru a specifica alte conditii mult mai complexe.
In loc de un string, `$condition` poate fi o instanta a clasei [CDbCriteria], care ne permite
sa specificam alte conditii pe langa clauza `WHERE`. De exemplu:

~~~
[php]
$criteria=new CDbCriteria;
$criteria->select='title';  // selecteaza doar coloana 'title'
$criteria->condition='postID=:postID';
$criteria->params=array(':postID'=>10);
$post=Post::model()->find($criteria); // $params nu este necesar
~~~

Trebuie sa retinem ca atunci cand folosim [CDbCriteria] pentru conditii, parametrul
`$params` nu mai este necesar din moment ce poate fi specificat in [CDbCriteria], asa
cum am aratat mai sus.

O alta modalitate in ce priveste [CDbCriteria] este sa transmitem un array catre
metoda `find`. Fiecare pereche key-value din array va corespune unei perechi nume proprietate-valoare.
Putem sa rescriem exemplul de mai sus astfel:

~~~
[php]
$post=Post::model()->find(array(
	'select'=>'title',
	'condition'=>'postID=:postID',
	'params'=>array(':postID'=>10),
));
~~~

> Info: Atunci cand cream o conditie pentru cautarea unor coloane cu anumite valori,
putem folosi [findByAttributes()|CActiveRecord::findByAttributes].
Parametrii `$attributes` vor fi transmisi intr-un array de valori indexate dupa numele de coloana.
In unele framework-uri, acest task poate fi facut prin apelarea unor metode de genul
`findByNameAndTitle`. Desi pare atractiva aceasta abordare, de obicei cauzeaza confuzie, conflicte
si probleme (ex. daca numele coloanelor sunt case-sensitive sau nu). 

Atunci cand se potrivesc mai multe coloane cu conditia noastra, putem sa le extragem
pe toate in acelasi timp folosind metodele `findAll`, fiecare metoda `find` avand un corespondent `findall`.

~~~
[php]
// find all pentru a cauta randurile care indeplinesc conditia
$posts=Post::model()->findAll($condition,$params);
// find all pentru a cauta randurile care au cheile primare specificate
$posts=Post::model()->findAllByPk($postIDs,$condition,$params);
// find all pentru a cauta randurile care au valorile specificate
$posts=Post::model()->findAllByAttributes($attributes,$condition,$params);
// find all pentru a cauta randurile care rows using the specified SQL statement
$posts=Post::model()->findAllBySql($sql,$params);
~~~

Daca nu gaseste nimic, `findAll` va intoarce un array gol. Spre deosebire de
`find` care ar intoarce null daca nu gaseste nimic.

Pe langa metodele `find` si `findAll` descrise mai sus, urmatoarele metode sunt de
asemenea disponibile:

~~~
[php]
// afla cate randuri indeplinesc conditia specificata
$n=Post::model()->count($condition,$params);
// afla numarul de randuri folosind instructiunea SQL specificata
$n=Post::model()->countBySql($sql,$params);
// verifica daca este cel putin un rand care indeplineste conditia specificata
$exists=Post::model()->exists($condition,$params);
~~~

Actualizarea inregistrarilor
----------------------------

Dupa ce o instanta AR este populata cu valorile coloanelor, putem modifica valorile si
apoi putem salva noul stadiu in baza de date.

~~~
[php]
$post=Post::model()->findByPk(10);
$post->title='Titlul nou';
$post->save(); // salveaza modificarile in baza de date
~~~

Dupa cum se vede, folosim aceeasi metoda [save()|CActiveRecord::save] pentru a executa
si inserarea si actualizarea. Daca o instanta AR este creata folosind operatorul `new`,
atunci [save()|CActiveRecord::save] va insera un nou rand in tabela.
Daca instanta AR este rezultatul unei metode `find` sau `findAll`,
atunci [save()|CActiveRecord::save] va actualiza randul existent din tabela. De fapt,
putem folosi [CActiveRecord::isNewRecord] pentru a verifica daca instanta AR
este noua sau nu.

Este de asemenea posibil sa actualizam unul sau mai multe randuri dintr-o tabela
fara sa incarcam inainte. AR pune la dispozitie urmatoarele metode pentru acest scop:

~~~
[php]
// actualizeaza randurile care indeplinesc conditia specificata
Post::model()->updateAll($attributes,$condition,$params);
// actualizeaza randurile care se potrivesc cu conditia specificata si cu cheile primare specificate
Post::model()->updateByPk($pk,$attributes,$condition,$params);
// actualizeaza contorizarea coloanelor din randurile care indeplinesc conditia specificata
Post::model()->updateCounters($counters,$condition,$params);
~~~

In cele de mai sus, `$attributes` este un array cu valori de coloane indexate dupa numele coloanelor;
`$counters` este un array cu valori de incrementare indexate dupa numele coloanelor;
iar `$condition` si `$params` sunt descrie in subsectiunea anterioara. 

Stergerea inregistrarilor
-------------------------

Putem de asemenea sterge un rand din tabela daca o instanta AR a fost populata cu acest rand.

~~~
[php]
$post=Post::model()->findByPk(10); // presupunem ca este un post cu ID=10
$post->delete(); // sterge randul din tabela
~~~

Este de notat ca, dupa stergere, instanta AR ramane neschimbata, dar randul corespunzator din tabela
este deja sters.

Urmatoarele metode sunt puse la dispozitie pentru a sterge randuri fara a fi nevoie sa le
incarcam intai intr-o instanta AR:

~~~
[php]
// sterge randurile care indeplinesc conditia specificata
Post::model()->deleteAll($condition,$params);
// sterge randurile care se potrivesc cu cheile primare si cu conditia specificata
Post::model()->deleteByPk($pk,$condition,$params);
~~~

Validarea datelor
-----------------

Atunci cand se insereaza sau se actualizeaza un rand, deseori trebuie sa verificam daca valorile
coloanelor sunt valide. Validarea datelor este importanta in special daca valorile
coloanelor sunt furnizate de catre utilizatori. In general, nu ar trebui sa avem niciodata
incredere in informatiile care vin de la client.

AR executa validarea datelor automat atunci cand este apelata
[save()|CActiveRecord::save]. Validarea este bazata pe regulile specificate in metoda
[rules()|CModel::rules] a clasei AR. Pentru mai multe detalii despre specificarea regulilor
de validare, trebuie sa vedem sectiunea [Declararea regulilor de validare](/doc/guide/form.model#declaring-validation-rules).
Mai jos este un flux de lucru necesar salvarii unei inregistrari:

~~~
[php]
if($post->save())
{
	// datele sunt valide si sunt inserate/actualizate cu succes
}
else
{
	// datele nu sunt valide, trebuie apelata getErrors() pentru a primi mesajele de eroare
}
~~~

Atunci cand datele care trebuie inserate/actualizate sunt furnizate de catre clienti printr-un
form HTML, trebuie sa asignam aceste date proprietatilor AR corespunzatoare. Putem face acest lucru in
felul urmator:

~~~
[php]
$post->title=$_POST['title'];
$post->content=$_POST['content'];
$post->save();
~~~

Daca sunt multe coloane, vom vedea un sir lung de astfel de atribuiri.
Pentru a ocoli acest lucru, se poate folosi proprietatea
[attributes|CActiveRecord::attributes] ca in exemplul de mai jos. Mai multe detalii le gasim
in sectiunea [Securizarea asignarii atributelor](/doc/guide/form.model#securing-attribute-assignments)
si in sectiunea [Creare action](/doc/guide/form.action).

~~~
[php]
// se presupune ca $_POST['Post'] este un array de valori de coloane indexat dupa numele coloanelor
$post->attributes=$_POST['Post'];
$post->save();
~~~


Compararea inregistrarilor
--------------------------

La fel ca randurile tabelei, instantele AR sunt identificate unic prin valorile cheilor
lor primare. De aceea, pentru a compara doua instante AR, trebuie sa comparam doar
valorile cheilor lor primare, presupunand ca ele instantele apartin aceleiasi clase AR.
O cale mai simpla, totusi, este sa apelam [CActiveRecord::equals()].

> Info: Spre deosebire de implementarile AR din alte framework-uri, Yii are suport
pentru chei primare compuse in instantele sale AR. O cheie primara compusa este formata din
doua sau mai multe coloane. Valoarea cheii primare este reprezentata in Yii ca un array.
Proprietatea [primaryKey|CActiveRecord::primaryKey] defineste valoarea cheii primare a unei
instante AR.

Customizare
-----------

[CActiveRecord] pune la dispozitie cateva metode care pot fi suprascrise in clasele derivate
pentru a schimba fluxul de lucru.

   - [beforeValidate|CModel::beforeValidate] si
[afterValidate|CModel::afterValidate]: acestea sunt apelate inainte si dupa validare.

   - [beforeSave|CActiveRecord::beforeSave] si
[afterSave|CActiveRecord::afterSave]: acestea sunt apelate inainte si dupa salvarea
unei instante AR.

   - [beforeDelete|CActiveRecord::beforeDelete] si
[afterDelete|CActiveRecord::afterDelete]: acestea sunt apelate inainte si dupa ce o instanta
AR este stearsa.

   - [afterConstruct|CActiveRecord::afterConstruct]: aceasta este apelata pentru fiecare
instanta AR creata cu operatorul `new`.

   - [afterFind|CActiveRecord::afterFind]: aceasta este apelata pentru fiecare instanta AR
creata ca rezultat al cererii.


Folosirea tranzactiilor cu AR
-----------------------------

Fiecare instanta AR contine o proprietate cu numele
[dbConnection|CActiveRecord::dbConnection] care este o instanta [CDbConnection].
De aceea, putem folosi [tranzactii](/doc/guide/database.dao#using-transactions),
feature pus la dispozitie de Yii DAO in cazul in care se doreste folosirea lor cu AR.

~~~
[php]
$model=Post::model();
$transaction=$model->dbConnection->beginTransaction();
try
{
	// find si save sunt doi pasi care pot fi intrerupti de o alta cerere
	// de aceea, folosirea unei tranzactii asigura consistenta si integritate datelor
	$post=$model->findByPk(10);
	$post->title='Titlu nou post';
	$post->save();
	$transaction->commit();
}
catch(Exception $e)
{
	$transaction->rollBack();
}
~~~

<div class="revision">$Id: database.ar.txt 687 2009-02-17 02:57:56Z qiang.xue $</div>