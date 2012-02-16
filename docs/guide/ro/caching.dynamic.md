Continut dinamic
================

Atunci cand folosim [caching de fragmente](/doc/guide/caching.fragment) sau
[caching de pagini](/doc/guide/caching.page), intalnim de obicei situatia
in care intreaga portiune a output-ului este relativ statica cu exceptia unei
mici portiuni (sau mai multor mici portiuni). De exemplu, o pagina de ajutor,
poate afisa informatii statice de ajutor, dar cu numele utilizatorului (inregistrat in
acest moment) afisat in partea de sus a paginii.

Pentru a rezolva aceasta situatie, putem retine in cache continutul paginii pentru fiecare
nume de utilizator in parte. Aceasta varianta va duce la o extrema irosire de spatiu cache
pretios. In special pentru ca se afiseaza in mare parte acelasi lucru, cu exceptia numelui utilizatorului.

O alta varianta ar fi sa impartim pagina in mai multe fragmente si sa le memoram in cache pe fiecare in parte,
dar aceasta varianta complica view-ul si face codul prea complex.

O mai buna abordare este sa folosim feature-ul de *continut dinamic* (dynamic content)
furnizata de [CController].

Un continut dinamic inseamna un fragment de output care nu ar trebui sa fie memorat in cache,
chiar daca este in interiorul unui fragment cache. Pentru aface acest continut dinamic mereu,
trebuie sa fie generat de fiecare data, chiar daca portiunea care include fragmentul nostru
este generat din cache la momentul cererii utilizatorului web.
Din acest motiv, trebuie sa generam continutul dinamic printr-o functie sau metoda.

Apelam [CController::renderDynamic()] pentru a insera continut dinamic in locul dorit.

~~~
[php]
...alt continut HTML...
<?php if($this->beginCache($id)) { ?>
...fragmentul de memorat in cache...
	<?php $this->renderDynamic($callback); ?>
...fragmentul de memorat in cache...
<?php $this->endCache(); } ?>
...alt continut HTML...
~~~

In codul de mai sus, `$callback` se refera la un callback PHP valid. 
Poate sa fie un string care se refera ori la numele unei metode din clasa controller-ului curent
ori la numele unei functii globale.
Poate de asemenea sa fie un array care se refera la o metoda a unei clase. 
Orice parametri in plus transmisi catre [renderDynamic()|CController::renderDynamic()]
vor fi transmisi callback. Callback-ul ar trebui sa returneze continutul dinamic in loc sa il afiseze.

<div class="revision">$Id: caching.dynamic.txt 163 2008-11-05 12:51:48Z weizhuo $</div>