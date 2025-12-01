Le Migliori Pratiche MVC
==================

Sebbene il design pattern Model-View-Controller (MVC) sia conosciuto da quasi 
tutti gli sviluppatori Web, sfugge ancora a molte persone come usare MVC 
correttamente nelle applicazioni reali.
L'idea centrale che sta dietro MVC è la **riutilizzabilità del codice e la 
separazione delle competenze**.
In questa sezione sono descritte alcune linee guida generali su come seguire al 
meglio il design pattern MVC quando si sviluppa un'applicazione con Yii.

Per maggior chiarezza di queste linee guida, si assume che un'applicazione Web 
sia composta da diverse sotto-apllicazioni, come ad esempio

* front-end: un sito web ad accesso pubblico per normali utenti finali;
* back-end: un sito che esponga funzionalità amministrative per la gestione 
dell'applicazione. Normalmente l'accesso è ristretto allo staff amministrativo;
* console: un'applicazione consistente in comandi console che vanno eseguiti in 
una finestra di terminale o come job schedulato a supporto di tutta l'applicazione;
* Web API: fornitura di un'interfaccia a terze parti per l'integrazione con 
l'applicazione.

Le sotto-applicazioni possono essere implementate in termini di 
[moduli](/doc/guide/basics.module), o come applicazione Yii che condivide parti 
del codice con le altre sotto-applicazioni.


Model
-----

I [Model](/doc/guide/basics.model) rappresentano la struttura di base dei dati 
di un'applicazione Web.
I Model sono spesso condivisi tra le differenti sotto-applicazioni 
dell'applicazione Web.
Per esempio il model `LoginForm` può essere usato sia da un'applicazione front-
end che da una back-end;
il Model `News` può essere usato dalla console comandi, dalle Web API, e 
dall'applicazione front/back-end. Quindi i model:

* dovrebbero contenere le proprietà per rappresentare dati specifici;
* dovrebbero contenere la logica di funzionamento (es. regole di validazione) 
per assicurare la piena corrispondenza dei dati alle esigenze di progetto;
* potrebbero contenere codice per la manipolazione dei dati. Per sesempio il 
model `SearchForm` sebbene rappresenti la ricerca di dati inseriti, potrebbe 
contenere un metodo `search` che implementi la ricerca vera e propria.

A volte, sequire quest'ultima regola potrebbe portare ad avere un model molto 
grande, contenente troppo codice in una sola classe. Potrebbe accadere che il 
model sia più difficile da mantenere qualora esso venga utilizzato per diversi 
scopi. Ad esempio, il model `News` potrebbe contenere il metodo `getLatestNews` 
il quale può essere utilizzato solo dal front-end; ovvero potrebbe contenere il 
metodo `getDeletedNews` il quale è utilizzato solo dal back-end. Ciò può andar 
bene per un programma di piccole dimesion. Per applicazioni di grandi 
dimensioni, la strategia seguente può essere usata per rendere i model più 
mantenibili:

* Definire una classe model `NewsBase` che contenga solo il codice condiviso 
dalle diverse sotto-applicazioni (es. front-end, back-end);

* in ogni sotto-applicazione, definire il modello `News` estendendo `NewsBase`. 
Poszionare tutto il codice che è specifico della sotto-applicazione in questo 
model `News`.

Quindi, se dovessimo utilizzare questa strategia nell'esempio precedente, 
potremmo aggiungere un model `News` nell'applicazione front-end che contenga 
solo il metodo `getLatestNews`, e aggiungeremmo un'altro model `News` 
nell'applicazione back-end che contenga solo il metodo `getDeletedNews`.

In generale i model non dovrebbero contenere logica che abbia a che fare 
direttamente con gli utenti finali. Più in dettaglio, i model:

* non dovrebbero utilizzare `$_GET`, `$_POST` o varaibili simili che sono 
direttamente legate alle richieste dell'utente finale. Ricordarsi che un model 
potrebbe essere usato da sotto-applicazioni completamente diverse (es. unit 
test, API web) che potrebbero utilizzare queste variabili per rappresentare le 
richieste degli utenti. Queste variabili relative alle richieste degli utenti 
devono essere gestite dal Controller.

* dovrebbero evitare di incorporare HTML od altro codice di presentazione. 
Siccome il codice di presentazione varia in funzione delle esigenze 
dell'utente finale (es. front-end e back-end potrebbero visualizzare i dettagli 
di una news in formati completamente diversi), è meglio che se occupino le view.


