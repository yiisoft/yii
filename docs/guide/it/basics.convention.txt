Convenzioni
===========

Yii favorisce le convenzioni al posto delle configurazioni. Seguendo le convenzioni
si possono creare applicazioni Yii sofisticate senza scrivere e gestire configurazioni 
complesse. Naturalmente, quando necessario Yii può essere ancora personalizzato 
in quasi ogni aspetto con le configurazioni.

Qui di seguito sono descritte le convenzioni che sono raccomandate nella programmazione 
con Yii. Per comodità, si presume che `WebRoot` sia la cartella in cui l'applicazione 
Yii si trovi installata.

URL
---

Per default, Yii riconosce gli URL con il seguente formato:

~~~
http://hostname/index.php?r=ControllerID/ActionID
~~~

La variabile GET `r` si riferisce alla [route](/doc/guide/basics.controller#route) 
che può essere risolto da Yii in controller ed action. Se si omette `ActionID`, 
il controller prenderà l'action di default (definito tramite [CController::defaultAction]); 
se viene omesso anche `ControllerID` (o la variabile `r` è assente), l'applicazione 
userà il controller di default (definito tramite [CWebApplication::defaultController]).

Con l'aiuto di [CUrlManager], è possibile creare e riconoscere URL più amichevoli 
per il SEO, come ad esempio `http://hostname/ControllerID/ActionID.html`. Questa 
funzionalità è descritta in dettaglio nel paragrafo [URL Management](/doc/guide/topics.url).

Codice
----

Yii raccomanda di nominare le variabili, funzioni e classi in "camel case" dove la 
prima lettera di ogni parola del nome è maiuscola e queste parole sono legate 
tra loro senza spazi. I nomi delle variabili e delle funzioni dovrebbero avere la 
loro prima parola tutta in minuscolo, cosicché possano essere differenziati dai 
nomi delle classi (es. `$basePath`, `runController()`, `LinkPager`). Per i membri 
di variabili private, è raccomandato mettere un underscore come prefisso al nome 
(es. `$_actionList`).

Dato che i namespace non sono supportati dalle versioni di PHP precedenti alla 5.3.0, 
è raccomandato che le classi siano nominati in un modo univoco in modo tale da evitare 
conflitti con classi di terze parti. Per questa ragione, tutte le classi del 
framework Yii hanno come prefisso la lettera "C".

Una regola speciale per i nomi della classe controller è quella che a questi 
deve seguire la parola `Controller`. L'ID del controller è quindi definito come il 
nome della classe con la prima lettera in minuscolo senza la parola `Controller`.
Per esempio, la classe `PageController` avrà l'ID `page`. Questa regola rende 
l'applicazione più sicura. Rende inoltre gli URL legati ai controller un po' più 
chiari (es. `/index.php?r=page/index` invece di `/index.php?r=PageController/index`).

Configurazione
-------------

La configurazione è un array a coppie di chiave-valore. Ogni chiave rappresenta il 
nome di una proprietà dell'oggetto che deve essere configurato, ed ogni valore 
rappresenta il corrispondete valore iniziale della proprietà. Per esempio, 
`array('name'=>'My application', 'basePath'=>'./protected')` inizializza le 
proprietà `name` e `basePath` con i loro corrispondenti valori dell'array.

Ogni proprietà scrivibile di un oggetto può essere configurata. Se non configurata, 
la proprietà prenderà il suo valore di default. Quando viene configurata una proprietà, 
vale la pena di leggere la corrispondente documentazione cosicché il valore iniziale 
possa essere dato in modo corretto.

File
----

La convenzione per la denominazione e l'uso dei file dipende dai loro tipo.

I file delle classi dovrebbero essere denominati dopo la loro classe pubblica che 
contengono. Per esempio, la classe [CController] corrisponde al file `CController.php`.
Una classe pubblica è una classe che può essere usata da altre classi. Ogni file di 
una classe dovrebbe contenere al massimo un sola classe pubblica. Classi privare 
(classi che sono utilizzate da una singola classe pubblica) possono stare nello 
stesso file con la classe pubblica.

I file delle view dovrebbero essere denominate dopo il nome della view. Per esempio, 
la view `index` corrisponde al file `index.php`. Un file view è uno script PHP che 
contiene HTML e codice PHP principalmente a scopo di presentazione.

I file di configurazione possono essere denominati arbitrariamente. Un file di 
configurazione è uno script PHP il cui unico scopo è quello di restituire un 
array associativo che rappresenta la configurazione.

Cartelle
---------

Yii presuppone una serie di cartelle di default usate per vari scopi. Ciascuna di 
esse, se necessario, può essere personalizzata.

   - `WebRoot/protected`: questa è la [cartella principale 
dell'applicazione](/doc/guide/basics.application#application-base-directory) che contiene 
tutti gli script PHP sensibili alla sicurezza ed i file dei dati. Yii ha un alias di default 
che si chiama `application` associato a questo path. Questa cartella nonché qualunque 
cosa al suo interno dovrebbe essere protetta dall'essere accessibile dagli utenti Web.
Si può personalizzare tramite [CWebApplication::basePath].

   - `WebRoot/protected/runtime`: questa cartella contiene file temporanei privati 
generati durante l'esecuzione dell'applicazione. Questa cartella deve esse scrivibile 
dal processo proprietario del Web server. Si può personalizzare tramite 
[CApplication::runtimePath].

   - `WebRoot/protected/extensions`: questa cartella contiene tutte le estensioni 
di terze parti. Si può personalizzare tramite [CApplication::extensionPath]. Yii ha
un alias di default che si chiama `ext` associato a questo path.

   - `WebRoot/protected/modules`: questa cartella contiene tutti i 
[moduli](/doc/guide/basics.module) dell'applicazione, ognuno rappresenta un sotto cartella.

   - `WebRoot/protected/controllers`: questa cartella contiene tutti i file delle
classi controller. Si può personalizzare tramite [CWebApplication::controllerPath].

   - `WebRoot/protected/views`: questa cartella contiene tutti i file delle view, 
comprese le view dei controller, le view dei layout e le view di sistema. Si può 
personalizzare tramite [CWebApplication::viewPath].

   - `WebRoot/protected/views/ControllerID`: questa cartella contiene i file delle 
view di una singola classe controller. Qui `ControllerID` sta per l'ID del controller. 
Si può personalizzare tramite [CController::viewPath].

   - `WebRoot/protected/views/layouts`: questa cartella contiene tutti i file delle 
view dei layout. Si può personalizzare tramite [CWebApplication::layoutPath].

   - `WebRoot/protected/views/system`: questa cartella contiene tutti i file delle 
view di sistema. Le view di sistema sono dei template utilizzati per visualizzare 
eccezioni ed errori. Si può personalizzare tramite [CWebApplication::systemViewPath].

   - `WebRoot/assets`: questa cartella contiene tutti i file asset pubblicati. Un file 
asset è un file privato che può essere pubblicato per divenire accessibile agli utenti 
Web. Questa cartella deve esse scrivibile dal processo proprietario del Web server. 
Si può personalizzare tramite [CAssetManager::basePath].

   - `WebRoot/themes`: questa cartella contiene vari temi che possono essere applicati 
all'applicazione. Ciascuna sotto cartella rappresenta un singolo tema il cui nome 
è il nome della sottocartella. Si può personalizzare tramite 
[CThemeManager::basePath].

Database
--------

La maggior parte delle web application sono sorrette da un database. Per una migliore 
pratica, si propone la seguente convenzione per la denominazione delle tabelle e dei 
campi del database. Notare che non sono necessari per Yii.

   - Sia le tabelle del database che i campi hanno nomi in minuscolo.

   - Le parole nel nome dovrebbero essere separate usando un underscores (e.g. `product_order`).

   - Per i nomi delle tabelle, si può usare sia il singolare che il plurale, ma non entrambi. 
Per semplicità, si raccomanda di utilizzare i nomi singolari.

   - I nomi delle tabelle possono avere un prefisso come `tbl_`. Ciò è particolarmente 
utile quando le tabelle di una applicazione coesistono nello stesso database con 
le tabelle di un'altra applicazione. Le due serie di tabelle possono essere separate 
facilmente utilizzando prefissi diversi per i nomi delle tabelle.

<div class="revision">$Id: basics.convention.txt 3225 2011-05-17 23:23:05Z alexander.makarow $</div>