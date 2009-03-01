Application
===========

Application este locul unde se executa procesarea cererilor client. Rolul
principal este analizarea cererii client si transmiterea ei la controller-ul 
corespunzator pentru a fi procesata in continuare. De asemenea, Application joaca 
un rol central pentru pastrarea configuratiilor la nivel de aplicatie. De aceea, 
application mai este numita `front-controller` (controller radacina, principal).

Application este creata ca singleton de catre [fisierul de intrare](/doc/guide/basics.entry).
In acest fel, accesul este posibil de oriunde via [Yii::app()|YiiBase::app].


Configurare
-----------

La baza, application este o instanta a [CWebApplication]. Pentru customizare,
in mod normal trebuie sa furnizam un fisier de configurare (care este de fapt un array)
pentru a initializa valorile proprietatilor atunci cand instanta application este creata.
Ca alternativa de customizare, putem extinde [CWebApplication].

Configuratia in sine este un array cu perechi key-value (cheie-valoare). Fiecare key reprezinta
numele proprietatii instantei application, iar valoarea reprezinta valoarea initiala a proprietatii.
De exemplu, asa se configureaza proprietatile [name|CApplication::name] si
[defaultController|CWebApplication::defaultController]: 

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

De obicei retinem configuratia intr-un fisier PHP separat (ex.
`protected/config/main.php`). Aici, se returneaza array-ul de configurare dupa cum urmeaza:

~~~
[php]
return array(...);
~~~

Ca sa aplicam configuratia, transmitem numele fisierului PHP ca parametru al constructorului clasei
application, sau ca parametru al [Yii::createWebApplication()] in felul urmator
(asa se face de obicei in [fisierul de intrare](/doc/guide/basics.entry) ):

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Tip|Sfat: Daca aplicatia are nevoie de o configuratie complexa, putem sa o separam in mai multe
fisiere, fiecare intorcand un array de configurare. Dupa aceea, in fisierul de configurare principal,
adaugam cu `include()` fiecare fisier creat.


Application Base Directory
--------------------------
 
Application base directory se refera la directorul radacina care contine toate fisierele PHP
care trebuie ascunse fata de clienti. Implicit, acest director este denumit `protected` si se afla
in acelasi director cu fisierul php accesibil clientilor. Totusi, poate fi schimbat acest director
prin proprietatea [basePath|CWebApplication::basePath] din [configuratia aplicatiei](#application-configuration).

Tot ce este in acest director special ar trebui protejat fata de orice client WEB.
Cu [Apache HTTP server](http://httpd.apache.org/) protectia se face foarte simplu printr-un fisier
`.htaccess` pus in acest director. Continutul fisierului `.htaccess` este: 

~~~
deny from all
~~~

Componente
----------

Functionalitatea aplicatiei poate fi usor customizata si imbogatita datorita arhitecturii foarte
flexibile de componente. Application gestioneaza un set de componente, fiecare implementand diverse
features. De exemplu, application analizeaza o cerere client cu ajutorul componentelor [CUrlManager] si
[CHttpRequest].

Configurand proprietatea [components|CApplication::components], putem customiza orice valori ale
componentelor folosite in aplicatie. De exemplu, putem configura componenta [CMemCache] pentru a folosi
mai multe servere memcache:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
)
~~~

Adaugam elementul `cache` la array-ul `components`. Elementul
`cache` retine clasa folosita de componenta, clasa fiind `CMemCache`, iar proprietarea `servers`
ar trebui initializata in acest fel.

ca sa accesam o componenta, folosim `Yii::app()->ComponentID`, unde
`ComponentID` se refera la ID-ul componentei (ex. `Yii::app()->cache`).

O componenta poate fi dezactivata atribuind lui `enabled` valoarea false. Daca incercam sa accesam
o componenta dezactivata, atunci primim null.

> Tip|Sfat: Implicit, componentele sunt create la cerere. Ca rezultat, componenta nu va fi creata
daca nu este accesata in timpul unei cereri client. Ca rezultat, performanta per ansamblu nu va scadea,
chiar daca aplicatia are o configuratie cu foarte multe componente. Unele componente (ex. [CLogRouter]) poate ar trebui
totusi sa fie create indiferent daca sunt accesate sau nu. Daca se doreste aces lucru, atunci
ID-urile lor trebuie mentionate in lista memorata in proprietatea [preload|CApplication::preload].

Componente nucleu
-----------------

Yii activeaza implicit un set de componente nucleu pentru a asigura anumite features intalnite in majoritatea
aplicatiilor Web. De exemplu, componenta [request|CWebApplication::request] este folosita pentru a analiza
cererile client si pentru a furniza informatii folositoare despre URL, cookies. Prin configurarea
proprietatilor acestor componente nucleu, putem schimba comportamentul implicit al Yii aproape in orice privinta. 

Mai jos este o lista de componente nucleu care sunt pre-declarate de catre [CWebApplication].

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] -
gestioneaza publicarea fisierelor private de tip asset (implicit acestea exista in directorul Asset).

   - [authManager|CWebApplication::authManager]: [CAuthManager] - gestioneaza role-based access control (RBAC - control acces bazat pe roluri).

   - [cache|CApplication::cache]: [CCache] - asigura cache de date. Este de retinut ca trebuie
specificata clasa care se va ocupa cu acest lucru (ex. [CMemCache], [CDbCache]). Altfel,
atunci cand se va accesa componenta se va primi null. 

   - [clientScript|CWebApplication::clientScript]: [CClientScript] -
gestioneaza scripturile client (javascript si CSS).

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] -
gestioneaza mesajele nucleu traduse folosite de platforma Yii.

   - [db|CApplication::db]: [CDbConnection] - asigura conexiunea la baza de date. Este de retinut
ca trebuie sa configuram proprietatea [connectionString|CDbConnection::connectionString]
pentru a putea folosi aceasta componenta.

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - trateaza exceptii si erori PHP. 

   - [messages|CApplication::messages]: [CPhpMessageSource] - gestioneaza mesaje traduse folosite de Yii.

   - [request|CWebApplication::request]: [CHttpRequest] - furnizeaza informatii despre cererile client.

   - [securityManager|CApplication::securityManager]: [CSecurityManager] -
asigura servicii de securitate, precum hashing si encryption.

   - [session|CWebApplication::session]: [CHttpSession] - functionalitati la nivel de sesiune.

   - [statePersister|CApplication::statePersister]: [CStatePersister] -
ofera o zona de date persistenta la nivel global al aplicatiei intre cererile client. 

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - creare si analizare URL.

   - [user|CWebApplication::user]: [CWebUser] - reprezinta informatiile despre identitatea utilizatorului curent.

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - gestiune teme.


Ciclul de viata al aplicatiei
-----------------------------

Atunci cand se trateaza o cerere client, aplicatia va trece prin urmatoarele stadii: 

   1. Seteaza tratarea de erori si autoloader-ul de clase;
   
   2. Inregistreaza componentele nucleu ale aplicatiei;
   
   3. Incarca configuratia aplicatiei;
   
   4. Initializeaza aplicatia cu [CApplication::init()]
	   - Incarca componentele statice ale aplicatiei;
	   
   5. Activeaza evenimentul [onBeginRequest|CApplication::onBeginRequest];
   
   6. Proceseaza cererea client:
	   - Analizeaza cererea client;
	   - Creaza controller-ul necesar;
	   - Ruleaza controller-ul;
   7. Activeaza evenimentul [onEndRequest|CApplication::onEndRequest];

<div class="revision">$Id: basics.application.txt 626 2009-02-04 20:51:13Z qiang.xue $</div>