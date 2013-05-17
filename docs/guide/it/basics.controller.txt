Controller
==========

Il `controller` è un'istanza di [CController] o di una classe che estende [CController].
È creata dall'oggetto applicazione quando l'utente lo richiede. Quando un controller 
è in esecuzione, esegue l'azione richiesta, che usualmente porta al model necessario 
e visualizza la view appropriata. Un'`action`, nella sua forma più elementare, è 
semplicemente un metodo di una classe controller che ha il nome che inizia con `action`.

Un controller ha un'action di default. Quando la richiesta dell'utente non specifica 
quale action eseguire, verrà eseguita l'action di default. Per default, l'action di 
default è chiamata `index`. Può essere modificata impostando la variabile pubblica 
[CController::defaultAction] dell'istanza.

Il codice che segue definisce il controller `site`, l'action `index` (l'action di 
default) e l'action `contact`:

~~~
[php]
class SiteController extends CController
{
	public function actionIndex()
	{
		// ...
	}

	public function actionContact()
	{
		// ...
	}
}
~~~


Route
-----

Controller ed action sono identificate da un ID. 
L'ID del controller è nel formato `percorso/per/xyz`, che corrisponde al file 
della classe controller `protected/controllers/percorso/per/XyzController.php`, 
dove il segnaposto `xyz` dovrebbe essere sostituito con un nome reale; 
es. `post` corrisponde a `protected/controllers/PostController.php`.
L'ID dell'action è il nome del metodo action senza il prefisso `action`. Per 
esempio, se una classe controller contiene un metodo che si chiama `actionEdit`, 
l'ID corrispondente a questa action sarebbe `edit`.

Gli utenti accedono ad un particolare controller ed action tramite route (percorso).
Una route è formata concatenando l'ID del controller con l'ID dell'action 
separandoli con uno slash. Per esempio, la route `post/edit` si riferisce al 
controller `PostController` ed all'action `edit`. Di default, l'URL 
`http://hostname/index.php?r=post/edit` richiederà il controller `post` e l'action 
`edit`.

>Note:|Nota: Di default, le route differenziano tra maiuscolo e minuscolo (case-sensitive). È
>possibile rendere le route indifferenti al maiuscolo/minuscolo impostando [CUrlManager::caseSensitive]
>a false nel file di configurazione dell'applicazione. Quando si è in modalità indifferente al maiuscolo/minuscolo,
>assicurarsi di seguire la convenzione che le carelle che contengono i file delle classi 
>controller siano in minuscolo, e che sia la [mappa dei controller|CWebApplication::controllerMap]
>che la [mappa delle action|CController::actions] abbiano le chiavi in minuscolo.

Un'applicazione può contenere [moduli](/doc/guide/basics.module). La route per
l'action di un controller all'interno di un modulo è nel formato `moduleID/controllerID/actionID`.
Per ulteriori dettagli, vedere la [sezione sui moduli](/doc/guide/basics.module).


Instaziazione del Controller
------------------------

Un'istanza del controller è creata quando [CWebApplication] gestisce una 
richiesta in arrivo. Dato l'ID del controller l'applicazione utilizzerà 
le seguenti regole per stabilire quale sia la classe controller e dove si trovi 
il file della classe.

   - Se [CWebApplication::catchAllRequest] è specificato, sarà creato un controller
basato su questa proprietà, ed l'ID del controller specificato dall'utente sarà 
ignorato. Ciò è utilizzato principalmente per mettere l'applicazione in modalità
manutenzione e visualizzare una pagina statica con un avviso.

   - Se l'ID è trovato in [CWebApplication::controllerMap], la configurazione 
del corrispondente controller verrà utilizzata per creare l'istanza di quel controller.

   - Se l'ID è nel formato `'percorso/per/xyz'`, il nome della classe del controller 
si assume essere `XyzController` e il corrispondete file della classe sarà 
`protected/controllers/percors/per/XyzController.php`. Per esempio, il controller ID
`admin/user` sarà mappato con la classe controller `UserController` ed il file 
della classe sarà `protected/controllers/admin/UserController.php`.
Se il file della classe non esiste, sarà generato un errore 404 [CHttpException].

