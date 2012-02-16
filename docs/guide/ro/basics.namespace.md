Path Alias si Namespace
==========================

Yii foloseste foarte mult path alias-uri (scurtaturi de cale). Un path alias
este asociat unui director sau unui fisier fizic. Alias-ul este specificat in
sintaxa cu puncte, la fel cu formatul namespace adoptat in lume:

~~~
RootAlias.cale.catre.destinatie
~~~

`RootAlias` este alias-ul unui director existent. Prin apelarea [YiiBase::setPathOfAlias()],
putem defini noi alias-uri de cale. Pentru convenienta, Yii predefineste urmatoarele alias-uri radacina:

 - `system`: se refera la directorul platformei Yii;
 - `application`: se refera la [directorul de baza al aplicatiei](/doc/guide/basics.application#application-base-directory);
 - `webroot`: se refera la directorul care contine fisierul cu [scriptul de intrare](/doc/guide/basics.entry).
 Acest alias este disponibil incepand cu versiunea 1.0.3.

In plus, daca aplicatia foloseste [module](/doc/guide/basics.module), un alias radacina este
de asemenea predefinit pentru fiecare ID de modul si se refera la calea modulului respectiv.
Acest feature este disponibil incepand cu versiunea 1.0.3.
 
Folosind [YiiBase::getPathOfAlias()], un alias poate fi tradus in path-ul corespunzator.
De exemplu, `system.web.CController` ar fi tradus in `yii/framework/web/CController`.

Folosind alias-uri, este foarte convenabil sa importam definitiile unei clase.
De exemplu, daca vrem sa includem definitia clasei [CController], putem face in felul urmator:

~~~
[php]
Yii::import('system.web.CController');
~~~

Metoda [import|YiiBase::import] este diferita de functiile PHP `include` si `require`
in sensul ca este mult mai eficienta. Definitia clasei nu este de fapt inclusa decat din
momentul in care este folosita prima data. Importarea unor namespace-uri de mai multe ori
este de asemenea mult mai rapida decat functiile PHP `include_once` si `require_once`.

> Tip|Sfat: Cand facem referirea la o clasa definita de platforma Yii, nu trebuie sa o
importam sau sa o includem. Toate clasele nucleu ale Yii sunt importate deja.

Putem folosi de asemenea urmatoarea sintaxa pentru a importa un intreg director, astfel incat
toate clasele publice din fisierele din acel director vor fi automat incluse atunci cand este necesar.

~~~
[php]
Yii::import('system.web.*');
~~~

Pe langa [import|YiiBase::import], alias-urile sunt de asemenea folosite in multe alte locuri
pentru a se face referinta catre clase. De exemplu, un alis poate fi transmis catre
[Yii::createComponent()] pentru a crea o instanta a clasei corespunzatoare, chiar daca fisierul clasei nu
a fost inclus anterior.

Sa nu confuzam path alias-urile cu namespace-uri. Un namespace se refera la o grupare logica
a unor nume de clase, astfel incat acestea sa fie diferite de alte clase care au acelasi nume.
Path alias-urile sunt folosite pentru a se face referinta la o clasa dintr-un fisier fizic sau la
un director. Path alias-ul nu intra in conflict cu 

> Tip|Sfat: Pentru ca inainte de versiunea PHP 5.3.0 nu exista suport pentru namespace,
nu putem crea instante a doua clase care au acelasi nume (dar definitii diferite). Din acest
motiv, toate clasele platformei Yii sunt prefixate cu litera 'C' (de la 'clasa') astfel incat
vor fi diferite fata de clasele definite ulterior de catre utilizatori. Este recomandat
sa prefixam cu 'C' doar clasele din platforma Yii, iar clasele definite de utilizatori sa fie
prefixate cu alte litere.

<div class="revision">$Id: basics.namespace.txt 766 2009-02-27 17:22:13Z qiang.xue $</div>