View
----

Le [View](/doc/guide/basics.view) sono responsabili della presentazione dei 
model nel formato desiderato dall'utente. In generale le view:

* dovrebbero principalmente contenere codice di presentazione, come HTML e 
semplice codice PHP per scorrere, formattare e rappresentare i dati.

* dovrebbero evitare di contenere codice che esplicitamente esegua query al DB. 
Questo tipo di codice è meglio che venga posto nei model.

* dovrebbero evitare di accedere direttamente a `$_GET`, `$_POST` o varaibili 
simili che rappresentano le richieste dell'utente finale. Questo è il lavoro 
dei controller. La view dovrebbe concentrarsi sulla visualizzazione e 
sull'organizzazione dei dati ad essa forniti dal controller e/o dal model, e 
non tentare di accedere direttamente alle variabili di request od al database.

* possono accedere direttamente alle proprietà ed ai metodi dei controller e 
dei model. Comunque ciò dovrebbe essere fatto al solo scopo di presentare i dati.


Le view possono essere riutilizzate in diversi modi:

* Layout: aree di presentazione comune (es. header, footer) possono essere 
messe in una view di layout.

* View parziali: usare le view parziali (view che non sono decorate dal layout) 
per riutilizzare frammenti di codice di presentazione. Per esempio, usiamo la 
view parziale `_form.php` per renderizzare il form di input del model che è 
utilizzato sia nella pagina di creazione del model che in quella di aggiornamento.

* I widget: se è necessaria un sacco di logica per presentare una view parziale 
la view parziale può essere trasformata in un widget il cui file della classe è 
il miglior posto per contenere questa logica. Per i widget che generano 
parecchio codice HTML, si consiglia di usare file view specifici del widget che 
contenga il codice di markup.

* Classi helper: nelle view spesso si ha bisogno di alcuni frammenti di codice 
per eseguire piccoli compiti come la formattazione dei dati e la generazione di 
tag HTML. Piuttosto che inserire questo codice direttamente nei file delle view, 
un migliore approccio è quello di mettere tutti questi frammenti di codice in 
una classe helper della view. Così è sufficiente utilizzare la classe helper 
nei file delle view. Yii fornisce un esempio per questo tipo di approccio. Yii 
dispone di una classe helper [CHtml] potente, che può produrre codice HTML di 
uso comune. Le classi Helper possono essere messe una 
[cartella di caricamento automatico](/doc/guide/basics.namespace) 
in modo che possano essere utilizzate senza l'esplicita inclusione della classe.


Controller
----------

I [Controller](/doc/guide/basics.controller) sono il collante che lega tra 
loro i model, le view ed altri componenti. I Controller hanno l'incarico di 
avere a che fare direttamente con le richieste dell'utente. Pertanto i 
controller:

* possono accedere a `$_GET`, `$_POST` ed ad altre variabili PHP che 
rappresentino le richieste dell'utente.

* possono creare istanze di model e gestire il loro ciclo di vita. Per esempio, 
in una tipica action di update del model, il controller prima potrebbe creare 
una istanza del model; poi popolare il model con l'imput dell'utente 
prelevandolo da `$_POST`; quindi dopo aver salvato correttamente il model, il 
controller potrebbe ridirigere il browser dell'utente in una pagina di 
dettaglio del model. Si noti che che l'effettivo salvataggio di un model 
dovrebbe essere posizionato nel model piuttosto che nel controller.

* dovrebbero evitare di contenere istruzioni SQL, che invece è meglio tenere nei 
model.

* dovrebbero evitare di contenere qualsiasi HTML od altro marcatore di 
presentazione. È meglio che questo codice sia messo nelle view.


In una applicazione MVC ben progettata, i controller spesso sono molto piccoli, 
contengono probabilmente solo una dozzina di righe di codice; mentre i model 
sono molto grandi, contengono la maggior parte del codice responsabile di 
rappresentare e manipolare i dati. Ciò è dovuto al fatto che la struttura dei 
dati e la logica di business rappresentata dai model è di solito molto specifica 
per la particolare applicazione e necessita di essere pesantemente 
personalizzata per soddisfare le specifiche esigenze dell'applicazione; mentre 
le logiche di controllo, tra le varie applicazioni, spesso seguono percorsi 
similari e quindi possono essere ben semplificate dal framework sottostante 
o dalle classi di base.

<div class="revision">$Id: basics.best-practices.txt 2795 2010-12-31 00:22:33Z alexander.makarow $</div>