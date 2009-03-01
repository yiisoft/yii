Cache de pagini
===============

Caching-ul de pagini se refera la memorarea in cache a continutului unei
intregi pagini. Caching-ul de pagini poate aparea si in aplicatia server
si in aplicatia client.

De exemplu, prin alegerea unui header de pagina corespunzator, browser-ul clientului
poate memora in cache pagina pentru o perioada limitata de timp. 

Aplicatia web insasi poate memora continutului paginii in cache si in aceasta subsectiune
ne referim la aceasta tehnica.

Caching-ul de pagini poate fi considerat un caz special al
[caching-ului de fragmente](/doc/guide/caching.fragment). Continutul unei pagini
este de obicei generat prin aplicarea unui layout la un view. De aceea, caching-ul
nu va functiona daca apelam in interiorul layout-ului [beginCache()|CBaseController::beginCache] si
[endCache()|CBaseController::endCache]. Cauza este ca layout-ul este aplicat
in interiorul metodei [CController::render()] DUPA ce view-ul a fost evaluat.

Pentru a memora in cache o pagina intreaga, ar trebui sa sarim peste action-ul
care genereaza continutul paginii. Putem folosi [COutputCache] ca
[filtru](/doc/guide/basics.controller#filter) al respectivului action
pentru a ne atinge scopul. Urmatorul cod arata cum sa configuram filtrul cache:

~~~
[php]
public function filters()
{
	return array(
		array(
			'system.web.widgets.COutputCache',
			'duration'=>100,
			'varyByParam'=>array('id'),
		),
	);
}
~~~

Configuratia filtrului de mai sus face ca filtrul sa fie aplicat tuturor
action-urilor din controller. Putem preciza caror action-uri le va fi aplicat
filtrul. Putem folosi in acest scop operatorul `+`. Mai multe detalii pot fi gasite
in sectiunea despre [filtre](/doc/guide/basics.controller#filter).

> Tip|Sfat: Putem folosi clasa [COutputCache] ca filtru pentru ca este derivata
din clasa [CFilterWidget], ceea ce inseamna ca este atat filtru cat si widget.
De fapt, un widget functioneaza foarte asemanator cu un filtru: un widget incepe
inainte de evaluarea continutului HTML care ii urmeaza, si apoi widget-ul se termina dupa
ce continutul HTML a fost evaluat. 

<div class="revision">$Id: caching.page.txt 162 2008-11-05 12:44:08Z weizhuo $</div>