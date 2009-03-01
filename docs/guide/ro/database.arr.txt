Active Record relational
========================

Am aratat deja cu se foloseste Active Record (AR) pentru a selecta date dintr-o
singura tabela a bazei de date. In aceasta sectiune, descriem cum se foloseste AR
pentru a face join intre mai multe tabele din baza de date si pentru a intoarce
setul de date compus. 

Pentru a folosi AR relational, estenecesar ca relatiile dintre cheile primare de
tip foreign sa fie clar definite intre tabelele carora li se aplica join. AR
se bazeaza pe metadatele despre aceste relatii pentru a determina cum se aplica join
acestor tabele.

> Note|Nota: Incepand cu versiunea 1.0.1 a Yii, putem folosi AR relational chiar daca
> nu definim constrangeri intre cheile foreign in baza de date.

Pentru simplicate, vom folosi schema bazei de date din diagrama ER (entity-relationship)
de mai jos in exemplele din aceasta sectiune. 

![ER Diagram](er.png)

> Info: Suportul pentru constrangeri cu chei foreign depinde de DBMS.
>
> SQLite nu are suport pentru astfel de constrangeri. Dar putem totusi
> declara constrangerile atunci cand cream tabelele. AR poate exploata aceste declaratii
> pentru a aduce un suport pentru cererile relationale.
>
> MySQL are suport pentru astfel de constrangeri doar cu engine-ul InnoDB. De aceea este
> recomandat sa folosim InnoDB in bazele de date MySQL.
> Atunci cand se foloseste MyISAM, putem sa exploatam urmatorul truc pentru a putea
> sa executam cereri relationale folosind AR:
> ~~~
> [sql]
> CREATE TABLE Foo
> (
>   id INTEGER NOT NULL PRIMARY KEY
> );
> CREATE TABLE bar
> (
>   id INTEGER NOT NULL PRIMARY KEY,
>   fooID INTEGER
>      COMMENT 'CONSTRAINT FOREIGN KEY (fooID) REFERENCES Foo(id)'
> );
> ~~~
> In cele de mai sus, folosim cuvantul cheie `COMMENT` pentru a descrie constrangerea foreign
> care poate fi citita de catre AR pentru a recunoaste relatia descrisa.



Declararea relatiei
-------------------

Inainte de a folosi AR pentru a executa cereri relationale, trebuie sa informam
AR despre tipul de relatie dintre clasele AR.

Relatia dintre doua clase AR este direct asociata cu relatia dintre tabelele bazei de date
reprezentate de catre clasele AR. Din punctul de vedere al bazei de date, o relatie dintre
doua tabele A si B este de trei tipuri: one-to-many (ex. `User` si `Post`), one-to-one (ex.
`User` si `Profile`) si many-to-many (ex. `Category` si `Post`). In AR,
exista patru tipuri de relatii:

   - `BELONGS_TO`: Daca relatia dintre tabelele A si B este
one-to-many, atunci B apartine lui A (ex. `Post` apartine lui `User`);

   - `HAS_MANY`: daca relatia dintre tabelele A si B este one-to-many,
atunci A are mai multi B (ex. `User` are multe `Post`);

   - `HAS_ONE`: acesta este un caz special al lui `HAS_MANY`, in care A are cel mult un
B (ex. `User` are cel mult un `Profile`);

   - `MANY_MANY`: acesta corespunde cu relatia many-to-many din baza de date.
O tabela asociativa este necesara pentru a sparge o relatie many-to-many
in relatii one-to-many, din moment ce majoritatea DBMS nu au suport pentru relatii
many-to-many direct. In schema bazei de date din exemplul nostru,
`PostCategory` serves for this purpose. In AR terminology, we can explain
`MANY_MANY` as the combination of `BELONGS_TO` and `HAS_MANY`. For example,
`Post` belongs to many `Category` and `Category` has many `Post`.

Declararea relatiei in AR implica suprascrierea metodei
[relations()|CActiveRecord::relations] din clasa [CActiveRecord]. Metoda
returneaza un array cu configuratiile de relatii. Fiecare element din array reprezinta
o singura relatie cu urmatorul format:

~~~
[php]
'VarName'=>array('RelationType', 'ClassName', 'ForeignKey', ...optiuni aditionale)
~~~

