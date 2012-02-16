Applicazione
===========

L'oggetto application incapsula il contesto di esecuzione all'interno del quale viene processata
una richiesta. Il suo compito principale è quello di raccogliere alcune informazioni basilari
relative alla richiesta, ed indirizzarle ad un appropriato controller per essere ulteriormente processate.
È utilizzato anche come luogo centrale per mantenere le impostazioni di configurazione a livello applicazione.
Per questa ragione l'applicazione è chiamata anche `front-controller`.

L'applicazione è istanziata come singleton attraverso l'[entry script](/doc/guide/basics.entry).
Si può accedere al singleton d'ovunque tramite [Yii::app()|YiiBase::app].


Configurazione dell'applicazione
-------------------------

Di default, l'oggetto application è un'istanza di [CWebApplication]. Per
personalizzarla, di solito si utilizza un file di configurazione (o array)
per inizializzare le proprietà quando viene istanziata. Un metodo
alternativo di personalizzazione è quello di estendere [CWebApplication].

La configurazione è un'array con coppie di chiave-valore. Ogni chiave rappresenta il nome
di una proprietà dell'istanza application, ed ogni valore il suo corrispondente
valore iniziale. Per esempio, il seguente array di configurazione imposta le
proprietà di [name|CApplication::name] e di [defaultController|CWebApplication::defaultController] dell'application.

~~~
[php]
array(
	'name'=>'Yii Framework',
	'defaultController'=>'site',
)
~~~

Di solito si memorizza la configurazione in uno script PHP separato (p.e.
`protected/config/main.php`). All'interno dello script, viene restituito
l'array di configurazione come segue:

~~~
[php]
return array(...);
~~~

Per applicare la configurazione, si passa il nome del file di configurazione
come parametro al costruttore dell'application, o a [Yii::createWebApplication()]
nel modo seguente, di solito nell'[entry script](/doc/guide/basics.entry):

~~~
[php]
$app=Yii::createWebApplication($configFile);
~~~

> Suggerimento: se la configurazione dell'applicazione è molto complessa, possiamo dividerla
in più file, ognuno riportante una porzione dell'array di configurazione.
Quindi, nel file di configurazione principale, possiamo, tramite la funzione PHP `include()`,
includere gli altri file di configurazione ed unirli in un array di configurazione completo.


Cartella Base dell'Applicazione
--------------------------

