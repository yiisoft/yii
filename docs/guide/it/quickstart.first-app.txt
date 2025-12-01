Crea la tua prima applicazione con Yii
===================================

Per farti avere la prima esperienza con Yii, in questa sezione viene descritto 
come creare la tua prima applicazione con Yii. Utilizzeremo `yiic` (uno 
strumento a riga di comando) per creare una nuova applicazione Yii e `Gii` 
(potente generatore di codice ad interfaccia web) per automatizzare la 
generazione di codice per determinate attività. Per comodità, si da per 
scontato che `YiiRoot` sia la cartella in cui è installato Yii, e `WebRoot`
sia la cartella radice del web server.

Eseguire `yiic` su riga di comando come segue:

~~~
% YiiRoot/framework/yiic webapp WebRoot/testdrive
~~~

> Nota: Quando si esegue `yiic` su Mac OS, Linux or Unix, potrebbe essere 
> necessario cambiare i permessi di esecuzione del file `yiic` per renderlo 
> eseguibile. In alternativa, è possibile eseguire `yiic` come segue,
>
> ~~~
> % cd WebRoot
> % php YiiRoot/framework/yiic.php webapp testdrive
> ~~~

Questo creerà lo schema di una applicazione Yii all'interno della cartella 
`WebRoot/testdrive`. L'applicazione ha uno schema di cartelle che è richiesta 
dalla maggior parte delle applicazioni Yii.

Senza scrivere neanche una riga di codice PHP, possiamo provare la nostra 
prima applicazione Yii accedendo al seguente URL in un browser web:

~~~
http://hostname/testdrive/index.php
~~~

Come si può vedere, l'applicazione ha quattro pagine: la home page, la pagina 
del chi siamo, la pagina dei contatti e la pagina di login. La pagina dei 
contatti mostra un form di contatto che gli utenti possono compilare per 
presentare le loro richieste al webmaster, la pagina di login consente agli 
utenti di autenticarsi prima di accedere ai contenuti riservati. Guarda questi 
screenshot per maggiori dettagli.

![Home page](first-app1.png)

![Contact page](first-app2.png)

![Contact page with input errors](first-app3.png)

![Contact page with success message](first-app4.png)

![Login page](first-app5.png)


Il seguente diagramma mostra la struttura delle cartelle della nostra 
applicazione. Si prega di consultare le 
[Convenzioni](/doc/guide/basics.convention#directory) per maggiori 
spiegazioni. 

~~~
testdrive/
   index.php                 script di entrata della web application
   index-test.php            script di entrata per i test funzionali
   assets/                   contenitore dei file di risorse pubblicate
   css/                      contenitore dei file CSS
   images/                   contenitore dei file immagine
   themes/                   contenitore dei temi dell'applicazione
   protected/                contenitore dei file protetti dell'applicazione
      yiic                   script di riga di comando per Unix/Linux
      yiic.bat               script di riga di comando per Windows
      yiic.php               script di riga di comando in PHP
      commands/              contenitore di comandi 'yiic' personalizzati
         shell/              contenitore di comandi 'yiic shell' personalizzati
      components/            contenitore di componenti utente riutilizzabili
         Controller.php      classe di base per tutte le classi Controller
         UserIdentity.php    la classe 'UserIdentity' usata per l'autenticazione
      config/                contenitore dei file di configurazione
         console.php         configurazione della console dell'applicazione
         main.php            configurazione della web application
         test.php            configurazione dei test funzionali
      controllers/           contenitore dei file delle classi Controller 
         SiteController.php  classe Controller di default
      data/                  contenitore di database di esempio
         schema.mysql.sql    schema di esempio database MySQL
         schema.sqlite.sql   schema di esempio database SQLite
         testdrive.db        file di esempio database SQLite
      extensions/            contenitore estensioni di terze parti
      messages/              contenitore delle traduzioni dei messaggi
      models/                contenitore dei file delle classi Model
         LoginForm.php       il Model del form di login per l'Action di 'login'
         ContactForm.php     il Model del form di contatto per l'Action 'contact'
      runtime/               contenitore dei file generati temporaneamente
      tests/                 contenitore degli script di test
      views/                 contenitore dei file di View dei layout e dei Controller
         layouts/            contenitore dei file di View dei layout
            main.php         layout di base condiviso da tutte le pagine
            column1.php      layout per le pagine che utilizzano una singola colonna
            column2.php      layout per le pagine che utilizzano due colonne
         site/               contenitore dei file di View del Controller 'site'
            pages/           contenitore delle pagine "static" (statiche)
               about.php     la View della pagina "about"
            contact.php      la View della action 'contact'
            error.php        la View della action 'error' (visualizza errori esterni)
            index.php        la View della action 'index'
            login.php        la View della action 'login'
~~~

Connessione al database
----------------------

La maggior parte delle web application si appoggiano ad un database. La nostra 
applicazione di prova non fa eccezione. Per utilizzare un database, occorre 
spiegare all'applicazione come connettersi con esso. Ciò viene fatto nel file 
di configurazione dell'applicazione 
`WebRoot/testdrive/protected/config/main.php`, evidenziato di seguito,

~~~
[php]
return array(
	......
	'components'=>array(
		......
		'db'=>array(
			'connectionString'=>'sqlite:protected/data/testdrive.db',
		),
	),
	......
);
~~~

Il codice qui sopra spiega a Yii che l'applicazione dovrebbe connettersi, 
quando è necessario, al database SQLite 
`WebRoot/testdrive/protected/data/testdrive.db`. Si noti che il database 
SQLite è già incluso nella struttura dell'applicazione che è stata appena 
generata. Il database contiene una sola tabella che si chiama `tbl_user`:

~~~
[sql]
CREATE TABLE tbl_user (
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	username VARCHAR(128) NOT NULL,
	password VARCHAR(128) NOT NULL,
	email VARCHAR(128) NOT NULL
);
~~~

Se invece si vuole utilizzare un database MySQL, si può utilizzare il file 
che contiene lo schema MySQL
`WebRoot/testdrive/protected/data/schema.mysql.sql` per creare il database.

> Nota: per utilizzare la funzionalità database di Yii, è necessario abilitare 
le estensioni PDO di PHP ed il driver specifico per l'estensione PDO. 
Per l'applicazione di prova è necessario abilitare sia l'estensione `php_pdo` 
che `php_pdo_sqlite`.


Implementazione delle operazioni CRUD
----------------------------

Adesso arriva la parte divertente. Vorremmo implementare le operazioni CRUD 
(create, read, update and delete - crea, leggi, aggiorna e cancella) per la 
tabella `tbl_user` appena creata. Questa è una necessità tipica delle 
applicazioni. Invece di sobbarcarci l'onere di scrivere il codice necessario, 
utilizzeremo `Gii` -- un potente generatore di codice ad interfaccia web.

> Informazione: Gii è stato reso disponibile a partire dalla versione 1.1.2. Prima di allora, per ottenere lo stesso risultato, è possibile utilizzare il citato strumento `yiic`. Per maggiori dettagli, si prega di fare riferimento a [Implementare operazioni CRUD con la shell yiic](/doc/guide/quickstart.first-app-yiic).


### Configurare Gii

Per poter utilizzare Gii, è necessario prima modificare il file 
`WebRoot/testdrive/protected/config/main.php`, noto come il file di 
[configurazione dell'applicazione](/doc/guide/basics.application#application-configuration):

~~~
[php]
return array(
	......
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'inserire qui una password',
		),
	),
);
~~~