Quando sono usati i [moduli](/doc/guide/basics.module), la procedura descritta sopra
è leggermente diversa. In particolare, l'applicazione controllerà se l'ID si riferisce o meno 
ad un controller contenuto in un modulo, ed in tal caso, sarà prima creata una 
istanza del modulo, seguita dall'istanza del controller.


Action
------

Come notato in precedenza, un'action può essere definita come un metodo il cui 
nome inizia con la parola `action`. Una tecnica più avanzata è quella di definire 
una classe action e chiedere al controller di istanziarla quando richiesto. Ciò 
consente alle action di poter essere riutilizzate e quindi si ha maggiore riusabilità.

Per definire una nuova classe action, procedere come segue:

~~~
[php]
class UpdateAction extends CAction
{
	public function run()
	{
		// inserire qui la logica dell'action
	}
}
~~~

Per far prendere coscienza al controller dell'esistenza di questa action, occorre sovraccaricare 
il metodo [actions()|CController::actions] della nostra classe controller:

~~~
[php]
class PostController extends CController
{
	public function actions()
	{
		return array(
			'edit'=>'application.controllers.post.UpdateAction',
		);
	}
}
~~~

Nel codice sopra, è stato utilizzato un path alias  
`application.controllers.post.UpdateAction` per specificare che il file della 
classe action è `protected/controllers/post/UpdateAction.php`.

Scrivendo action basate su classi, si può organizzare un'applicazione come un 
sistema modulare. Per esempio, la seguente struttura di cartelle può essere 
utilizzata per organizzare il codice dei controller:

~~~
protected/
    controllers/
        PostController.php
        UserController.php
        post/
            CreateAction.php
            ReadAction.php
            UpdateAction.php
        user/
            CreateAction.php
            ListAction.php
            ProfileAction.php
            UpdateAction.php
~~~

### Associazione dei parametri all'Action

Sin dalla versione 1.1.4, Yii ha aggiunto il supporto per l'associazione 
automatica dei parametri all'action. Vale a dire, il metodo action del controller 
può definire parametri nominali il cui valore sarà automaticamente impostato da Yii 
prelevandolo da `$_GET`

Per far vedere come questo funzioni, supponiamo di dover scrivere l'action `create` 
per il `PostController`. L'action necessita di due parametri:

* `category`: un intero che indica l'ID della categoria all'interno della quale il nuovo post sarà creato;
* `language`: una stringa che indica il codice della lingua in cui il nuovo post si troverà.

Potremmo concludere con il seguente codice noioso allo scopo di recuperare i valori dei parametri necessari da `$_GET`:

~~~
[php]
class PostController extends CController
{
	public function actionCreate()
	{
		if(isset($_GET['category']))
			$category=(int)$_GET['category'];
		else
			throw new CHttpException(404,'invalid request');

		if(isset($_GET['language']))
			$language=$_GET['language'];
		else
			$language='en';

		// ... il codice divertente inizia qui ...
	}
}
~~~

Adesso, utilizzando la funzionalità di associazione dei parametri all'action, si può completare 
il compito più piacevolmente:

~~~
[php]
class PostController extends CController
{
	public function actionCreate($category, $language='en')
	{
		$category=(int)$category;

		// ... il codice divertente inizia qui ...
	}
}
~~~

Notare che si aggiungono due parametri al metodo action `actionCreate`.
I nomi di questi parametri devono essere esattamente gli stessi di quelli che ci 
si aspetta da `$_GET`. Il parametro `$language` prende di default il valore `en` 
nel caso in cui la richiesta non includa tale parametro. Poiché `$category` non 
ha un valore di default, se la richiesta non include il parametro `category`, 
sarà generata automaticamente un'eccezione [CHttpException] (error code 400).

A partire dalla versione 1.1.5, Yii supporta anche il tipo di dato array quale parametro dell'action.
Ciò è ottenuto tramite il suggerimento di tipo del PHP utilizzano la sintassi seguente:

~~~
[php]
class PostController extends CController
{
	public function actionCreate(array $categories)
	{
		// Yii si assicurerà che $categories sia un array
	}
}
~~~

Cioè, si aggiunge la keyword `array` prima di `$categories` nella dichiarazione 
del parametro del metodo. Così facendo, se `$_GET['categories']` è una semplice stringa, 
sarà convertita in un array composto solamente da quella stringa.

