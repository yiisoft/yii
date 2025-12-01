Modulo
======

Un modulo è una unità software autosufficiente che consiste in 
[model](/doc/guide/basics.model), [view](/doc/guide/basics.view), 
[controller](/doc/guide/basics.controller) ed altri componenti di 
supporto. In molti aspetti, un modulo assomiglia ad una 
[applicazione](/doc/guide/basics.application). La differenza principale 
consiste nel fatto che un modulo non può essere utilizzato da solo ma deve risiedere 
all'interno di una applicazione. Gli utenti possono accedere al controller di un 
modulo così come farebbero con il controller di una normale applicazione.

I moduli sono utili in diversi scenari. Le applicazioni di grandi dimensioni, si 
possono dividere in diversi moduli, ciascuno dei quali può essere sviluppato e 
mantenuto separatamente. Alcune funzionalità di uso comune, come la gestione degli 
utenti, la gestione dei contenuti, possono essere sviluppate in termini di 
moduli che possono essere riutilizzati in progetti futuri.

Creare un modulo
---------------

Un modulo è organizzato come una cartella il cui nome viene utilizzato quale suo 
[ID|CWebModule::id] univoco. La struttura della cartella del modulo è simile a quella della 
[cartella principale dell'applicazione](/doc/guide/basics.application#application-base-directory). 
Il codice seguente mostra la struttura tipica delle cartelle di un modulo che si chiama `forum`:

~~~
forum/
   ForumModule.php            il file della classe del modulo
   components/                contiene componenti utente riutilizzabili
      views/                  contiene i file delle view dei widget 
   controllers/               contiene i file delle classi controller
      DefaultController.php   il file di default della classe controller 
   extensions/                contiene le estensioni sviluppate da terze parti
   models/                    contiene i file delle classi model 
   views/                     contiene i file delle view e dei layout
      layouts/                contiene i file delle view dei layout
      default/                contiene i file delle view del DefaultController
         index.php            il file index della view
~~~

Un modulo deve avere una classe module che estende da [CWebModule]. Il nome della 
classe è determinato utilizzando l'espressione `ucfirst($id).'Module'`, dove `$id` 
si riferisce a l'ID del modulo (o il nome della cartella del modulo). La classe 
del modulo serve quale luogo centrale per la memorizzazione delle informazioni 
condivise lungo tutto il codice del modulo. Per esempio, si può utilizzare 
[CWebModule::params] per memorizzare i parametri del modulo, ed usare 
[CWebModule::components] per condividere i 
[component dell'applicazione](/doc/guide/basics.application#application-component) 
a livello di modulo.

> Suggerimento: È possibile usare il generatore di moduli Gii per creare la struttura di base di un nuovo modulo.


Usare i moduli
------------

Per usare un modulo, prima piazzare la cartella del modulo all'interno della cartella `modules` della 
[cartella principale dell'applicazione](/doc/guide/basics.application#application-base-directory). 
Quindi dichiarare l'ID del modulo nella proprietà [modules|CWebApplication::modules] dell'applicazione. 
Per esempio, per poter utilizzare il modulo `forum` di prima, si può utilizzare la seguente 
[configurazione dell'applicazione](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	......
	'modules'=>array('forum',...),
	......
);
~~~

Un modulo può essere anche configurato con valori iniziali delle proprietà. 
L'uso è molto simile alla configurazione dei 
[component dell'applicazione](/doc/guide/basics.application#application-component). 
Per esempio, il modulo `forum` potrebbe avere una proprietà che si chiama `postPerPage` 
nella sua classe del modulo che può essere configurata nella 
[configurazione dell'applicazione](/doc/guide/basics.application#application-configuration) 
come segue:

~~~
[php]
return array(
	......
	'modules'=>array(
	    'forum'=>array(
	        'postPerPage'=>20,
	    ),
	),
	......
);
~~~

È possibile accedere all'istanza del modulo tramite la proprietà [module|CController::module] 
del controller attualmente attivo. Tramite l'istanza del modulo, si accedere alle informazioni 
che sono condivise a livello modulo. Per esempio, per poter accedere all'informazione 
`postPerPage` vista prima, si può utilizzare la seguente espressione:

~~~
[php]
$postPerPage=Yii::app()->controller->module->postPerPage;
// ovvero il codice seguente qualora $this si riferisca all'istanza del controller
// $postPerPage=$this->module->postPerPage;
~~~

È possibile accedere all'action del controller in un modulo utilizzando la 
[route](/doc/guide/basics.controller#route) `moduleID/controllerID/actionID`. 
Per esempio, supponendo che il precedente modulo `forum` abbia un controller che 
si chiami `PostController`, si può usare la [route](/doc/guide/basics.controller#route) 
`forum/post/create` per riferirsi all'action `create` di questo controller. L'URL 
corrispondente per questa route sarebbe `http://www.example.com/index.php?r=forum/post/create`.

> Suggerimento: Se un controller si trova nella cartella `controllers`, è 
> possibile ancora utilizzare il precedente formato di [route](/doc/guide/basics.controller#route). 
> Per esempio, supponendo che `PostController` si trovi all'interno di `forum/controllers/admin`, 
> si può fare riferimento all'action `create` usando `forum/admin/post/create`.


Moduli annidati
-------------

I moduli possono essere annidati indefinitamente. Cioè, un modulo può contenere 
un'altro modulo che a sua volta può contenere ancora un'altro modulo. Il primo 
si chiama *modulo genitore* mentre il secondo si chiama *modulo figlio*. I moduli 
figlio possono essere dichiarati nella proprietà del [modulo|CWebModule::modules] 
del loro modulo genitore, così come si dichiarano i moduli nella configurazione 
dell'applicazione vista prima.

Per accedere all'action del controller di un modulo figlio, si dovrebbe usare la 
route `parentModuleID/childModuleID/controllerID/actionID`.

<div class="revision">$Id: basics.module.txt 2890 2011-01-18 15:58:34Z qiang.xue $</div>