`VarName` este numele relatiei; `RelationType` specifica tipul
relatiei, care poate fi unul din patru constante:
`self::BELONGS_TO`, `self::HAS_ONE`, `self::HAS_MANY` si
`self::MANY_MANY`; `ClassName` este numele clasei AR in relatie cu aceasta clasa AR;
si `ForeignKey` precizeaza cheile foreign key implicate in relatie. Optiuni aditionale
pot fi specificate la sfarsitul fiecarei relatii (se va descrie mai tarziu acest lucru). 

Urmatorul cod arata cum declaram relatiile pentru clasele `User` si `Post`.

~~~
[php]
class Post extends CActiveRecord
{
	public function relations()
	{
		return array(
			'author'=>array(self::BELONGS_TO, 'User', 'authorID'),
			'categories'=>array(self::MANY_MANY, 'Category', 'PostCategory(postID, categoryID)'),
		);
	}
}

class User extends CActiveRecord
{
	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY, 'Post', 'authorID'),
			'profile'=>array(self::HAS_ONE, 'Profile', 'ownerID'),
		);
	}
}
~~~

> Info: O cheie foreign poate fi compusa, fiind formata din doua sau mai
multe coloane. In acest caz, ar trebui sa concatenam numele coloanelor care
contin cheile foreign si sa separam cu spatiu sau cu virgula. Pentru tipul
de relatie `MANY_MANY`, tabela asociativa trebuie sa fie specificata de asemenea in
cheia foreign. De exemplu, relatia `categories` din `Post` este specificata cu cheia foreign
`PostCategory(postID, categoryID)`.

Declararea relatiilor intr-o clasa AR adauga implicit o proprietate clasei pentru fiecare
relatie. Dupa ce este executata o cerere relationala, proprietatea corespunzatoare va fi
populata cu instantele AR cu care s-a facut legatura. De exemplu, daca `$author`
reprezinta o instanta AR `User`, putem folosi `$author->posts` pentru a accesa
instantele sale `Post`.

Executarea cererilor relationale
--------------------------------

Cel mai simplu mod de executie al unei cereri relationale este prin citirea proprietatii
relationale dintr-o instanta AR. Daca proprietatea nu este accesata anterior, va fi initiata
o cerere relationala care aplica join celor doua tabele si filtreaza dupa cheia primara a
instantei AR curente. Rezultatul cererii va fi salvat in proprietate ca instanta (sau instante)
ale clasei (claselor) AR respective. Aceasta abordare este cunoscuta sub termenul de
*lazy loading* (incarcare pt puturosi:D), aceasta insemnand ca cererea relationala
este executata atunci cand obiectele respective sunt accesate initial. Exemplul de mai jos
arata cum sa folosim aceasta abordare:

~~~
[php]
// extragem post-ul cu ID=10
$post=Post::model()->findByPk(10);
// extragem autorul post-ului: o cerere relationala va fi executata aici
$author=$post->author;
~~~

> Info: Daca nu este nici o instanta reprezantand relatia respectiva,
proprietatea va fi null sau un array gol. Pentru relatiile
`BELONGS_TO` si `HAS_ONE`, proprietatea va fi null; pentru relatiile
`HAS_MANY` si `MANY_MANY`, proprietatea va fi un array gol.

Abordarea lazy loading este foarte convenabila, dar in unele scenarii nu este eficienta deloc.
De exemplu, daca vrem sa accesam informatiile despre autor pentru
`N` post-uri, folosind abordarea lazy ar implica executarea a
`N` cereri join. In acest caz, abordarea *eager loading* este de preferat.

Abordarea eager loading extrage instantele AR de legatura in acelasi timp cu
instanta AR principala. Acest lucru este facut folosind metoda
[with()|CActiveRecord::with] impreuna cu una dintre metodele
[find|CActiveRecord::find] sau [findAll|CActiveRecord::findAll] din AR.
De exemplu:

~~~
[php]
$posts=Post::model()->with('author')->findAll();
~~~

Codul de mai sus va returna un array de instante `Post`. Spre deosebire de abordarea
lazy, proprietatea `author` din fiecare instanta `Post` este deja populata
cu instantele corespunzatoare `User` inainte ca noi sa accesam proprietatea.
In loc de a executa o cerere join pentru fiecare post, prin abordarea eager loading
se extrag toate post-urile cu autorii lor intr-un singura cerere join!

Putem specifica mai multe nume de relatii in metoda
[with()|CActiveRecord::with]. Astfel, abordarea eager loading va crea toate relatiile
impreuna in acelasi timp. De exemplu, urmatorul cod va extrage toate post-urile
impreuna cu autorii si categoriile lor:

~~~
[php]
$posts=Post::model()->with('author','categories')->findAll();
~~~

Putem de asemenea sa facem eager loading pe nivele. In loc sa furnizam o
lista de nume de relatii, furnizam o reprezentare ierarhica de nume de relatii
catre metoda [with()|CActiveRecord::with], ca in exemplul urmator:

~~~
[php]
$posts=Post::model()->with(
	'author.profile',
	'author.posts',
	'categories')->findAll();
~~~

Codul de mai sus va extrage toate post-urile impreuna cu autorul si categoriile lor.
De asemenea, vor fi extrase post-urile fiecarui autor si profilul sau.

> Note|Nota: Folosirea metodei [with()|CActiveRecord::with] a fost modificata incepand cu
> versiunea 1.0.2 a Yii. Trebuie citita cu atentie documentatia API in cauza.

Implementarea AR din Yii este foarte eficienta. Atunci cand se aplica eager loading
cu o ierarhie de obiecte aflate in `N` relatii `HAS_MANY` sau `MANY_MANY`
vor fi necesare `N+1` cereri SQL pentru a obtine rezultatele necesare.
Aceasta inseamna ca, in exemplul anterior, trebuie executate 3 cereri SQL
din cauza proprietatilor `posts` si `categories`. Alte framework-uri au o abordare mult
mai radicala folosind doar o singura cerere SQL. La prima vedere, aceasta abordare pare
mai eficienta, pentru ca ar fi implicata doar o singura cerere SQL. In realitate,
nu este deloc practic din doua motive. In primul rand, sunt multe coloane de date repetitive
in rezultat care necesita un timp in plus pentru a fi transmise si procesate. In al doilea rand,
numarul de randuri din setul de rezultate creste exponential cu numarul de tabele implicate. Daca sunt
mai multe relatii implicate, totul devine atat de greoi si complex incat nu mai poate fi gestionat
corespunzator.

Din versiunea 1.0.2 a Yii, putem de asemenea sa fortam o cerere relationala
sa fie facuta intr-o singura cerere SQL. Trebuie doar sa adaugam un apel
[together()|CActiveFinder::together] dupa after [with()|CActiveRecord::with]. De exemplu:example,

~~~
[php]
$posts=Post::model()->with(
	'author.profile',
	'author.posts',
	'categories')->together()->findAll();
~~~

Codul de mai sus va fi facut intr-o singura cerere SQL. Fara
apelarea [together|CActiveFinder::together], ar fi fost necesare doua cereri SQL: una in care
se aplica join intre tabelele `Post`, `User` si `Profile`, iar cealalta in care se aplica
join intre tabelele `User` si `Post`.


Optiuni in cererile relationale
------------------------

Am mentionat ca pot fi specificate optiuni aditionale in declaratia relatiei.
Aceste optiuni, specificate intr-un array de perechi key-value, sunt folosite
pentru a customiza cererea relationala. Avem un sumar mai jos.

   - `select`: o lista de coloane care vor fi selectate pentru clasa AR de legatura.
Implicit, aceasta lista este '*', adica toate coloanele. Numele de coloane ar trebui
sa fie diferentiate folosind `aliasToken` daca apar intr-o expresie (ex.
`COUNT(??.name) AS nameCount`).

   - `params`: parametrii care vor fi legati la instructiunea SQL.
Ar trebui sa primeasca un array cu perechi nume-valoare. Aceasta optiune este disponibila
incepand cu versiunea 1.0.3.

   - `condition`: clauza `WHERE`. Implicit nu contine nimic, Referintele catre coloane
trebuie sa fie diferentiate folosind `aliasToken` (ex. `??.id=10`).

   - `on`: clauza `ON`. Conditia specificata aici va fi adaugata la conditia join
folosind operatorul `AND`. Aceasta optiune este disponibila incepand cu versiunea
1.0.2 a Yii.

   - `order`: clauza `ORDER BY`. implicit nu contine nimic. Referintele catre coloane
trebuie sa fie diferentiate folosind `aliasToken` (ex. `??.age DESC`).

   - `with`: o lista cu obiectele inrudite care ar trebui incarcate impreuna cu acest obiect.
Aceasta lista este creata doar prin abordarea lazy loading, nu eager loading.

   - `joinType`: tipul de join pentru aceasta relatie. Implcit este `LEFT OUTER JOIN`.

   - `aliasToken`: placeholder pentru prefix de coloana. Va fi inlocuit