> Nota: Se un parametro è dichiarato senza il suggerimento di tipo, significa che il parametro
> deve essere uno scalare (p.e., non è un array). In questo caso, passando come parametro un array tramite 
> `$_GET` causerà un'eccezione HTTP.

A partire dalla versione 1.1.7, l'associazione automatica dei parametri funziona 
anche per le action basate su classi. Quando è stato definito il metodo `run()` con parametri 
di una classe action, questi saranno impostati con i corrispondenti valori dei 
parametri richiesti. Per esempio,

~~~
[php]
class UpdateAction extends CAction
{
	public function run($id)
	{
		// $id sarà impostato con il valore di $_GET['id']
	}
}
~~~


Filter
------

Il Filter è un pezzo di codice che è configurato per essere eseguito prima e/o dopo 
l'esecuzione dell'action di un controller. Per esempio, il filter del controllo degli accessi
potrebbe essere eseguito per assicurarsi che l'utente sia autenticato prima che 
venga eseguita l'action richiesta; un filter per prestazioni potrebbe essere utilizzato
per misurare il tempo utilizzato per eseguire l'action.

Un'action potrebbe avere molti filter. I filter sono eseguiti nell'ordine con 
cui appaiono nella lista dei filter. Un filter può impedire l'esecuzione di un'action 
nonché il resto dei filter non eseguiti.

Un filter può essere definito come un metodo della classe controller. Il nome del metodo deve
iniziare con `filter`. Ad esempio, un metodo chiamato `filterAccessControl` definisce 
un filter chiamato `accessControl`. Il metodo filter deve avere la corretta firma:

~~~
[php]
public function filterAccessControl($filterChain)
{
	// chiamare $filterChain->run() per continuare l'esecuzione del filter e dell'action
}
~~~

dove `$filterChain` è un'istanza di [CFilterChain] che rappresenta la lista dei 
filter associata con l'action richiesta. All'interno di un metodo filter, si può 
chiamare `$filterChain->run()` per continuare l'esecuzione del filter e dell'action.

Un filter può essere anche un'istanza di [CFilter] o una sua classe figlio. Il codice 
che segue definisce una nuova classe filter:

~~~
[php]
class PerformanceFilter extends CFilter
{
	protected function preFilter($filterChain)
	{
		// logica applicata prima che l'action venga eseguita
		return true; // false se l'action non deve essere eseguita
	}

	protected function postFilter($filterChain)
	{
		// logica applicata dopo che l'action è stata eseguita
	}
}
~~~

Per applicare i filter alle action, è necessario sovraccaricare il metodo 
`CController::filters()`. Il metodo dovrebbe restituire un array delle configurazioni 
dei filter. Per esempio, 

~~~
[php]
class PostController extends CController
{
	......
	public function filters()
	{
		return array(
			'postOnly + edit, create',
			array(
				'application.filters.PerformanceFilter - edit, create',
				'unit'=>'second',
			),
		);
	}
}
~~~

Il codice precedente specifica due filter: `postOnly` e `PerformanceFilter`.
Il filter `postOnly` è basato sul metodo (il metodo filter corrispondente è già 
definito in [CController]); mentre il filter `PerformanceFilter` è basato sugli 
oggetti. Il path alias `application.filters.PerformanceFilter` 
specifica che il file della classe filter è `protected/filters/PerformanceFilter`. 
Si utilizza un array per configurare `PerformanceFilter` cosicché può essere 
utilizzato per inizializzare i valori delle proprietà dell'oggetto filter. 
Qui la proprietà `unit` di `PerformanceFilter` sarà inizializzata come `'second'`.

Utilizzando gli operatori più e meno, si può specificare a quale action il filter 
dovrebbe o non dovrebbe essere applicato. Nel codice precedente, il filter `postOnly` 
verrà applicato alle action `edit` e `create`, mentre il filter `PerformanceFilter` 
verrà applicato a tutte le action AD ECCEZIONE DI `edit` e `create`. Se non appare alcun 
più o meno nella configurazione del filter, il filter sarà applicato a tutte le action.

<div class="revision">$Id: basics.controller.txt 3251 2011-06-01 00:24:06Z qiang.xue $</div>