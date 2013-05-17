Crearea primei aplicatii Yii
============================

Pentru a intra in contact prima data cu Yii, descriem in aceasta sectiune cum sa cream
prima noastra aplicatie Yii. Vom folosi unealta (foarte puternica) `yiic`
pe care o vom folosi pentru a crea automat cod pentru anumite taskuri. Pentru convenienta,
presupunem ca `YiiRoot` este directorul in care a fost instalata platforma Yii, iar `WebRoot`
este documentul radacina al serverului nostru Web.

Rulam `yiic` in linia de comanda in felul urmator:

~~~
% YiiRoot/framework/yiic webapp WebRoot/myproject
~~~

> Note|Nota: Atunci cand rulam `yiic` in Mac OS, Linux sau Unix, va trebui sa modificam
> permisiunile fisierului `yiic` pentru a fi executabil.
> Altfel, putem rula unealta si in felul urmator:
>
> ~~~
> % cd WebRoot/myproject
> % php YiiRoot/framework/yiic.php webapp WebRoot/myproject
> ~~~

Aceste comenzi vor crea un schelet de aplicatie Yii in directorul
`WebRoot/myproject`. Aplicatia are o structura de directoare care este necesarea
pentru majoritatea aplicatiilor Yii.

Fara sa scriem nici o linie de cod, putem testa prima noastra aplicatie Yii
prin accesarea urmatorului URL intr-un browser Web: 

~~~
http://hostname/myproject/index.php
~~~

Dupa cum putem vedea, aplicatia are trei pagini: pagina home, pagina contact
si pagina de logare. Pagina home contine cateva informatii despre aplicatie
si despre utilizator. Pagina de contact afiseaza un formular de contact care poate fi
completat de catre utilizatori. Pagina de logare permite utilizatorilor sa fie autentificati 
inainte de a accesa continut pentru care au nevoie de anumite privilegii.
Putem vedea screenshot-urile urmatoare pentru mai multe detalii.

![Pagina home](first-app1.png)

![Pagina contact](first-app2.png)

![Pagina contact cu afisare erori la intrarea datelor](first-app3.png)

![Pagina contact cu afisare succes](first-app4.png)

![Pagina de logare](first-app5.png)


Urmatoarea diagrama ne arata structura de directoare a aplicatiei.
Trebuie vazuta sectiunea [Conventii](/doc/guide/basics.convention#directory) pentru
explicatii detaliate despre aceasta structura.

~~~
myproject/
   index.php                 fisierul php de intrare in aplicatia Web
   assets/                   contine fisiere cu resurse accesibile din Web
   css/                      contine fisiere CSS
   images/                   contine imagini
   themes/                   contine teme
   protected/                contine fisierele protejate ale aplicatiei
      yiic                   scriptul yiic de linie de comanda
      yiic.bat               scriptul yiic de linie de comanda pt Windows
      commands/              contine comenzi customizate pt 'yiic'
         shell/              contine comenzi customizate pt 'yiic shell'
      components/            contine componente utilizator reutilizabile
         MainMenu.php        clasa widget-ului 'MainMenu'
         Identity.php        clasa 'Identity' folosita pentru autentificare
         views/              contine fisiere view pentru widget-uri
            mainMenu.php     fisierul view pentru widget-ul 'MainMenu'
      config/                contine fisiere de configurare
         console.php         configuratia aplicatiei consola
         main.php            configuratia aplicatiei Web
      controllers/           contine fisierele cu clasele controller-elor
         SiteController.php  clasa controller-ului implicit
      extensions/            contine extensii third-party
      messages/              contine mesaje traduse
      models/                contine fisiere cu clasele modelelor
         LoginForm.php       modelul de tip formular pentru action-ul 'login'
         ContactForm.php     modelul de tip formular pentru action-ul 'contact'
      runtime/               contine fisiere generate temporar
      views/                 contine fisiere layout si view-urile controller-elor
         layouts/            contine fisiere layout pt view-uri
            main.php         layout-ul implicit pt toate view-urile
         site/               contine fisierele view pentru controller-ul 'site'
            contact.php      view-ul pentru action-ul 'contact'
            index.php        view-ul pentru action-ul 'index'
            login.php        view-ul pentru action-ul 'login'
         system/             contine fisierele view pt erorile sistemului
~~~

Conectarea la baza de date
--------------------------

Majoritatea aplicatiilor Web folosesc baze de date, iar aplicatia noastra
nu este o exceptie. Pentru a folosi o baza de date, trebuie sa spunem
aplicatiei cum sa se conecteze la ea. Putem face acest lucru prin modificarea fisierului
de configurare al aplicatiei `WebRoot/myproject/protected/config/main.php`, in felul urmator:

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/source.db',
		),
	),
	......
);
~~~

In codul de mai sus, adaugam o intrare `db` la `components`. Acest lucru spune
aplicatiei sa se conecteze la baza de date SQLite
`WebRoot/myproject/protected/data/source.db` atunci cand este necesar.

