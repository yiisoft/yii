Cache
=====

Caching este o modalitate eficienta si ieftina de a imbunatati performanta
unei aplicatii Web. Prin memorarea datelor statice in cache si prin servirea
acestora atunci cand este necesar, se castiga timpul in care aceste date statice
ar fi fost generate de catre serverul Web.

Folosirea caching-ului in Yii implica in general configurarea si accesarea
unei componente cache a aplicatiei Yii. Codul urmator configureaza componenta
cache sa foloseasca clasa memcache cu doua servere de cache:

~~~
[php]
array(
	......
	'components'=>array(
		......
		'cache'=>array(
			'class'=>'system.caching.CMemCache',
			'servers'=>array(
				array('host'=>'server1', 'port'=>11211, 'weight'=>60),
				array('host'=>'server2', 'port'=>11211, 'weight'=>40),
			),
		),
	),
);
~~~

Cand aplicatia ruleaza, componenta cache poate fi accesata prin
`Yii::app()->cache`.

Yii pune la dispozitie mai multe componente cache, pentru a asigura memorarea
datelor pe mai multe medii de stocare, in functie de necesitati.
De exemplu, componenta [CMemCache] incapsuleaza extensia PHP memcache si foloseste
memoria RAM ca mediu de stocare; componenta [CApcCache] incapsuleaza extensia APC din PHP;
iar componenta [CDbCache] memoreaza datele intr-o baza de date.
Mai jos sunt componentele cache disponibile in acest moment: 

   - [CMemCache]: foloseste PHP [extensia memcache](http://www.php.net/manual/en/book.memcache.php).

   - [CApcCache]: foloseste [extensia APC](http://www.php.net/manual/en/book.apc.php) din PHP.

   - [CXCache]: foloseste [extensia XCache](http://xcache.lighttpd.net/) din PHP.
Note, this has been available since version 1.0.1.

   - [CDbCache]: foloseste o tabela din baza de date pentru a memora datele din
cache. Va crea si va folosi o baza de date SQLite3 care va exista in directorul
runtime. Putem specifica explicit o baza de date prin setarea proprietatii
[connectionID|CDbCache::connectionID].

> Tip|Sfat: Pentru ca toate aceste componente cache sunt derivate din aceeasi
clasa de baza [CCache], putem folosi oricand un tip diferit de cache fara sa
modificam codul care foloseste cache-ul. 

Caching-ul poate fi folosit la diferite nivele. La La nivelul cel mai de jos,
folosim cache-ul pentru a memora o singura entitate (ex. o variabila) si acest
nivel poarta numele de *data caching*. La nivelul urmator, memoram in cache
un fragment dintr-o pagina web care este generat de o portiune a unui fisier view.
La cel mai inalt nivel, memoram o intreaga pagina web in cache si o servim din cache
atunci cand este necesar. 

In urmatoarele cateva subsectiuni, vom arata cum sa folosim cache-ul in toate
aceste cazuri. 

> Note|Nota: prin definitie, cache-ul este un mediu volatil de stocare.
Nu asigura existenta unei portiuni de date care a fost memorata in cache, chiar
daca aceasta portiune teoretic nu a expirat inca. De aceea, cache-ul nu trebuie
folosit ca un mediu de stocare persistent (ex. a nu se folosi cache-ul pentru a
memora date despre sesiune).
<div class="revision">$Id: caching.overview.txt 723 2009-02-21 18:14:05Z qiang.xue $</div>