La cartella base dell'applicazione è la cartella principale dove si trovano
tutti gli script e i dati PHP sensibili alla sicurezza. Di default, è una
sottocartella chiamata `protected` che si trova all'interno della cartella contenente
l'entry script. Può essere personalizzata settando la proprietà [basePath|CWebApplication::basePath]
nella [Configuratione dell'applicazione](/doc/guide/basics.application#application-configuration).

I contenuti all'interno della cartella base dell'applicazione dovrebbero essere
protetti essendo accessibili degli utenti Web. Con [Apache HTTP server](http://httpd.apache.org/),
questo può essere facilmente ottenuto posizionando un file `.htaccess` all'interno della cartella principale.
Il contenuto del file `.htaccess` dovrà essere:

~~~
deny from all
~~~

Componenti dell'Applicazione
----------------------

Le funzionalità dell'oggetto application possono essere facilmente personalizzate ed arricchite
grazie alla sua architettura flessibile a componenti. Quest'oggetto gestisce una serie di componenti dell'applicazione,
ognuno dei quali implementa specifiche funzionalità. Ad esempio l'oggetto esegue
alcune elaborazioni iniziali di una richiesta utente con l'aiuto dei componenti [CUrlManager]
e [CHttpRequest].

Configurando le proprietà dei [componenti|CApplication::components] di un'istanza
dell'applicazione, si può personalizzare i valori della classe e della proprietà
di ogni componente utilizzato. Per esempio, possiamo configurare il componente [CMemCache]
in modo tale che per il caching utilizzi diversi server di cache, come questo:

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

Qui sopra abbiamo aggiunto l'elemento `cache` all'array `components`.
L'elemento `cache` determina che la classe del componente sia `CMemCache`
e che la proprietà dei suoi server debba essere inizializzata in questo modo.

Per accedere ad un componente, usa `Yii::app()->ComponentID`, dove
`ComponentID` si riferisce all'ID del componente (es. `Yii::app()->cache`).

Un componente può essere disabilitato impostando nella configurazione il
parametro `enabled` come `false`. Quando si accede ad un componente disabilitato
si ottiene come risultato `null`.

> Suggerimento: di default i componenti sono creati quando necessari. Ciò significa
che un componente non può essere creato se non è stato acceduto durante
una richiesta. Come risultato, le perfomance totali non sono degradate
persino quando un'applicazione è configurata per utilizzare molti componenti. Per Alcuni
componenti (es. [CLogRouter]) potrebbe essere necessario che vengano creati senza tener conto
se siano stati acceduti o meno. Per far ciò, elenca i loro ID nella proprietà
[preload|CApplication::preload] dell'applicazione.


Componenti Core dell'Applicazione
---------------------------

Yii predefinisce una serie di componenti core per fornire funzionalità
tipiche per le applicazioni Web. Per esempio, il componente [request|CWebApplication::request]
è utilizzato per raccogliere le informazioni di una richiesta utente e fornire
informazioni come l'URL richiesto ed i cokie. Configurando le proprietà
di questi componenti core, possiamo cambiare il comportamento di default
di quasi ogni aspetto di Yii.

Ecco una lista di componenti core che sono pre-dichiarati da [CWebApplication]:

   - [assetManager|CWebApplication::assetManager]: [CAssetManager] - gestisce la pubblicazione di file risorsa privati.

   - [authManager|CWebApplication::authManager]: [CAuthManager] - gestisce il controllo di accesso role-based (RBAC).

   - [cache|CApplication::cache]: [CCache] - gestisce la funzione di caching dei dati.
Nota, si deve specificare la classe attuale (es.
[CMemCache], [CDbCache]). Altrimenti quando si accede al componente
si otterrà null come risultato.

   - [clientScript|CWebApplication::clientScript]: [CClientScript] - gestisce gli script client (javascript and CSS).

   - [coreMessages|CApplication::coreMessages]: [CPhpMessageSource] - fornisce i messaggi core tradotti utilizzati dal framework Yii.

   - [db|CApplication::db]: [CDbConnection] - fornisce la connessione al database. Nota, si deve configurare la proprietà [connectionString|CDbConnection::connectionString] per poter utilizzare questo componente.

   - [errorHandler|CApplication::errorHandler]: [CErrorHandler] - gestisce le eccezioni e gli errori PHP non catturati.

   - [format|CApplication::format]: [CFormatter] - formatta i valori dei dati a solo scopo di visualizzazione.

   - [messages|CApplication::messages]: [CPhpMessageSource] - fornisce i messaggi tradotti utilizzati dall'applicazione Yii.

   - [request|CWebApplication::request]: [CHttpRequest] - fornisce le informazioni relative alle richieste utente.

   - [securityManager|CApplication::securityManager]: [CSecurityManager] - fornisce i servizi legati alla sicurezza, come hashing e criptazione.

   - [session|CWebApplication::session]: [CHttpSession] - fornisce la funzionalità legata alla sessione.

   - [statePersister|CApplication::statePersister]: [CStatePersister] - fornisce il meccanismo per la persistenza dello stato globale.

   - [urlManager|CWebApplication::urlManager]: [CUrlManager] - fornisce le funzionalità per la creazione e il parsing (analisi) degli URL.

   - [user|CWebApplication::user]: [CWebUser] - trasporta le informazioni legate all'identità dell'utente corrente.

   - [themeManager|CWebApplication::themeManager]: [CThemeManager] - gestisce i temi.


Ciclo di vita dell'Applicazione
----------------------

Quando viene gestita una richiesta, una applicazione seguirà il seguente ciclo di vita:

   0. Pre-inizializza l'applicazione con [CApplication::preinit()];

   1. Impostazione della classe autoloader e della gestione degli errori;

   2. Registrazione dei componenti core;

   3. Caricamento della configurazione dell'applicazione;

   4. Inizializzazione dell'applicazione con [CApplication::init()]
     - Registrazione dei comportamenti dell'applicazione;
     - caricamento dei componenti statici;

   5. Generazione dell'evento [onBeginRequest|CApplication::onBeginRequest];

   6. Processo della richiesta:
	   - Raccolta delle informazioni riguardanti la richiesta;
	   - Creazione di un controller;
	   - Esecuzione del controller;

   7. Generazione dell'evento [onEndRequest|CApplication::onEndRequest];

<div class="revision">$Id: basics.application.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>