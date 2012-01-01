Path Alias e Namespace
========================

Yii utilizza ampiamente i path alias. Un path alias è associato ad una cartella 
od al percorso per un file. Utilizza la sintassi del punto, similmente a quanto 
diffusamente adottato per il formato dei namespace:

~~~
RootAlias.percorso.per.obiettivo
~~~

dove `RootAlias` è l'alias di una qualche cartella esistente.

Utilizzando [YiiBase::getPathOfAlias()], un alias può essere tradotto nel suo path 
corrispondente. Per esempio, `system.web.CController` verrebbe tradotto come 
`yii/framework/web/CController`.

Si può anche utilizzare [YiiBase::setPathOfAlias()] per definire un nuovo path alias principale.

Alias principale
----------

Per comodità, Yii predefinisce i seguenti alias principali:

 - `system`: si riferisce alla cartella del framework Yii;
 - `zii`: si riferisce alla cartella della [libreria Zii](/doc/guide/extension.use#zii-extensions);
 - `application`: si riferisce alla [cartella principale](/doc/guide/basics.application#application-base-directory) dell'applicazione;
 - `webroot`: si riferisce alla cartella che contiene il file dell'[entry script](/doc/guide/basics.entry).
 - `ext`: si riferisce alla cartella che contiene tutte le [estensioni](/doc/guide/extension.overview) sviluppate da terze parti.

Inoltre, se un'applicazione utilizza i [moduli](/doc/guide/basics.module), ogni 
modulo avrà un alias principale predefinito che ha lo stesso nome dell'ID del 
modulo e si riferisce al path di base del modulo. Per esempio, se una applicazione 
utilizza un modulo il cui ID è `users`, verrà predefinito un alias principale 
che si chiamerà `users`.

Importazione di classi
-----------------

L'uso di alias, è molto utile per includere la definizione di una classe.
Per esempio, se si vuole includere la classe [CController], si può fare come segue:

~~~
[php]
Yii::import('system.web.CController');
~~~

Il metodo [import|YiiBase::import] si differenza da `include` e `require` 
nel senso che è molto efficiente. La definizione della classe che si deve importare 
in realtà non viene inclusa fin quando non vi si fa riferimento per la prima volta 
(implementato attraverso il meccanismo di autoload di PHP). L'importazione dello stesso 
namespace più volte è molto più veloce di `include_once` e `require_once`.

> Suggerimento: Quando si fa riferimento ad una classe definita dal framework Yii, 
> non è necessario importarla od includerla. Tutto il core delle classi di Yii sono preimportate.

###Usare la mappa delle classi

A partire dalla versione 1.1.5, Yii permette alle classi utente di essere preimportate 
attraverso un meccanismo di mappatura delle classi che è usato anche dalle classi del 
core di Yii. Le classi preimportate possono essere utilizzate ovunque in una 
applicazione Yii senza che vengano importate od incluse esplicitamente. 
Questa funzionalità è molto utile per un framework o per una libreria che è 
costruita su a Yii.

Per preimportare una serie di classi, deve essere eseguito il seguente codice 
prima che venga invocato [CWebApplication::run()]:

~~~
[php]
Yii::$classMap=array(
	'ClassName1' => 'percorso/per/ClassName1.php',
	'ClassName2' => 'percorso/per/ClassName2.php',
	......
);
~~~

Importazione delle cartelle
---------------------

Si può utilizzare anche la seguente sintassi per importare un'intera cartella 
cosicché i file delle classi all'interno della cartella possono essere 
automaticamente incluse quando necessario.

~~~
[php]
Yii::import('system.web.*');
~~~

Oltre che per l'[importazione|YiiBase::import], gli alias sono anche utilizzati 
in molti altri posti per riferirsi alle classi. Per esempio, un alias può essere 
passato a [Yii::createComponent()] per creare una istanza della corrispondente 
classe, anche se il file della classe non è stato incluso in precedenza.

Namespace
---------

Un namespace si riferisce ad un raggruppamento logico di alcuni nomi di classe 
cosicché esse possano essere differenziate dagli altri nomi di classi anche se 
i loro nomi sono gli stessi. Non si confondano i path alias con i namespace. Un 
path alias è semplicemente un modo conveniente di nominare un file od una cartella.
Non ha niente a che vedere con i namespace.

> Suggerimento: Dato che PHP prima della versione 5.3.0 non supporta intrinsecamente 
i namespace, non si possono creare istanze di due classi che hanno lo stesso nome ma
con definizioni differenti. Per questa ragione, tutte le classi del framework Yii hanno 
il prefisso 'C' (che significa 'classe') cosicché possano essere differenziate dalle 
classi definite dagli utenti. Si raccomanda che il prefisso 'C' sia riservato solo 
ad uso del framework Yii e che le classi definite dagli utenti abbiano come prefisso 
una qualunque altra lettera.

Classi nel namespace
------------------

Una classe nel namespace si riferisce ad una classe dichiarata all'interno di un 
namespace non globale. Per esempio, la classe `application\components\GoogleMap` 
è dichiarata all'interno del namespace `application\components`. L'uso di classi nel 
namespace richiede PHP 5.3.0 o superiore.

A partire dalla versione 1.1.5 di Yii, è possibile usare le classi nel namespace 
senza includerle esplicitamente. Per esempio, si può creare una nuova istanza di 
`application\components\GoogleMap` senza includere esplicitamente il corrispondente 
file della classe. Ciò è reso possibile dal miglioramento del meccanismo di autoload 
delle classi di Yii.

Per poter eseguire l'autoload di una classe nel namespace, il namespace deve essere 
chiamato in modo simile al nome di un path alias. Per esempio la classe `application\components\GoogleMap`
deve essere memorizzata in un file che può avere come alias `application.components.GoogleMap`.

<div class="revision">$Id: basics.namespace.txt 3086 2011-03-15 00:04:53Z qiang.xue $</div>