> Note|Nota: Pentru a folosi o baza de date in Yii, trebuie sa activam extensia PHP PDO
si extensia PDO cu driver-ul specific pentru baza de date pe care vrem sa o folosim. 
Pentru aplicatia noastra, trebuie ca extensiile `php_pdo` si `php_pdo_sqlite` sa fie activate.

Trebuie sa cream o baza de date SQLite astfel incat configuratia de mai sus sa
functioneze. Folosind orice unealta de administrare SQLite, putem crea o baza de date
cu urmatoarea structura:

~~~
[sql]
CREATE TABLE User (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

Pentru simplitate, cream o singura tabela (`User`) in baza de date. Fisierul
bazei de date SQLite este salvat avand urmatoarea cale: `WebRoot/myproject/protected/data/source.db`. 
Trebuie remarcat ca fisierul bazei de date si directorul care contine acest fisier
trebuie sa permita scrierea de catre serverul Web server. 


implementarea operatiilor CRUD
------------------------------

Acum este partea interesanta. Pentru ca vrem sa implementam operatiile CRUD (create, read,
update si delete) pentru tabela `User` tocmai creata. Aceste operatii sunt foarte folosite
in aplicatiile reale.

In loc de a ne chinui sa scriem codul pentru aceste operatii, mai bine folosim unealta
`yiic` din nou pentru a genera automat codul pentru noi. Acest proces mai este cunoscut sub
numele de *scaffolding*. Deschidem o fereastra cu linia de comanda, si executam urmatoarele
comenzi:

~~~
% cd WebRoot/myproject
% protected/yiic shell
Yii Interactive Tool v1.0
Please type 'help' for help. Type 'exit' to quit.
>> model User
   generate User.php

The 'User' class has been successfully created in the following file:
    D:\wwwroot\myproject\protected\models\User.php

If you have a 'db' database connection, you can test it now with:
    $model=User::model()->find();
    print_r($model);

>> crud User
   generate UserController.php
   generate create.php
      mkdir D:/wwwroot/myproject/protected/views/user
   generate update.php
   generate list.php
   generate show.php

Crud 'user' has been successfully created. You may access it via:
http://hostname/path/to/index.php?r=user
~~~

In cele de mai sus, folosim comanda `yiic shell` pentru a interactiona cu aplicatia
noastra. Executam doua sub-comenzi: `model User` si `crud User`.
`model User` genereaza clasa modelului tabelei `User`.
`crud User` citeste modelul `User` si genereaza codul necesar pentru operatiile CRUD.

> Note|Nota: Putem intalni erori de genul "...could not find driver", chiar daca
> verificarea cerintelor necesare a avut succes si arata ca avem activate extensia PDO
> si driverul PDO corespunzator pentru baza noastra de date. Daca se intampla acest lucru,
> putem incerca sa rulam unealta `yiic` in felul urmator:
>
> ~~~
> % php -c path/to/php.ini protected/yiic.php shell
> ~~~
>
> unde `path/to/php.ini` reprezinta fisierul ini corect al PHP.

Acum ne putem bucura de rezultate folosind urmatorul URL:

~~~
http://hostname/myproject/index.php?r=user
~~~

Acest URL va afisa o lista cu intrari de utilizatori din tabela `User`. Din moment ce
tabela noastra este goala, nu apare nimic in acest moment.

Apasam pe link-ul `New User` din pagina. Va aparea pagina de logare
in cazul in care nu ne-am mai logat anterior. Dupa ce ne-am logat, apare
un formular de intrare care ne permite sa adaugam o noua intrare de utilizator.
Completam formularul si apasam pe butonul `Create`. Daca avem vreo eroare la intrare,
va aparea un prompt dragut cu erorile in cauza. Astfel suntem impiedicati sa salvam datele.
Revenind la lista de utilizatori, ar trebui sa vedem utilizatorul nou adaugat ca apare in lista.

Repetam pasii de mai sus pentru a adauga mai multi utilizatori. Putem remarca
faptul ca pagina cu lista utilizatorilor va face paginare automat daca sunt prea multi
utilizatori de afisat pe o singura pagina.

Daca ne logam ca administrator folosind `admin/admin`, putem vedea pagina utilizatorului
admin in URL-ul acesta:

~~~
http://hostname/myproject/index.php?r=user/admin
~~~

Vom vedea aici o tabela cu intrarile utilizatorilor. Putem apasa pe celulele header ale
tabelei pentru a sora coloanele corespunzator. La fel ca si pagina cu lista utilizatorilor,
pagina admin face paginare automata.

Beneficiem de toate aceste feature-uri fara sa scriem nici macar o linie de cod!

![Pagina utilizatorului admin](first-app6.png)

![Pagina creare utilizator nou](first-app7.png)



<div class="revision">$Id: quickstart.first-app.txt 723 2009-02-21 18:14:05Z qiang.xue $</div>