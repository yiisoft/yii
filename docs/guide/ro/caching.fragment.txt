Cache de fragmente
==================

Caching-ul de fragmente se refera la memorarea in cache a unui fragment
dintr-o pagina. De exemplu, daca o pagina afiseaza un sumar al vanzarilor anuale
intr-o tabela, putem sa memoram aceasta tabela in cache pentru a elimina timpul
de generare al tabelei la fiecare cerere utilizator.

Pentru a folosi caching-ul de fragmente, putem sa apelam
[CController::beginCache()|CBaseController::beginCache()] si
[CController::endCache()|CBaseController::endCache()] intr-un fisier view al controller-ului.
Cele doua metode marcheaza inceputul si sfarsitul fragmentului din pagina care trebuie memorat
in cache. Ca si [caching-ul de date](/doc/guide/caching.data), avem nevoie de un ID
pentru a identifica fragmentul care va fi memorat in cache.

~~~
[php]
...alt continut HTML...
<?php if($this->beginCache($id)) { ?>
...continut de adaugat in cache...
<?php $this->endCache(); } ?>
...alt continut HTML...
~~~

In codul de mai sus, daca [beginCache()|CBaseController::beginCache()] returneaza
false, continutul memorat in cache va fi automat inserat in acel loc;
altfel, continutul dintre instructiunile `if` va fi executat si va fi adaugat in cache
atunci cand este invocata [endCache()|CBaseController::endCache()].

Optiuni Caching
---------------

Atunci cand apelam [beginCache()|CBaseController::beginCache()], putem furniza
un array ca parametru care va contine optiunile de caching cu care customizam
memorarea in cache a fragmentului. De fapt, metodele
[beginCache()|CBaseController::beginCache()] si [endCache()|CBaseController::endCache()]
sunt un wrapper convenabil al widget-ului [COutputCache]. De aceea,
optiunile caching pot contine valori initiale pentru orice proprietati ale clasei [COutputCache].

### Durata

Probabil ca cea mai folosita optiune este durata de memorare in cache [duration|COutputCache::duration]
care specifica timpul in care continutul de memorat va persista in cache. Este similar
cu parametrul de expirare al [CCache::set()]. Urmatorul cod memoreaza in cache fragmentul
pentru cel mult o ora:

~~~
[php]
...alt continut HTML...
<?php if($this->beginCache($id, array('duration'=>3600))) { ?>
...continut de memorat in cache...
<?php $this->endCache(); } ?>
...alt continut HTML...
~~~

Daca nu mentionam optiunea duration, valoarea va fi cea implicita, adica 60, ceea ce inseamna ca
memorarea in cache va persista maxim 60 de secunde.

### Dependenta

Ca si in cazul [caching-ului de date](/doc/guide/caching.data), fragmentul de memorat in cache
poate avea de asemenea dependente. De exemplu, continutul unui post care se afiseaza
depinde de starea post-ului, daca a fost modificat sau nu.

Pentru a specifica o dependenta, setam optiunea [dependency|COutputCache::dependency],
care poate fi sau un obiect care implementeaza [ICacheDependency] sau un array de configurare
care poate fi folosit pentru a genera obiectul care contine dependenta. Urmatorul cod specifica
dependenta fragmentului de valoarea coloanei `lastModified`:

~~~
[php]
...alt continut HTML...
<?php if($this->beginCache($id, array('dependency'=>array(
		'class'=>'system.caching.dependencies.CDbCacheDependency',
		'sql'=>'SELECT MAX(lastModified) FROM Post')))) { ?>
...continut de memorat in cache...
<?php $this->endCache(); } ?>
...alt continut HTML...
~~~

### Variatie

Continutul de memorat in cache poate sa varieze in functie de unii parametri.
De exemplu, profilul personal poate sa arate diferit unor utilizatori diferiti.
Pentru a memora in cache continutul profilelor, copiile memorate in cache ar trebui
variate in functie de ID-urile utilizatorilor. Esential, acest lucru inseamna
ca ar trebui sa folosim un ID diferit atunci cand apelam [beginCache()|CBaseController::beginCache()].

Nu trebuie sa cerem programatorilor sa creeze o schema de ID-uri, pentru ca acest feature
este deja implementat in [COutputCache]. Avem mai jos un sumar.

   - [varyByRoute|COutputCache::varyByRoute]: prin setarea acestei optiuni
cu valoarea true, continutul cache va fi variat in functie de
[ruta](/doc/guide/basics.controller#route). De aceea, fiecare combinatie de
controller si action va avea un continut memorat in cache separat.

   - [varyBySession|COutputCache::varyBySession]: prin setarea acestei optiuni cu
valoarea true, continutul memorat in cache va fi variat in functie de ID-urile de sesiune.
De aceea, fiecare sesiune utilizator poate vedea o versiune diferita a continutului si in acelasi
timp sa fie toti serviti din cache.

   - [varyByParam|COutputCache::varyByParam]: prin setarea acestei optiuni cu un array de nume,
continutul memorat in cache poate fi variat in functie de valorile unor parametri specificati in
GET. De exemplu, daca o pagina afiseaza continutul unui post in functie de parametrul
`id` din GET, putem sa setam [varyByParam|COutputCache::varyByParam] sa fie `array('id')`,
astfel in cat sa memoram in cache continutul pentru fiecare post al utilizatorului.
Fara o astfel de variatie, am putea sa memoram in cache un singur post al utilizatorului respectiv.

### Tipuri de cereri

Uneori vrem ca fragmentul sa fie memorat in cache doar cand se face un anumit tip de cerere din partea
utilizatorilor web. De exemplu, pentru o pagina care afiseaza un form, am dori sa memoram
in cache doar starea form-ului cand este initial ceruta de catre utilizatorii web
(prin GET). Orice afisare ulterioara a form-ului (prin POST) nu ar trebui sa fie servita din
cache datorita input-urilor care contin date introduse de utilizator. Pentru a face acest lucru,
putem specifica optiunea [requestTypes|COutputCache::requestTypes]:

~~~
[php]
...alt continut HTML...
<?php if($this->beginCache($id, array('requestTypes'=>array('GET')))) { ?>
...continut de memorat in cache...
<?php $this->endCache(); } ?>
...alt continut HTML...
~~~

Caching pe nivele
-----------------

Caching-ul de fragmente poate fi pe mai multe nivele (nested). Un fragment memorat in cache
poate sa fie inclus intr-un fragment mai mare care este de asemenea memorat in cache.
De exemplu, comentariile sunt memorate in interiorul unui fragment mai mare din cache, si sunt
memorate impreuna cu post-ul.

~~~
[php]
...alt continut HTML...
<?php if($this->beginCache($id1)) { ?>
...continutul exterior de memorat in cache...
	<?php if($this->beginCache($id2)) { ?>
	...continutul interior de memorat in cache...
	<?php $this->endCache(); } ?>
...continutul exterior de memorat in cache...
<?php $this->endCache(); } ?>
...alt continut HTML...
~~~

Pot fi setate mai multe optiuni in caching-ul pe nivele. De exemplu,
fragmentele din exemplul de mai sus (interior si exterior) pot fi memorate
pe durate diferite de timp. Atunci cand fragmentul exterior nu mai este valid,
fragmentul interior inca poate furniza continut valid.
Oricum, nu este valabil si invers. Daca fragmentul exterior furnizeaza continut valid,
va furniza mereu copia memorata in cache, chiar daca fragmentul interior deja a expirat.

<div class="revision">$Id: caching.fragment.txt 323 2008-12-04 01:40:16Z qiang.xue $</div>