Poi visitare l'URL `http://hostname/testdrive/index.php?r=gii`. Verrà richiesta una password che dovrebbe essere quella che è stata appena personalizzata nel file di configurazione dell'applicazione qui sopra.

### Generazione del Model User

Dopo il login, cliccare sul link `Model Generator`. Questo ci porterà sulla seguente pagina di generazione dei Model,

![Model Generator](gii-model.png)

Nel campo `Table Name` digiater `tbl_user`. Nel campo `Model Class` digitare `User`. Poi cliccare sul bottone `Preview`. Questo ci farà vedere il nuovo file di codice che sarà generato. Adesso premere il bottone`Generate`. Un nuovo fiel chiamato `User.php` sarà generato all'interno della cartella `protected/models`. Come sarà spiegato più avanti in questa guida, questa classe del Model `User` ci permette di parlare con la tabella `tbl_user` del database sottostante in modalità object-oriented.

### Generazione del codice CRUD

Dopo aver creato il file con la classe del Model, genereremo il codice che implementa le operazioni CRUD sui dati utente. Selezionare `Crud Generator` in Gii, come indicato di seguito,

![CRUD Generator](gii-crud.png)

Nel campo `Model Class` digitare `User`. Nel campo `Controller ID` digitare `user` (in minuscolo). Adesso premere il bottone `Preview` e poi il bottone `Generate`. Così abbiamo concluso con la generazione del codice per le funzionalità CRUD.

### Accedere alle pagine CRUD

Godiamoci il nostro lavoro navigando verso il seguente URL:

~~~
http://hostname/testdrive/index.php?r=user
~~~

Verrà visualizzato l'elenco delle voci della tabella `tbl_user`.

Cliccare sul bottone `Create User` sulla pagina. Se non ci siamo autenticati 
in precedenza, saremo portati alla pagina di login. Dopo l'autenticazione 
verrà visualizzato un form che ci consente di aggiungere un nuovo utente. 
Compila il modulo e clicca sul bottone `Create`. Qualora ci sia un errore di 
input, verrà visualizzato un simpatico messaggio di errore che ci impedisce di 
salvare i dati inseriti. Tornando alla pagina di elenco degli utenti, dovremmo 
vedere apparire nella lista il nuovo utente.

Ripetere i passaggi precedenti per aggiungere altri utenti. Notare che la 
pagina della lista utenti verrà automaticamente suddivisa in più pagine se ci 
sono troppi utenti da visualizzare in una pagina sola.

Se ci autentichiamo come amministratori utilizzando le credenziali 
`admin/admin`, possiamo vedere la pagina dell'utente admin con il seguente 
URL: 

~~~
http://hostname/testdrive/index.php?r=user/admin
~~~

Verranno visualizzate le voci degli utenti in un bel formato tabellare. 
Possiamo cliccare sull'intestazione della colonna per ordinare la tabella in 
base a quella colonna. Possiamo cliccare sui bottoni di ciascuna riga di dati 
visualizzati per visualizzare, aggiornare o cancellare la corrispondente riga 
di dati. Possiamo sfogliare diverse pagine. Possiamo anche filtrare e cercare 
i dati ai quali siamo interessati.

Tutte queste caratteristiche interessanti sono disponibili senza che ci sia la 
necessità di scrivere una sola riga di codice PHP!

![User admin page](first-app6.png)

![Create new user page](first-app7.png)

<div class="revision">$Id: quickstart.first-app.txt 3219 2011-05-13 03:03:35Z qiang.xue $</div>