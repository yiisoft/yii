Cache de date
=============

Cashing-ul de date se refera la memorarea unor variabile PHP in cache si folosirea lor
mai tarziu prin extragerea lor din cache. In acest scop, clasa componentei de baza cache
[CCache] furnizeaza doua metode care sunt folosite de obicei: [set()|CCache::set]
si [get()|CCache::get].

Pentru a memora o variabila `$value` in cache, alegem un ID unic
si apelam [set()|CCache::set] pentru a o memora:

~~~
[php]
Yii::app()->cache->set($id, $value);
~~~

Datele memorate in cache vor ramane in cache pe un termen nedefinit pana cand sunt sterse
datorita unor politici de caching (ex. spatiul de memorie cache este plin si cele mai vechi
date trebuie sterse). Pentru a modifica acest comportament, putem de asemenea sa furnizam o
perioada de expirare atunci cand apelam [set()|CCache::set], si astfel datele vor fi sterse
din cache dupa o perioada de timp specificata:

~~~
[php]
// pastram valoarea in cache pentru cel mult 30 de secunde
Yii::app()->cache->set($id, $value, 30);
~~~

Mai tarziu, cand trebuie sa accesam aceasta variabila (in aceeasi cerere web sau in alta cerere web),
apelam [get()|CCache::get] cu ID-ul necesar pentru a extrage variabila din cache.
Daca valoarea returnata este false, atunci inseamna ca valorea respectiva nu mai este
valabila in cache si ca ar trebui sa o regeneram.

~~~
[php]
$value=Yii::app()->cache->get($id);
if($value===false)
{
	// regeneram $value pentru ca nu mia exista in cache
	// si o salvam iar in cache pentru o utilizare ulterioara:
	Yii::app()->cache->set($id,$value);
}
~~~

Atunci cand alegem ID-ul pentru o variabila de memorat in cache, trebuie sa ne asiguram
ca ID-ul este unic printre variabilele aplicatiei noastre. Este SUFICIENT sa asiguram
unicitatea variabilei in aplicatia noastra. Componenta cache este suficient de inteligenta
pentru a face diferenta intre ID-urile a doua aplicatii diferite.

Pentru a sterge o valoare din cache, apelam [delete()|CCache::delete]. Pentru
a sterge tot ce exista in cache, apelam [flush()|CCache::flush]. Trebuie sa fim foarte
atenti cand apelam [flush()|CCache::flush] pentru ca se sterg si datele din toate celelalte
aplicatii.

> Tip|Sfat: Pentru ca [CCache] implementeaza `ArrayAccess`, o componenta cache poate fi
> folosita ca un array, ca in urmatoarele exemple:
> ~~~
> [php]
> $cache=Yii::app()->cache;
> $cache['var1']=$value1;  // echivalent cu: $cache->set('var1',$value1);
> $value2=$cache['var2'];  // echivalent cu: $value2=$cache->get('var2');
> ~~~

Dependente Cache
----------------

In afara de expirare, datele din cache pot fi invalidate in functie de unele schimbari
ale unor dependente. De exemplu, daca introducem in cache continutul unui fisier,
iar fisierul se modifica in vreun fel, atunci ar trebui sa invalidam copia sa din cache si sa
citim ultima versiune a fisierului pentru a o adauga in cache. 

Reprezentam o dependenta ca instanta a clasei [CCacheDependency] sau a unei clase derivate.
Transmitem instanta dependentei impreuna cu datele care trebuie memorate in cache atunci cand
apelam [set()|CCache::set].

~~~
[php]
// valoarea va expira in 30 de secunde
// poate fi invalidata mai devreme daca fisierul dependent este modificat
Yii::app()->cache->set($id, $value, 30, new CFileCacheDependency('NumeFisier'));
~~~

In acest moment, daca extragem `$value` din cache prin apelarea [get()|CCache::get],
dependenta va fi evaluata si daca este modificata, atunci vom primi o valoare
false, ceea ce indica faptul ca datele trebuie regenerate.

Mai jos avem un sumar ale dependentelor cache posibile:

   - [CFileCacheDependency]: dependenta este modificata daca timpul ultimei modificari s-a schimbat.

   - [CDirectoryCacheDependency]: dependenta este modificata daca s-a modificat cel putin un fisier din
director sau din subdirectoarele acestuia.

   - [CDbCacheDependency]: dependenta este modificata daca rezultatul cererii SQL este modificata.

   - [CGlobalStateCacheDependency]: dependenta este modificata daca valoarea starii globale specificate
este modificata. O stare globala este o variabila care este persistenta intre cererile
si sesiunile unei aplicatii. Variabila este definita prin [CApplication::setGlobalState()].

   - [CChainedCacheDependency]: dependenta este modificata daca este modificata oricare dintre dependentele
dintr-un chain.

<div class="revision">$Id: caching.data.txt 169 2008-11-06 19:43:44Z qiang.xue $</div>