cu alias-ul tabelei corespunzatoare pentru a se putea discrimina referintele la
coloane. Implicit este `'??.'`.

   - `alias`: alias pentru tabela asociata cu aceasta relatie.
Aceasta optiune este disponibila din versiunea 1.0.1 a Yii. Implicit este null,
adica alias-ul tabelei este generat automat. Este diferit fata de
`aliasToken`. `aliasToken` este doar un placeholder si va fi inlocuit cu alias-ul
tabelei in cauza.

   - `together`: daca tabela asociata cu aceasta relatie should ar trebui sa faca un join fortat
cu tabela primara. Aceasta optiune are sens pentru relatiile HAS_MANY si MANY_MANY. 
Daca optiunea nu este setata sau este false, fiecare relatie HAS_MANY sau MANY_MANY
va avea instructiunea ei JOIN proprie pentru a imbunatati performanta.
Aceasta optiune este disponibila incepand cu versiunea 1.0.3.

In plus, sunt disponibile urmatoarele optiuni pentru anumite relatii
in timpul abordarii lazy loading:

   - `group`: clauza `GROUP BY`. Implicit nu contine nimic. De notat ca referintele
la coloane trebuie diferentiate folosind `aliasToken` (ex. `??.age`).
Aceasta optiune este valabila doar in cazul relatiilor `HAS_MANY` si `MANY_MANY`.

   - `having`: clauza `HAVING`. Implicit nu contine nimic.  De notat ca
referintele la coloane trebuie sa fie diferentiate folosind `aliasToken` (ex. `??.age`).
Aceasta optiune este valabila doar in cazul relatiilor `HAS_MANY` si `MANY_MANY`. Este disponibila
incepand cu versiunea 1.0.1 a Yii.

   - `limit`: clauza limit pentru limitarea randurilor selectate. Aceasta optiune NU se aplica
relatiei `BELONGS_TO`.

   - `offset`: offset pentru randurile care vor fi selectate. Aceasta optiune NU se aplica
relatiei `BELONGS_TO`.

Mai jos, modificam declaratia de relatie `posts` din `User` prin includerea unor
optiuni de mai sus:

~~~
[php]
class User extends CActiveRecord
{
	public function relations()
	{
		return array(
			'posts'=>array(self::HAS_MANY, 'Post', 'authorID'
							'order'=>'??.createTime DESC',
							'with'=>'categories'),
			'profile'=>array(self::HAS_ONE, 'Profile', 'ownerID'),
		);
	}
}
~~~

Acum, daca accesam `$author->posts`, ar trebui sa obtinem post-urile autorului
sortate dupa timpul de creare, in ordine descendenta. Fiecare instanta post
are de asemenea categoriile incarcate deja.

> Info: Atunci cand un nume de coloana apare in doua sau mai multe
tabele care au fost legate printr-un JOIN, trebuie sa fie diferentiate.
Acest lucru il facem prin prefixarea numelui de coloana cu numele tabelei.
De exemplu, `id` devine `Team.id`. Totusi, in cererile relationale AR
nu avem aceasta libertate deoarce instructiunile SQL sunt generate automat
de catre AR, deci fiecare tabela va primi automat un alias.
De aceea, pentru a evita eventuale conflicte dintre numele coloanelor,
folosin un placeholder pentru a indica existenta unei coloane 
care trebuie sa fie diferentiata fata de celelalte. AR va inlocui 
placeholder-ul cu un alias de tabela corespunzator pentru a diferentia corect coloana in cauza.

Optiuni pentru cereri relationale dinamice
------------------------------------------

Incepand cu versiunea 1.0.2, Putem folosi optiuni pentru cereri relationale dinamice
si in [with()|CActiveRecord::with] si in optiunea `with`. Optiunile dinamice
vor suprascrie optiunile existente specificate in metoda [relations()|CActiveRecord::relations].
De exemplu, in cazul modelului `User` de mai sus, daca vrem sa folosim abordarea
eager loading pentru a incarca toate post-urile care apartin unui autor,
*ascending order* (optiunea `order` din specificatia relatiei este setata cu ordine desecendenta),
putem face in felul urmator:

~~~
[php]
User::model()->with(array(
	'posts'=>array('order'=>'??.createTime DESC'),
	'profile',
))->findAll();
~~~

<div class="revision">$Id: database.arr.txt 683 2009-02-16 05:20:17Z qiang.xue $</div>
